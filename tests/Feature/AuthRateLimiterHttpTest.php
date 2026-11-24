<?php

namespace Tests\Feature;

use Illuminate\Cache\ArrayStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Testing\TestResponse;
use Tests\Support\UsesIsolatedApplication;
use Tests\TestCase;

class AuthRateLimiterHttpTest extends TestCase
{
    use UsesIsolatedApplication;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertTrue($this->app->environment('testing'));
        $this->assertFalse($this->app->configurationIsCached());
        $this->assertFalse($this->app->routesAreCached());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $this->assertInstanceOf(ArrayStore::class, Cache::store()->getStore());
        $this->assertSame('array', config('session.driver'));
        $this->assertSame('array', config('mail.default'));
        // No schema is needed: invalid payloads must stop before any application query.
        DB::connection()->enableQueryLog();
        $this->freezeTime();
    }

    protected function tearDown(): void
    {
        try {
            Http::assertNothingSent();
            Mail::assertNothingOutgoing();
            Notification::assertNothingSent();
            Queue::assertNothingPushed();
            $this->assertSame([], DB::connection()->getQueryLog());
        } finally {
            $this->travelBack();
            parent::tearDown();
        }
    }

    private function assertBuckets(int $group, int $login): void
    {
        $signature = sha1('|127.0.0.1');
        $this->assertSame($group, RateLimiter::attempts('auth-group:'.$signature));
        $this->assertSame($login, RateLimiter::attempts('auth-login:'.$signature));
    }

    private function assertThrottled(TestResponse $response, int $limit, int $retry = 60): void
    {
        $response->assertStatus(429)
            ->assertHeader('Content-Type', 'application/json')
            ->assertExactJson(['message' => 'Too Many Attempts.'])
            ->assertHeader('X-RateLimit-Limit', (string) $limit)
            ->assertHeader('X-RateLimit-Remaining', '0')
            ->assertHeader('Retry-After', (string) $retry)
            ->assertHeader('X-RateLimit-Reset', (string) now()->addSeconds($retry)->timestamp);
    }

    public function test_login_limit_renders_json_and_recovers_after_the_window(): void
    {
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->postJson('/api/login', [])->assertUnprocessable()
                ->assertJsonValidationErrors(['login_id', 'password']);
            $this->assertBuckets($attempt, $attempt);
        }
        $this->assertThrottled($this->postJson('/api/login', []), 5);
        $this->assertBuckets(6, 5);
        $this->postJson('/api/register/send-otp', [])->assertUnprocessable()
            ->assertJsonValidationErrors('login_id');
        $this->assertBuckets(7, 5);
        $this->travel(59)->seconds();
        $this->assertThrottled($this->postJson('/api/login', []), 5, 1);
        $this->travel(2)->seconds();
        $this->postJson('/api/login', [])->assertUnprocessable()
            ->assertJsonValidationErrors(['login_id', 'password']);
        $this->assertBuckets(1, 1);
    }

    public function test_auth_endpoints_share_ten_requests_without_consuming_login_bucket(): void
    {
        $endpoints = [
            '/api/register' => ['name', 'phone', 'password'],
            '/api/register/send-otp' => ['login_id'],
            '/api/register/verify-otp' => ['login_id'],
            '/api/lupa-password/send-otp' => ['login_id'],
            '/api/lupa-password/verify' => ['new_password'],
            '/api/google-login' => ['id_token'],
            '/api/send-otp-wa' => ['phone'],
            '/api/verify-otp-wa' => ['phone'],
        ];
        $count = 0;
        foreach ($endpoints as $path => $errors) {
            $this->postJson($path, [])->assertUnprocessable()->assertJsonValidationErrors($errors);
            $this->assertBuckets(++$count, 0);
        }
        for ($attempt = 1; $attempt <= 2; $attempt++) {
            $this->postJson('/api/login', [])->assertUnprocessable()
                ->assertJsonValidationErrors(['login_id', 'password']);
            $this->assertBuckets(8 + $attempt, $attempt);
        }
        $this->assertThrottled($this->postJson('/api/login', []), 10);
        $this->assertThrottled($this->postJson('/api/register/send-otp', []), 10);
        $this->assertBuckets(10, 2);
        $this->travel(61)->seconds();
        $this->postJson('/api/register/send-otp', [])->assertUnprocessable()
            ->assertJsonValidationErrors('login_id');
        $this->postJson('/api/login', [])->assertUnprocessable()
            ->assertJsonValidationErrors(['login_id', 'password']);
        $this->assertBuckets(2, 1);
    }
}
