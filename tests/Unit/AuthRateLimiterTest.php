<?php

namespace Tests\Unit;

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\RateLimiter;
use Illuminate\Cache\Repository;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Router;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class AuthRateLimiterTest extends TestCase
{
    private Router $router;

    private RateLimiter $limiter;

    protected function setUp(): void
    {
        parent::setUp();

        // Deliberately do not bootstrap the application, dotenv, DB or providers.
        $container = new Container;
        $this->router = new Router(new Dispatcher($container), $container);
        $previousRouter = Route::getFacadeRoot();
        Route::swap($this->router);
        try {
            require dirname(__DIR__, 2).'/routes/api.php';
        } finally {
            Route::swap($previousRouter);
        }
        $this->limiter = new RateLimiter(new Repository(new ArrayStore));
        Carbon::setTestNow(Carbon::parse('2026-10-18 12:00:00 UTC'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function middleware(string $path): array
    {
        $route = $this->router->getRoutes()->match(Request::create($path, 'POST'));

        return array_values(array_filter($route->middleware(),
            fn (string $middleware) => str_starts_with($middleware, 'throttle:')));
    }

    private function send(string $path = '/login', ?array $middleware = null, string $ip = '192.0.2.1', ?Response $response = null): Response
    {
        $request = Request::create($path, 'POST', server: ['REMOTE_ADDR' => $ip]);
        $route = $this->router->getRoutes()->match($request);
        $request->setRouteResolver(fn () => $route);
        $request->setUserResolver(fn () => null);
        $next = fn () => $response ?? new Response('isolated endpoint', 200);

        foreach (array_reverse($middleware ?? $this->middleware($path)) as $definition) {
            $parameters = explode(',', substr($definition, strlen('throttle:')));
            $downstream = $next;
            $next = fn ($request) => (new ThrottleRequests($this->limiter))->handle($request, $downstream, ...$parameters);
        }

        // Keep the real exception status/headers; no app exception handler or logging.
        try {
            return $next($request);
        } catch (ThrottleRequestsException $exception) {
            self::assertSame('Too Many Attempts.', $exception->getMessage());

            return new Response($exception->getMessage(), $exception->getStatusCode(), $exception->getHeaders());
        }
    }

    private function key(string $definition, string $ip = '192.0.2.1'): string
    {
        $parameters = explode(',', substr($definition, strlen('throttle:')));

        return ($parameters[2] ?? '').sha1('|'.$ip);
    }

    public function test_unprefixed_middleware_reproduces_the_original_collision(): void
    {
        $legacy = ['throttle:10,1', 'throttle:5,1'];
        self::assertSame(200, $this->send(middleware: $legacy)->getStatusCode());
        self::assertSame(2, $this->limiter->attempts($this->key($legacy[0])));
        self::assertSame(200, $this->send(middleware: $legacy)->getStatusCode());
        self::assertSame(4, $this->limiter->attempts($this->key($legacy[0])));
        $blocked = $this->send(middleware: $legacy);
        self::assertSame(429, $blocked->getStatusCode());
        self::assertSame('60', $blocked->headers->get('Retry-After'));
        self::assertSame(5, $this->limiter->attempts($this->key($legacy[0])));
    }

    public function test_actual_login_allows_five_requests_and_has_independent_buckets(): void
    {
        $definitions = $this->middleware('/login');
        self::assertSame(['throttle:10,1,auth-group:', 'throttle:5,1,auth-login:'], $definitions);
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            self::assertSame(200, $this->send()->getStatusCode(), 'Login attempt '.$attempt);
        }
        self::assertNotSame($this->key($definitions[0]), $this->key($definitions[1]));
        self::assertSame(5, $this->limiter->attempts($this->key($definitions[0])));
        self::assertSame(5, $this->limiter->attempts($this->key($definitions[1])));
        $blocked = $this->send();
        self::assertSame(429, $blocked->getStatusCode());
        self::assertSame('5', $blocked->headers->get('X-RateLimit-Limit'));
        self::assertSame('0', $blocked->headers->get('X-RateLimit-Remaining'));
        self::assertSame('60', $blocked->headers->get('Retry-After'));
        // The outer group still counts requests rejected by the inner login limit.
        self::assertSame(6, $this->limiter->attempts($this->key($definitions[0])));
        self::assertSame(5, $this->limiter->attempts($this->key($definitions[1])));
        self::assertSame((string) Carbon::now()->addMinute()->timestamp, $blocked->headers->get('X-RateLimit-Reset'));
        self::assertSame(200, $this->send('/register/send-otp')->getStatusCode());
        self::assertSame(5, $this->limiter->attempts($this->key($definitions[1])));
        self::assertSame(200, $this->send(ip: '192.0.2.2')->getStatusCode());
        Carbon::setTestNow(Carbon::now()->addSeconds(59));
        self::assertSame('1', $this->send()->headers->get('Retry-After'));
        Carbon::setTestNow(Carbon::now()->addSecond());
        self::assertSame(200, $this->send()->getStatusCode());
        self::assertSame(1, $this->limiter->attempts($this->key($definitions[0])));
        self::assertSame(1, $this->limiter->attempts($this->key($definitions[1])));
    }

    public function test_auth_group_is_shared_and_still_blocks_the_eleventh_request(): void
    {
        $paths = ['/register', '/register/send-otp', '/register/verify-otp',
            '/lupa-password/send-otp', '/lupa-password/verify', '/google-login',
            '/send-otp-wa', '/verify-otp-wa'];
        $group = $this->middleware('/login')[0];
        foreach ($paths as $path) {
            self::assertSame([$group], $this->middleware($path));
            self::assertSame(200, $this->send($path)->getStatusCode());
        }
        self::assertSame(200, $this->send()->getStatusCode());
        self::assertSame(200, $this->send()->getStatusCode());
        $blocked = $this->send();
        self::assertSame(429, $blocked->getStatusCode());
        self::assertSame('10', $blocked->headers->get('X-RateLimit-Limit'));
        self::assertSame('60', $blocked->headers->get('Retry-After'));
        self::assertSame(10, $this->limiter->attempts($this->key($group)));
        self::assertSame(2, $this->limiter->attempts($this->key($this->middleware('/login')[1])));
        self::assertSame(429, $this->send('/register')->getStatusCode());
        Carbon::setTestNow(Carbon::now()->addMinute());
        self::assertSame(200, $this->send('/register')->getStatusCode());
    }

    public function test_downstream_success_and_errors_are_not_replaced(): void
    {
        foreach ([200, 401, 403, 422, 500] as $status) {
            $response = new Response('unchanged body', $status, ['X-Existing' => 'unchanged']);
            self::assertSame($response, $this->send(response: $response));
            self::assertSame($status, $response->getStatusCode());
            self::assertSame('unchanged body', $response->getContent());
            self::assertSame('unchanged', $response->headers->get('X-Existing'));
        }
        self::assertSame(429, $this->send()->getStatusCode());
    }
}
