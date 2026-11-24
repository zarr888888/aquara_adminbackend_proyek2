<?php

/*
 * Consumes the single practice register-OTP created by rc-external-verify.php.
 * Run this YOURSELF in your own terminal: the 6-digit code goes from your
 * keyboard straight into this process and is never printed, logged, or echoed.
 *
 *   $env:SAMUDERA_RC_ENV="...\Release-Candidate\.env.candidate"
 *   $env:SAMUDERA_RC_DB="...\Release-Candidate\rc-otp.sqlite"
 *   (Read-Host 'Masukkan 6 digit OTP dari email') | php tests/Support/rc-otp-consume.php
 *
 * Prints only OTP_CONSUME=OK / OTP_CONSUME=FAIL. Never prints the code,
 * the recipient address, or any credential.
 */

declare(strict_types=1);
set_time_limit(60);

$envPath = getenv('SAMUDERA_RC_ENV');
$dbPath = getenv('SAMUDERA_RC_DB');
if (! is_string($envPath) || $envPath === '' || ! is_file($envPath)) {
    fwrite(STDERR, "OTP_CONSUME=FAIL env_missing\n");
    exit(2);
}
if (! is_string($dbPath) || $dbPath === '' || ! is_file($dbPath)) {
    fwrite(STDERR, "OTP_CONSUME=FAIL candidate_db_missing\n");
    exit(2);
}

$code = trim((string) fgets(STDIN));
if (! preg_match('/^[0-9]{6}$/D', $code)) {
    fwrite(STDERR, "OTP_CONSUME=FAIL format\n");
    exit(2);
}

$target = '';
foreach (file($envPath, FILE_IGNORE_NEW_LINES) as $line) {
    if (preg_match('/^\s*RC_OTP_TEST_EMAIL\s*=\s*(.*?)\s*$/', $line, $m)) {
        $target = trim($m[1], "\"'");
    }
}
if (! filter_var($target, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "OTP_CONSUME=FAIL no_recipient\n");
    exit(2);
}

foreach (['APP_ENV=testing', 'APP_DEBUG=false', 'DB_CONNECTION=sqlite', 'DB_DATABASE='.$dbPath,
    'SESSION_DRIVER=array', 'QUEUE_CONNECTION=sync', 'CACHE_STORE=array', 'LOG_CHANNEL=null'] as $pair) {
    putenv($pair);
    [$k, $v] = explode('=', $pair, 2);
    $_ENV[$k] = $_SERVER[$k] = $v;
}

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

config(['database.default' => 'sqlite', 'database.connections.sqlite' => [
    'driver' => 'sqlite', 'database' => $dbPath, 'prefix' => '', 'foreign_key_constraints' => true,
]]);

use App\Models\User;
use App\Support\OtpService;

$user = User::where('email', $target)->first();
if ($user === null) {
    echo "OTP_CONSUME=FAIL no_user\n";
    exit(1);
}

try {
    // Mirrors POST /api/register/verify-otp. saveQuietly only skips model
    // events (activity log table is not part of the candidate database);
    // the OTP challenge logic itself is the production service.
    (new OtpService())->consume($user, 'register', $code, function (User $locked): void {
        $locked->forceFill(['email_verified_at' => now()])->saveQuietly();
    });
    $verified = User::where('email', $target)->value('email_verified_at') !== null ? 'yes' : 'no';
    echo 'OTP_CONSUME=OK verified='.$verified."\n";
} catch (Throwable $e) {
    echo 'OTP_CONSUME=FAIL class='.$e::class."\n";
    exit(1);
}
