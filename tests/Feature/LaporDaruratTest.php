<?php

namespace Tests\Feature;

use App\Filament\Resources\LaporDaruratResource;
use App\Filament\Resources\LaporDaruratResource\Pages\ListLaporDarurats;
use App\Models\LaporDarurat;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Mockery;
use Spatie\Permission\Models\Role;
use Tests\Support\UsesIsolatedApplication;
use Tests\TestCase;

class LaporDaruratTest extends TestCase
{
    use UsesIsolatedApplication;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            '0001_01_01_000000_create_users_table.php',
            '2026_04_09_050518_create_permission_tables.php',
            '2026_09_27_120903_create_lapor_darurats_table.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
    }

    private function user(): User
    {
        return User::withoutEvents(fn () => User::factory()->create());
    }

    private function report(User $user): LaporDarurat
    {
        return LaporDarurat::create([
            'user_id' => $user->id,
            'jenis_sos' => 'Wabah Penyakit',
            'deskripsi' => 'Ikan mati mendadak.',
            'foto' => 'https://res.cloudinary.com/demo/image/upload/v1/samudera/sos-fotos/bukti.jpg',
        ]);
    }

    public function test_all_api_endpoints_require_authentication(): void
    {
        $this->getJson('/api/lapor-darurat')->assertUnauthorized();
        $this->postJson('/api/lapor-darurat')->assertUnauthorized();
        $this->getJson('/api/lapor-darurat/1')->assertUnauthorized();
    }

    public function test_uploaded_report_ignores_privileged_fields_and_reaches_the_officer_panel(): void
    {
        $user = $this->user();
        Sanctum::actingAs($user);
        $disk = Mockery::mock();
        $disk->shouldReceive('putFile')->once()->with('samudera/sos-fotos', Mockery::type(UploadedFile::class))->andReturn('samudera/sos-fotos/bukti.jpg');
        $url = 'https://res.cloudinary.com/demo/image/upload/v1/samudera/sos-fotos/bukti.jpg';
        $disk->shouldReceive('url')->once()->andReturn($url);
        Storage::shouldReceive('disk')->with('cloudinary')->twice()->andReturn($disk);

        $response = $this->postJson('/api/lapor-darurat', [
            'jenis_sos' => 'Wabah Penyakit', 'deskripsi' => 'Ikan mati mendadak.',
            'foto' => UploadedFile::fake()->image('bukti.jpg'),
            'user_id' => 999, 'admin_id' => 999, 'status_laporan' => 'Selesai',
        ])->assertCreated()->assertJsonPath('data.foto', $url)
            ->assertJsonPath('data.user_id', $user->id)
            ->assertJsonPath('data.status_laporan', 'Pending')->assertJsonPath('data.admin_id', null);

        $report = LaporDarurat::findOrFail($response->json('data.id'));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $officer = $this->user();
        $officer->assignRole(Role::findOrCreate('Admin Dinas', 'web'));
        $this->actingAs($officer, 'web');
        $this->get(LaporDaruratResource::getUrl())->assertOk();
        Livewire::test(ListLaporDarurats::class)
            ->assertCanSeeTableRecords([$report])
            ->mountTableAction('view', $report)
            ->assertSeeText('Wabah Penyakit')->assertSeeText('Ikan mati mendadak.')->assertSeeText('Pending');
    }

    public function test_store_without_photo_and_validation(): void
    {
        Sanctum::actingAs($this->user());
        $this->postJson('/api/lapor-darurat', ['jenis_sos' => 'Cuaca Ekstrem', 'deskripsi' => 'Badai.'])
            ->assertCreated()->assertJsonPath('data.foto', null);
        $this->postJson('/api/lapor-darurat', [
            'jenis_sos' => 'Tidak valid', 'deskripsi' => '',
            'foto' => UploadedFile::fake()->create('bukti.pdf', 10, 'application/pdf'),
        ])->assertUnprocessable()->assertJsonValidationErrors(['jenis_sos', 'deskripsi', 'foto']);
    }

    public function test_upload_failure_does_not_create_report(): void
    {
        Sanctum::actingAs($this->user());
        $disk = Mockery::mock();
        $disk->shouldReceive('putFile')->once()->andReturn(false);
        Storage::shouldReceive('disk')->once()->with('cloudinary')->andReturn($disk);
        $this->postJson('/api/lapor-darurat', [
            'jenis_sos' => 'Wabah Penyakit', 'deskripsi' => 'Ikan mati.',
            'foto' => UploadedFile::fake()->image('bukti.jpg'),
        ])->assertStatus(503);
        $this->assertDatabaseCount('lapor_darurats', 0);
    }

    public function test_history_and_details_are_owner_only(): void
    {
        $user = $this->user();
        $own = $this->report($user);
        $other = $this->report($this->user());
        Sanctum::actingAs($user);
        $this->getJson('/api/lapor-darurat?user_id='.$other->user_id)
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $own->id);
        $this->getJson('/api/lapor-darurat/'.$own->id)->assertOk();
        $this->getJson('/api/lapor-darurat/'.$other->id)->assertNotFound();
        $this->getJson('/api/lapor-darurat/999')->assertNotFound();
    }

    public function test_admin_roles_can_view_photo_and_update_status_in_filament(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $report = $this->report($this->user());
        foreach (LaporDarurat::ADMIN_ROLES as $roleName) {
            $admin = $this->user();
            Role::findOrCreate($roleName, 'web');
            $admin->assignRole($roleName);
            $this->actingAs($admin);
            Livewire::test(ListLaporDarurats::class)
                ->assertSuccessful()->assertCanSeeTableRecords([$report])
                ->mountTableAction('view', $report)->assertSee('Ikan mati mendadak.')
                ->assertSee($report->foto, false);
            Livewire::test(ListLaporDarurats::class)
                ->callTableAction('edit', $report, data: ['status_laporan' => 'Diproses', 'admin_id' => null])
                ->assertHasNoTableActionErrors();
            $this->assertSame('Diproses', $report->refresh()->status_laporan);
            $this->assertEquals($admin->id, $report->admin_id);
            Livewire::test(ListLaporDarurats::class)
                ->callTableAction('edit', $report, data: ['status_laporan' => 'Selesai', 'admin_id' => $admin->id])
                ->assertHasNoTableActionErrors();
            $this->assertSame('Selesai', $report->refresh()->status_laporan);
        }
    }

    public function test_regular_user_cannot_access_admin_resource(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($this->user());
        $this->get(LaporDaruratResource::getUrl())->assertForbidden();
        Livewire::test(ListLaporDarurats::class)->assertForbidden();
    }

    public function test_guest_is_redirected_to_panel_login(): void
    {
        $panel = Filament::getPanel('admin');
        Filament::setCurrentPanel($panel);
        $this->get(LaporDaruratResource::getUrl())->assertRedirect($panel->getLoginUrl());
    }
}