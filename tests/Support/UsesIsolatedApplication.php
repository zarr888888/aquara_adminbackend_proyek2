<?php

namespace Tests\Support;

use App\Support\OtpService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Illuminate\Foundation\Bootstrap\RegisterProviders;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Mockery;

trait UsesIsolatedApplication
{
    public function createApplication()
    {
        $root = sys_get_temp_dir().'/samudera-http-test-'.bin2hex(random_bytes(12));
        foreach (['', '/framework/views', '/framework/sessions', '/framework/cache', '/logs'] as $directory) {
            mkdir($root.$directory, 0700, true);
        }

        // Override cache paths before constructing Laravel; never read cached dev config/routes.
        $environment = [
            'APP_ENV' => 'testing',
            'APP_CONFIG_CACHE' => $root.'/config.php',
            'APP_ROUTES_CACHE' => $root.'/routes.php',
            'APP_SERVICES_CACHE' => $root.'/services.php',
            'APP_PACKAGES_CACHE' => $root.'/packages.php',
            'APP_EVENTS_CACHE' => $root.'/events.php',
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => ':memory:',
            'DB_URL' => '',
            'CACHE_STORE' => 'array',
            'SESSION_DRIVER' => 'array',
            'MAIL_MAILER' => 'array',
            'QUEUE_CONNECTION' => 'sync',
        ];
        $previous = [];
        foreach ($environment as $name => $value) {
            $previous[$name] = [getenv($name), $_ENV[$name] ?? null, $_SERVER[$name] ?? null];
            putenv($name.'='.$value);
            $_ENV[$name] = $_SERVER[$name] = $value;
        }

        $this->beforeApplicationDestroyed(function () use ($root, $previous) {
            foreach ($previous as $name => [$process, $env, $server]) {
                $process === false ? putenv($name) : putenv($name.'='.$process);
                unset($_ENV[$name], $_SERVER[$name]);
                if ($env !== null) {
                    $_ENV[$name] = $env;
                }
                if ($server !== null) {
                    $_SERVER[$name] = $server;
                }
            }
            (new Filesystem)->deleteDirectory($root);
        });

        $app = require dirname(__DIR__, 2).'/bootstrap/app.php';
        $app->addAbsoluteCachePathPrefix($root);
        $app->useEnvironmentPath($root)->loadEnvironmentFrom('.env.testing');
        $app->useStoragePath($root);
        $this->traitsUsedByTest = array_flip(class_uses_recursive(static::class));

        $app->afterBootstrapping(LoadConfiguration::class, function ($app) use ($root) {
            $app['config']->set([
                'app.env' => 'testing',
                'app.debug' => false,
                'app.key' => 'base64:'.base64_encode(str_repeat('t', 32)),
                'app.url' => 'http://localhost',
                'app.maintenance' => ['driver' => 'file'],
                'database.default' => 'sqlite',
                'database.connections' => ['sqlite' => [
                    'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
                    'foreign_key_constraints' => true,
                ]],
                'database.redis' => [],
                'cache.default' => 'array',
                'cache.limiter' => 'array',
                'cache.stores' => ['array' => ['driver' => 'array', 'serialize' => false]],
                'session.driver' => 'array',
                'mail.default' => 'array',
                'mail.mailers' => ['array' => ['transport' => 'array']],
                'queue.default' => 'sync',
                'queue.connections' => ['sync' => ['driver' => 'sync']],
                'broadcasting.default' => 'null',
                'logging.default' => 'null',
                'services' => [],
                'cloudinary.cloud_url' => 'cloudinary://test:test@test',
                'filesystems.default' => 'local',
                'filesystems.disks' => ['local' => ['driver' => 'local', 'root' => $root.'/files']],
                'view.compiled' => $root.'/framework/views',
                'hashing.bcrypt.rounds' => 4,
            ]);
        });
        $app->afterBootstrapping(RegisterProviders::class, function ($app) {
            Http::fake();
            Http::preventStrayRequests();
            Mail::fake();
            Notification::fake();
            Queue::fake();
            $otp = Mockery::mock(OtpService::class);
            $otp->shouldNotReceive('issue');
            $otp->shouldNotReceive('consume');
            $app->instance(OtpService::class, $otp);
        });
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
