<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\CommunityPost;
use App\Models\Pasar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SecurityAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_non_public_api_route_requires_sanctum(): void
    {
        $public = ['register', 'register/send-otp', 'register/verify-otp', 'lupa-password/send-otp',
            'lupa-password/verify', 'login', 'google-login', 'send-otp-wa', 'verify-otp-wa',
            'info/kkp', 'info/cuaca', 'info/tpi'];
        foreach (Route::getRoutes() as $route) {
            if (! str_starts_with($route->uri(), 'api/')) {
                continue;
            }
            if (in_array(substr($route->uri(), 4), $public, true)) {
                $this->assertNotEmpty(array_filter($route->gatherMiddleware(), fn ($m) => str_starts_with($m, 'throttle:')));
                continue;
            }
            $this->assertContains('auth:sanctum', $route->gatherMiddleware(), $route->uri());
            $uri = '/'.preg_replace('/\{[^}]+\}/', '1', $route->uri());
            $this->json($route->methods()[0], $uri)->assertUnauthorized();
        }
    }

    public function test_cors_allows_only_explicit_origin(): void
    {
        config(['cors.allowed_origins' => ['https://portal.example.test']]);
        $this->options('/api/pasars', [], ['Origin' => 'https://portal.example.test',
            'Access-Control-Request-Method' => 'POST', 'Access-Control-Request-Headers' => 'authorization,content-type'])
            ->assertHeader('Access-Control-Allow-Origin', 'https://portal.example.test');
        $denied = $this->options('/api/pasars', [], ['Origin' => 'https://evil.example', 'Access-Control-Request-Method' => 'POST']);
        // A single-origin CORS policy may emit its fixed allowed origin, never the attacker or '*'.
        $this->assertNotContains($denied->headers->get('Access-Control-Allow-Origin'), ['https://evil.example', '*']);
        $this->getJson('/api/pasars', ['Origin' => 'https://portal.example.test'])->assertUnauthorized();
        $this->assertFalse(config('cors.supports_credentials'));
        $this->assertNotContains('*', config('cors.allowed_origins'));
    }

    public function test_forum_and_pasar_ownership_and_mine_filter(): void
    {
        $owner = User::withoutEvents(fn () => User::factory()->create());
        $attacker = User::withoutEvents(fn () => User::factory()->create());
        $post = CommunityPost::create(['user_id' => $owner->id, 'author_name' => 'Owner', 'content' => 'Private owner post']);
        $pasar = Pasar::create(['user_id' => $owner->id, 'nama_ikan' => 'Bandeng', 'deskripsi' => 'Segar',
            'harga' => 10000, 'nomor_wa' => '08123456789', 'lokasi' => 'Indramayu']);
        Sanctum::actingAs($attacker);
        $this->postJson('/api/forum/'.$post->id.'/update', ['content' => 'Hijack'])->assertForbidden();
        $this->deleteJson('/api/forum/'.$post->id)->assertForbidden();
        $this->postJson('/api/pasars/'.$pasar->id.'/update', [])->assertForbidden();
        $this->deleteJson('/api/pasars/'.$pasar->id)->assertForbidden();
        $this->getJson('/api/forum?mine=1&user_id='.$owner->id)->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/user/'.$owner->id)->assertNotFound();
        Sanctum::actingAs($owner);
        $this->getJson('/api/forum?mine=1')->assertOk()->assertJsonCount(1, 'data');
        $this->postJson('/api/forum/'.$post->id.'/update', ['content' => 'Updated'])->assertOk();
        foreach (['fish-types', 'stocks', 'posts', 'forum', 'pasars'] as $endpoint) {
            $this->getJson('/api/'.$endpoint)->assertOk();
        }
    }

    public function test_google_identity_cannot_be_forged_from_profile_fields(): void
    {
        $this->postJson('/api/google-login', ['email' => 'victim@example.test', 'name' => 'Victim', 'google_id' => 'fake'])
            ->assertUnprocessable()->assertJsonValidationErrors('id_token');
        config(['services.google.client_id' => 'test-client']);
        $this->mock(\Google\Auth\AccessToken::class)->shouldReceive('verify')->once()->andReturn(false);
        $this->postJson('/api/google-login', ['id_token' => 'forged'])->assertUnauthorized();
    }

    public function test_user_serialization_never_exposes_otp(): void
    {
        $user = new User;
        $user->otp = '123456';
        $user->otp_expired_at = now();
        $this->assertArrayNotHasKey('otp', $user->toArray());
        $this->assertArrayNotHasKey('otp_expired_at', $user->toArray());
    }
}