<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\OtpService;
use App\Support\CloudinaryFileUpload;
use App\Support\CloudinaryUrl;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\CapturingOtpService;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::withoutEvents(fn () => User::factory()->create(['phone' => '081234567890']));
    }

    private function service(): CapturingOtpService
    {
        $service = new CapturingOtpService;
        $this->app->instance(OtpService::class, $service);

        return $service;
    }

    public function test_all_purpose_pairs_are_isolated_and_attempt_is_persisted(): void
    {
        $service = $this->service();
        $user = $this->user();
        foreach (OtpService::PURPOSES as $source) {
            foreach (OtpService::PURPOSES as $destination) {
                if ($source === $destination) { continue; }
                DB::table('otp_challenges')->delete();
                $service->issue($user, $source, 'email', $user->email);
                try {
                    $service->consume($user, $destination, $service->code, fn () => $this->fail('Cross-purpose accepted'));
                    $this->fail('Expected rejection');
                } catch (HttpException $e) {
                    $this->assertSame(401, $e->getStatusCode());
                }
                $this->assertSame(1, DB::table('otp_challenges')->value('attempts'));
            }
        }
    }

    public function test_api_reset_rejects_login_code_and_success_is_single_use(): void
    {
        $user = $this->user();
        $service = $this->service();
        $service->issue($user, 'login', 'wa', $user->phone);
        $this->postJson('/api/lupa-password/verify', ['login_id' => $user->email, 'otp' => $service->code,
            'new_password' => 'NewSecretPassword!123'])->assertUnauthorized();
        $this->travel(61)->seconds();
        $service->issue($user, 'reset', 'email', $user->email);
        $code = $service->code;
        $row = DB::table('otp_challenges')->first();
        $this->assertNotSame($code, $row->code_hash);
        $this->assertTrue(Hash::check($code, $row->code_hash));
        $user->createToken('old-device');
        $body = ['login_id' => $user->email, 'otp' => $code, 'new_password' => 'NewSecretPassword!123'];
        $this->postJson('/api/lupa-password/verify', $body)->assertOk();
        $this->assertTrue(Hash::check($body['new_password'], $user->refresh()->password));
        $this->assertSame(0, $user->tokens()->count());
        $this->postJson('/api/lupa-password/verify', $body)->assertUnauthorized();
        $this->travelBack();
    }

    public function test_attempt_lockout_cannot_be_reset_by_resend_or_purpose_change(): void
    {
        $user = $this->user();
        $service = $this->service();
        $service->issue($user, 'reset', 'email', $user->email);
        for ($i = 1; $i <= 5; $i++) {
            $this->postJson('/api/lupa-password/verify', ['login_id' => $user->email, 'otp' => '000000',
                'new_password' => 'NewSecretPassword!123'])->assertStatus($i === 5 ? 429 : 401);
        }
        $this->travel(61)->seconds();
        $this->postJson('/api/register/send-otp', ['login_id' => $user->email, 'method' => 'email'])->assertStatus(429);
        $this->assertSame(5, DB::table('otp_challenges')->value('attempts'));
        $this->travel(15)->minutes();
        $this->postJson('/api/lupa-password/send-otp', ['login_id' => $user->email, 'method' => 'email'])->assertOk();
        $this->assertSame(0, DB::table('otp_challenges')->value('attempts'));
        $this->travelBack();
    }

    public function test_expired_otp_is_rejected_and_contact_target_cannot_be_substituted(): void
    {
        $user = $this->user();
        $service = $this->service();
        $service->issue($user, 'reset', 'email', $user->email);
        $this->travel(6)->minutes();
        $this->postJson('/api/lupa-password/verify', ['login_id' => $user->email, 'otp' => $service->code,
            'new_password' => 'NewSecretPassword!123'])->assertUnauthorized();
        Sanctum::actingAs($user);
        $this->postJson('/api/ubah-kontak/send-otp', ['method' => 'email', 'target_contact' => 'new@example.test'])->assertOk();
        $code = $service->code;
        $this->postJson('/api/ubah-kontak', ['method' => 'email', 'target_contact' => 'attacker@example.test', 'otp' => $code])->assertUnauthorized();
        $this->deleteJson('/api/hapus-akun', ['otp' => $code])->assertUnauthorized();
        $this->postJson('/api/ubah-kontak', ['method' => 'email', 'target_contact' => 'new@example.test', 'otp' => $code])->assertOk();
        $this->assertSame('new@example.test', $user->refresh()->email);
        $this->assertSame('081234567890', $user->phone);
        $this->travelBack();
    }

    public function test_seeder_has_no_implicit_admins_and_never_overwrites_password(): void
    {
        config(['admin_seed' => ['super_admin' => ['email' => null, 'password' => null]]]);
        $this->seed();
        $this->assertSame(0, User::count());
        config(['admin_seed.super_admin' => ['email' => 'secure@example.test', 'password' => 'LongRandomSecret!12345']]);
        $this->seed();
        $user = User::first();
        $this->assertTrue(Hash::check('LongRandomSecret!12345', $user->password));
        $this->assertSame('bcrypt', password_get_info($user->password)['algoName']);
        config(['admin_seed.super_admin.password' => 'AnotherRandomSecret!456']);
        $this->seed();
        $this->assertSame(1, User::count());
        $this->assertTrue(Hash::check('LongRandomSecret!12345', $user->refresh()->password));
    }

    public function test_filament_uploads_use_shared_cloudinary_component(): void
    {
        $field = CloudinaryFileUpload::make('image');
        $this->assertSame('cloudinary', $field->getDiskName());
        $this->assertSame(5120, $field->getMaxSize());
        $this->assertFalse(CloudinaryUrl::isCloudinaryUrl('https://evil.test/cloudinary.com/a'));
        $this->assertFalse(CloudinaryUrl::isCloudinaryUrl('https://res.cloudinary.com.evil.test/a'));
        foreach (['Pasar', 'FishType', 'Post', 'AiConsultation'] as $resource) {
            $source = file_get_contents(app_path('Filament/Resources/'.$resource.'Resource.php'));
            $this->assertStringContainsString('CloudinaryFileUpload::make', $source);
            $this->assertStringNotContainsString('Components\\FileUpload::make', $source);
        }
    }

    public function test_filament_upload_callback_uses_trait_and_returns_cloudinary_url(): void
    {
        $field = CloudinaryFileUpload::make('image')->directory('fish-images');
        $property = new \ReflectionProperty(\Filament\Forms\Components\BaseFileUpload::class, 'saveUploadedFileUsing');
        $callback = $property->getValue($field);
        $disk = $this->createMock(FilesystemAdapter::class);
        $disk->expects($this->once())->method('putFile')
            ->with('samudera/fish-images', $this->isInstanceOf(UploadedFile::class))
            ->willReturn('samudera/fish-images/test.jpg');
        $url = 'https://res.cloudinary.com/demo/image/upload/v1/samudera/fish-images/test.jpg';
        $disk->expects($this->once())->method('url')
            ->with('samudera/fish-images/test.jpg')->willReturn($url);
        \Illuminate\Support\Facades\Storage::shouldReceive('disk')->twice()->with('cloudinary')->andReturn($disk);
        $this->assertSame($url, $callback(\Illuminate\Http\UploadedFile::fake()->image('test.jpg'), $field));
    }

    public function test_resend_cooldown_and_three_send_limit_apply_across_purposes(): void
    {
        $user = $this->user();
        $this->service();
        $body = ['login_id' => $user->email, 'method' => 'email'];
        $this->postJson('/api/lupa-password/send-otp', $body)->assertOk();
        $this->postJson('/api/register/send-otp', $body)->assertStatus(429);
        for ($i = 0; $i < 2; $i++) {
            $this->travel(61)->seconds();
            $this->postJson('/api/register/send-otp', $body)->assertOk();
        }
        $this->travel(61)->seconds();
        $this->postJson('/api/lupa-password/send-otp', $body)->assertStatus(429);
        $this->travelBack();
    }
}