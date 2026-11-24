<?php

namespace Tests\Feature;

use App\Filament\Resources\PreOrderResource\Pages\ListPreOrders;
use App\Models\Pasar;
use App\Models\PreOrder;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\Support\UsesIsolatedApplication;
use Tests\TestCase;

class PreOrderTest extends TestCase
{
    use UsesIsolatedApplication;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        foreach ([
            '0001_01_01_000000_create_users_table.php',
            '2026_02_14_201420_create_personal_access_tokens_table.php',
            '2026_02_28_074508_create_pasars_table.php',
            '2026_04_09_050518_create_permission_tables.php',
            '2026_09_27_062343_add_katalog_fields_to_pasars_table.php',
            '2026_09_27_123549_create_pre_orders_table.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
    }

    private function user(): User
    {
        return User::withoutEvents(fn () => User::factory()->create());
    }

    private function product(User $seller, string $type = 'grosir'): Pasar
    {
        return Pasar::create([
            'user_id' => $seller->id, 'nama_ikan' => 'Bandeng', 'deskripsi' => 'Bandeng segar',
            'harga' => 25000, 'nomor_wa' => '081234567890', 'lokasi' => 'Indramayu',
            'stok' => 5000, 'kategori' => 'laut', 'tipe_penjualan' => $type,
        ]);
    }

    private function payload(Pasar $product): array
    {
        return ['pasar_id' => $product->id, 'total_tonase' => '1.25', 'tgl_pengambilan' => today()->toDateString()];
    }

    private function order(User $buyer, Pasar $product): PreOrder
    {
        $order = new PreOrder($this->payload($product));
        $order->user()->associate($buyer);
        $order->save();

        return $order;
    }

    public function test_endpoints_require_authentication_and_reject_invalid_bearer_token(): void
    {
        $this->getJson('/api/pre-order')->assertUnauthorized();
        $this->postJson('/api/pre-order')->assertUnauthorized();
        $this->getJson('/api/pre-order/1')->assertUnauthorized();
        $this->withToken('invalid')->getJson('/api/pre-order')->assertUnauthorized();
    }

    public function test_valid_order_uses_authenticated_buyer_and_default_status(): void
    {
        $buyer = $this->user();
        Sanctum::actingAs($buyer);
        $this->postJson('/api/pre-order', $this->payload($this->product($this->user())))
            ->assertCreated()->assertJsonPath('data.user_id', $buyer->id)
            ->assertJsonPath('data.status_bayar', 'Belum Bayar')
            ->assertJsonPath('data.total_tonase', '1.25');
        $this->assertDatabaseCount('pre_orders', 1);
    }

    public function test_api_rejects_payment_and_ownership_injection_even_if_null(): void
    {
        Sanctum::actingAs($this->user());
        $payload = $this->payload($this->product($this->user()));
        foreach (['status_bayar' => 'Lunas', 'user_id' => 999] as $key => $value) {
            foreach ([$value, null] as $injection) {
                $this->postJson('/api/pre-order', [...$payload, $key => $injection])
                    ->assertUnprocessable()->assertJsonValidationErrors($key);
            }
        }
        $this->assertDatabaseCount('pre_orders', 0);
    }

    public function test_model_guards_payment_and_ownership_from_mass_assignment(): void
    {
        $model = new PreOrder(['status_bayar' => 'Lunas', 'user_id' => 999, 'total_tonase' => '2']);
        $this->assertSame('Belum Bayar', $model->status_bayar);
        $this->assertNull($model->user_id);
        $this->assertFalse($model->isFillable('status_bayar'));
        $this->assertFalse($model->isFillable('user_id'));
    }

    public function test_invalid_tonnage_dates_and_products_are_rejected(): void
    {
        Sanctum::actingAs($this->user());
        $payload = $this->payload($this->product($this->user()));
        foreach ([0, -1, 'abc', '0.001', '100000000', '1.234', null] as $value) {
            $this->postJson('/api/pre-order', [...$payload, 'total_tonase' => $value])
                ->assertUnprocessable()->assertJsonValidationErrors('total_tonase');
        }
        foreach ([today()->subDay()->toDateString(), '2026-02-30', 'tomorrow'] as $date) {
            $this->postJson('/api/pre-order', [...$payload, 'tgl_pengambilan' => $date])
                ->assertUnprocessable()->assertJsonValidationErrors('tgl_pengambilan');
        }
        foreach ([999, $this->product($this->user(), 'eceran')->id] as $id) {
            $this->postJson('/api/pre-order', [...$payload, 'pasar_id' => $id])
                ->assertUnprocessable()->assertJsonValidationErrors('pasar_id');
        }
        $this->assertDatabaseCount('pre_orders', 0);
    }

    public function test_buyer_and_seller_only_see_related_orders_and_cannot_change_payment(): void
    {
        $buyer = $this->user();
        $seller = $this->user();
        $order = $this->order($buyer, $this->product($seller));
        $unrelated = $this->order($this->user(), $this->product($this->user()));

        foreach ([$buyer, $seller] as $user) {
            Sanctum::actingAs($user);
            $this->getJson('/api/pre-order?user_id='.$unrelated->user_id.'&pasar_id='.$unrelated->pasar_id)
                ->assertOk()->assertJsonCount(1, 'data.data')->assertJsonPath('data.data.0.id', $order->id);
            $this->getJson('/api/pre-order/'.$order->id)->assertOk();
            $this->getJson('/api/pre-order/'.$unrelated->id)->assertNotFound();
            $this->assertTrue(Gate::forUser($user)->allows('view', $order));
            $this->assertFalse(Gate::forUser($user)->allows('view', $unrelated));
            $this->assertFalse(Gate::forUser($user)->allows('update', $order));
            $this->patchJson('/api/pre-order/'.$order->id, ['status_bayar' => 'Lunas'])->assertStatus(405);
        }
        Sanctum::actingAs($this->user());
        $this->getJson('/api/pre-order')->assertOk()->assertJsonCount(0, 'data.data');
        $this->getJson('/api/pre-order/'.$order->id)->assertNotFound();
        $this->getJson('/api/pre-order/999')->assertNotFound();
        $this->assertSame('Belum Bayar', $order->refresh()->status_bayar);
    }

    public function test_detail_denials_are_generic_and_semantically_equal(): void
    {
        config(['app.debug' => false]);
        $seller = $this->user();
        $buyer = $this->user();
        $order = $this->order($buyer, $this->product($seller));
        $missingId = 2147483647;
        $this->assertFalse(PreOrder::whereKey($missingId)->exists());
        Sanctum::actingAs($this->user());

        $denied = $this->getJson('/api/pre-order/'.$order->id)->assertNotFound();
        $missing = $this->getJson('/api/pre-order/'.$missingId)->assertNotFound();
        $this->assertSame('application/json', $denied->headers->get('Content-Type'));
        $this->assertSame($denied->headers->get('Content-Type'), $missing->headers->get('Content-Type'));
        // Exact allowed structure excludes model names, IDs, query, trace and business data.
        $denied->assertExactJson(['message' => 'Not Found']);
        $missing->assertExactJson(['message' => 'Not Found']);
        $this->assertEquals($denied->json(), $missing->json());

        foreach ([$buyer, $seller] as $participant) {
            Sanctum::actingAs($participant);
            $this->getJson('/api/pre-order/'.$order->id)->assertOk()
                ->assertJsonPath('data.id', $order->id)
                ->assertJsonPath('data.user_id', $buyer->id)
                ->assertJsonPath('data.pasar_id', $order->pasar_id)
                ->assertJsonPath('data.total_tonase', '1.25')
                ->assertJsonPath('data.tgl_pengambilan', today()->toDateString())
                ->assertJsonPath('data.status_bayar', 'Belum Bayar');
        }
    }

    public function test_admin_can_update_only_valid_payment_status_through_filament(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $order = $this->order($this->user(), $this->product($this->user()));
        foreach (PreOrder::ADMIN_ROLES as $role) {
            $admin = $this->user();
            Role::findOrCreate($role, 'web');
            $admin->assignRole($role);
            $this->actingAs($admin);
            Livewire::test(ListPreOrders::class)->assertSuccessful()->assertCanSeeTableRecords([$order])
                ->mountTableAction('view', $order)->assertSee('Bandeng');
            foreach (['DP', 'Lunas'] as $status) {
                Livewire::test(ListPreOrders::class)
                    ->callTableAction('edit', $order, data: ['status_bayar' => $status])
                    ->assertHasNoTableActionErrors();
                $this->assertSame($status, $order->refresh()->status_bayar);
            }
            Livewire::test(ListPreOrders::class)
                ->callTableAction('edit', $order, data: ['status_bayar' => 'Invalid'])
                ->assertHasTableActionErrors(['status_bayar']);
            $this->assertSame('Lunas', $order->refresh()->status_bayar);
        }
    }

    public function test_buyer_and_seller_cannot_open_admin_resource(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $buyer = $this->user();
        $seller = $this->user();
        $this->order($buyer, $this->product($seller));
        foreach ([$buyer, $seller] as $user) {
            $this->actingAs($user);
            Livewire::test(ListPreOrders::class)->assertForbidden();
        }
    }
}
