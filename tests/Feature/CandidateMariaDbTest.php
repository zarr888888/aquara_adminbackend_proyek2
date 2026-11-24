<?php

namespace Tests\Feature;

use App\Models\Pasar;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\Support\UsesIsolatedApplication;
use Tests\TestCase;

final class CandidateMariaDbTest extends TestCase
{
    use UsesIsolatedApplication;

    public function test_candidate_schema_orders_and_drill_are_isolated(): void
    {
        $password = getenv('SAMUDERA_RC_PASSWORD');
        if (!$password) {
            $this->markTestSkipped('Requires an empty disposable MariaDB container.');
        }
        $port = filter_var(getenv('SAMUDERA_RC_PORT'), FILTER_VALIDATE_INT);
        self::assertGreaterThan(1024, $port);
        config(['database.connections.rc' => ['driver' => 'mysql', 'host' => '127.0.0.1',
            'port' => $port, 'database' => 'samudera_rc_drill', 'username' => 'rc_drill',
            'password' => $password, 'charset' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'prefix' => ''],
            'database.default' => 'rc']);
        self::assertStringContainsString('MariaDB', DB::scalar('select version()'));
        self::assertSame('samudera_rc_drill', DB::scalar('select database()'));
        self::assertSame([], DB::connection()->getSchemaBuilder()->getTables());
        $migrator = app('migrator');
        $migrator->setConnection('rc');
        $migrator->getRepository()->createRepository();
        $migrator->run([database_path('migrations')]);
        // Real challenge generation, hashing and consumption; delivery stays in process.
        $otp = new class extends \App\Support\OtpService {
            public string $captured;
            protected function deliver(string $channel, string $target, string $code, string $purpose): void
            {
                $this->captured = $code;
            }
        };
        app()->instance(\App\Support\OtpService::class, $otp);
        $login = ['login_id' => 'rc@example.test', 'password' => bin2hex(random_bytes(16))];
        $this->postJson('/api/register', ['email' => $login['login_id'], 'password' => $login['password'],
            'phone' => '00000000000', 'name' => 'LATIHAN'])->assertCreated();
        $this->postJson('/api/login', $login)->assertForbidden();
        $this->postJson('/api/register/send-otp', ['login_id' => $login['login_id'], 'method' => 'email'])->assertOk();
        $this->postJson('/api/register/verify-otp', ['login_id' => $login['login_id'], 'otp' => $otp->captured])->assertOk();
        $this->postJson('/api/login', $login)->assertOk()->assertJsonStructure(['token']);
        $buyer = User::withoutEvents(fn () => User::factory()->create());
        $seller = User::withoutEvents(fn () => User::factory()->create());
        $other = User::withoutEvents(fn () => User::factory()->create());
        $panel = \Filament\Facades\Filament::getPanel('admin');
        self::assertFalse($buyer->canAccessPanel($panel));
        foreach (['Admin Dinas', 'super_admin'] as $role) {
            $admin = User::withoutEvents(fn () => User::factory()->create());
            $admin->assignRole(\Spatie\Permission\Models\Role::findOrCreate($role, 'web'));
            self::assertTrue($admin->canAccessPanel($panel));
        }
        $product = Pasar::create(['user_id' => $seller->id, 'nama_ikan' => 'LATIHAN', 'deskripsi' => 'LATIHAN',
            'nomor_wa' => '00000000000', 'lokasi' => 'LATIHAN', 'harga' => 100, 'stok' => 1000,
            'kategori' => 'laut', 'tipe_penjualan' => 'grosir']);
        Sanctum::actingAs($buyer);
        $id = $this->postJson('/api/pre-order', ['pasar_id' => $product->id, 'total_tonase' => '0.10',
            'tgl_pengambilan' => today()->toDateString()])->assertCreated()->json('data.id');
        $this->getJson('/api/pre-order')->assertOk()->assertJsonPath('data.data.0.id', $id);
        $this->putJson('/api/pre-order/'.$id.'/confirmation')->assertNotFound();
        $sos = $this->postJson('/api/lapor-darurat', ['jenis_sos' => 'Cuaca Ekstrem',
            'deskripsi' => 'LATIHAN RC - bukan peringatan darurat'])->assertCreated()->json('data.id');
        $this->getJson('/api/lapor-darurat/'.$sos)->assertOk()->assertJsonPath('data.status_laporan', 'Pending');
        Sanctum::actingAs($seller);
        $this->putJson('/api/pre-order/'.$id.'/confirmation', ['status_bayar'=>'Lunas'])->assertUnprocessable();
        $confirmed = $this->putJson('/api/pre-order/'.$id.'/confirmation')->assertOk()->assertJsonPath('data.status_bayar','Belum Bayar')->json('data.confirmed_at');
        self::assertNotNull($confirmed);
        $this->putJson('/api/pre-order/'.$id.'/confirmation')->assertOk()->assertJsonPath('data.confirmed_at',$confirmed);
        $this->getJson('/api/pre-order')->assertOk()->assertJsonPath('data.data.0.id', $id);
        $this->getJson('/api/pre-order/'.$id)->assertOk()->assertJsonPath('data.status_bayar', 'Belum Bayar');
        $this->getJson('/api/lapor-darurat/'.$sos)->assertNotFound();
        Sanctum::actingAs($other);
        $this->putJson('/api/pre-order/'.$id.'/confirmation')->assertNotFound();
        $this->getJson('/api/pre-order/'.$id)->assertNotFound();
        $retail = $product->replicate();
        $retail->tipe_penjualan = 'eceran';
        $retail->save();
        $offer = '/api/pasars/'.$retail->id.'/retail-offer';
        $url = '/api/retail-orders';
        $payload = ['pasar_id'=>$retail->id,'jumlah'=>'1.250','expected_unit'=>'kg','expected_price'=>1002];
        Sanctum::actingAs($buyer);
        $this->postJson($url, $payload)->assertConflict(); // legacy, no invented unit
        $this->putJson($offer, ['satuan_jual'=>'kg','harga'=>1002])->assertNotFound();
        Sanctum::actingAs($seller);
        $this->putJson($offer, ['satuan_jual'=>'kg','harga'=>1002])->assertOk();
        $this->getJson('/api/pasars')->assertOk()->assertJsonFragment(['satuan_jual'=>'kg']);
        $this->postJson($url,$payload)->assertForbidden();
        Sanctum::actingAs($buyer);
        $this->postJson($url,$payload+['total_harga'=>1])->assertUnprocessable();
        $this->postJson($url,array_merge($payload,['expected_price'=>1]))->assertConflict();
        foreach (['0','0.000','1.0001','-1','1e3'] as $bad) {
            $this->postJson($url,array_merge($payload,['jumlah'=>$bad]))->assertUnprocessable();
        }
        $oid=$this->postJson($url,$payload)->assertCreated()->assertJsonPath('data.total_harga',1253)->assertJsonPath('data.jumlah','1.250')->json('data.id');
        $confirm="$url/$oid/confirmation";
        $this->putJson($confirm)->assertNotFound();
        Sanctum::actingAs($other);
        $this->getJson($url)->assertOk()->assertJsonCount(0,'data.data');
        $this->putJson($confirm)->assertNotFound();
        foreach (['ekor','kemasan'] as $unit) {
            Sanctum::actingAs($seller);
            $this->putJson($offer, ['satuan_jual'=>$unit,'harga'=>123])->assertOk();
            Sanctum::actingAs($buyer);
            $new=array_merge($payload,['expected_unit'=>$unit,'expected_price'=>123]);
            $this->postJson($url,$new)->assertUnprocessable();
            $this->postJson($url,array_merge($new,['jumlah'=>'2']))->assertCreated()->assertJsonPath('data.total_harga',246);
        }
        $retail->delete();
        Sanctum::actingAs($seller);
        $this->getJson($url)->assertOk()->assertJsonCount(3,'data.data');
        $ts=$this->putJson($confirm)->assertOk()->assertJsonPath('data.status_bayar','Belum Bayar')->json('data.confirmed_at');
        self::assertNotNull($ts);
        $this->putJson($confirm)->assertOk()->assertJsonPath('data.confirmed_at',$ts);
        Sanctum::actingAs($buyer);
        $this->getJson($url)->assertOk()->assertJsonPath('data.data.2.id',$oid)->assertJsonPath('data.data.2.harga_satuan',1002)->assertJsonPath('data.data.2.total_harga',1253)->assertJsonPath('data.data.2.confirmed_at',$ts);
    }
}