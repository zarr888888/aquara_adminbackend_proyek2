<?php

namespace Tests\Feature;

use App\Filament\Widgets\InfoCuacaWidget;
use App\Filament\Widgets\InfoKkpWidget;
use App\Filament\Widgets\PetaTpiWidget;
use App\Models\User;
use App\Support\ExternalInfo;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExternalInfoTest extends TestCase
{
    private const WIDGETS = [InfoKkpWidget::class, InfoCuacaWidget::class, PetaTpiWidget::class];

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Http::fake(['maritim.bmkg.go.id/*' => Http::response([
            'code' => 'XJ022', 'valid_from' => now('UTC')->subHour()->format('Y-m-d H:i').' UTC',
            'valid_to' => now('UTC')->addDay()->format('Y-m-d H:i').' UTC',
            'forecast_day1' => [['time' => now('UTC')->format('Y-m-d H:i').' UTC', 'weather' => 'Cerah']],
        ])]);
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'cache.default' => 'array']);
        DB::purge('sqlite');
        foreach (['0001_01_01_000000_create_users_table.php', '2026_04_09_050518_create_permission_tables.php'] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        (require database_path('migrations/2026_11_24_104443_create_tpi_verifications_table.php'))->up();
    }

    private function user(?string $role = null): User
    {
        $user = User::withoutEvents(fn () => User::factory()->create());
        if ($role !== null) {
            Role::findOrCreate($role, 'web');
            $user->assignRole($role);
        }

        return $user;
    }

    public function test_admin_verification_publication_and_withdrawal_in_test_database(): void
    {
        $this->actingAs($this->user());
        self::assertFalse(\App\Filament\Resources\TpiVerificationResource::canCreate());
        $this->actingAs($this->user('Admin Dinas'));
        self::assertTrue(\App\Filament\Resources\TpiVerificationResource::canCreate());
        $model = \App\Models\TpiVerification::class;
        $info = fn () => collect(app(ExternalInfo::class)->get('tpi')['data']['items'])->firstWhere('id','dadap');
        self::assertNull($info()['lat']);
        // Synthetic values only in SQLite memory; not a claim about Dadap's location.
        $record = ['facility'=>'dadap', 'published'=>true, 'lat'=>1.5, 'lng'=>2.5,
            'source_url'=>'https://kkp.go.id/test-only', 'evidence'=>'LATIHAN unit test saja'];
        $model::create($record);
        self::assertSame(1.5, $info()['lat']);
        $model::create(['facility'=>'dadap','published'=>false,'evidence'=>'LATIHAN cabut pin uji']);
        self::assertNull($info()['lat']);
        self::assertSame(2, $model::count());
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $model::create(array_merge($record, ['source_url'=>'https://example.test/kkp.go.id']));
    }

    public function test_public_endpoints_share_cached_service_data(): void
    {
        foreach (['kkp', 'tpi'] as $type) {
            $expected = app(ExternalInfo::class)->get($type);
            $this->getJson('/api/info/'.$type)->assertOk()->assertExactJson($expected)
                ->assertJsonPath('data.is_mock', false);
            $this->assertSame($expected, Cache::get('samudera:external-info:curated:v1:'.$type));
        }
    }

    public function test_cache_reuses_payload_and_expires_after_five_minutes(): void
    {
        $service = app(ExternalInfo::class);
        $first = $service->get('kkp');
        $this->travel(4)->minutes();
        $this->assertSame($first, $service->get('kkp'));
        $this->travel(2)->minutes();
        $this->assertNotSame($first['data']['generated_at'], $service->get('kkp')['data']['generated_at']);
        $this->travelBack();
    }

    public function test_rate_limit_is_shared_across_endpoints_and_resets(): void
    {
        $types = ['kkp', 'cuaca', 'tpi'];
        for ($i = 0; $i < ExternalInfo::REQUESTS_PER_MINUTE; $i++) {
            $this->getJson('/api/info/'.$types[$i % 3])->assertOk();
        }
        foreach ($types as $type) {
            $this->getJson('/api/info/'.$type.'?bypass=1')->assertStatus(429)
                ->assertHeader('Retry-After')->assertJsonPath('success', false);
        }
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.2'])->getJson('/api/info/kkp')->assertOk();
        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1']);
        $this->travel(61)->seconds();
        $this->getJson('/api/info/kkp')->assertOk();
        $this->travelBack();
    }

    public function test_guests_and_regular_users_cannot_mount_widgets(): void
    {
        foreach (self::WIDGETS as $widget) {
            $this->assertFalse($widget::canView());
            Livewire::test($widget)->assertForbidden();
        }
        $this->actingAs($this->user());
        foreach (self::WIDGETS as $widget) {
            $this->assertFalse($widget::canView());
            Livewire::test($widget)->assertForbidden();
        }
    }

    public function test_both_admin_roles_can_render_widgets_using_api_data(): void
    {
        foreach (['Admin Dinas', 'super_admin'] as $role) {
            $this->actingAs($this->user($role));
            $values = [
                InfoKkpWidget::class => app(ExternalInfo::class)->get('kkp')['data']['items'][0]['judul'],
                InfoCuacaWidget::class => $this->getJson('/api/info/cuaca')->assertOk()->json('data.items.0.kondisi'),
                PetaTpiWidget::class => app(ExternalInfo::class)->get('tpi')['data']['items'][0]['nama'],
            ];
            foreach ($values as $widget => $value) {
                $this->assertTrue($widget::canView());
                Livewire::test($widget)->assertSuccessful()->assertSee($value)->assertSee($widget === InfoCuacaWidget::class ? 'BMKG' : ExternalInfo::NOTICE);
            }
        }
    }

    public function test_widgets_recheck_authorization_on_subsequent_requests(): void
    {
        foreach (self::WIDGETS as $widget) {
            $admin = $this->user('Admin Dinas');
            $this->actingAs($admin);
            $component = Livewire::test($widget)->assertSuccessful();
            $admin->removeRole('Admin Dinas');
            $component->call('$refresh')->assertForbidden();
        }
    }

    public function test_widget_escapes_untrusted_data_without_raw_html(): void
    {
        $this->actingAs($this->user('Admin Dinas'));
        $payload = app(ExternalInfo::class)->get('kkp');
        $payload['data']['items'][0]['judul'] = '<script>alert(1)</script>';
        Cache::put('samudera:external-info:curated:v1:kkp', $payload, 300);
        Livewire::test(InfoKkpWidget::class)->assertSuccessful()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }
}