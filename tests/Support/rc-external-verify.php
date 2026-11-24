<?php

/*
 * Release-candidate external verification (24 November 2026).
 *
 * Reads a private candidate env file (path in SAMUDERA_RC_ENV) and exercises the
 * exact production code paths for Cloudinary upload, BMKG forecast, Groq
 * Asisten, and one real OTP email to an authorized test recipient.
 *
 * Never prints credential values, OTP codes, or the recipient address.
 * Usage: SAMUDERA_RC_ENV=<path> php tests/Support/rc-external-verify.php
 *        SAMUDERA_RC_ONLY=otp limits the run to a single section.
 *
 * Exit codes: 0 = every requested section reached its expected success output;
 * 1 = a requested section failed or was skipped, so the gate is not met;
 * 2 = precondition failure (candidate env file missing or unusable).
 */

declare(strict_types=1);
set_time_limit(180);

$envPath = getenv('SAMUDERA_RC_ENV');
if (! is_string($envPath) || $envPath === '' || ! is_file($envPath)) {
    fwrite(STDERR, "ENV_MISSING\n");
    exit(2);
}

$map = [];
foreach (file($envPath, FILE_IGNORE_NEW_LINES) as $line) {
    if (preg_match('/^\s*([A-Z0-9_]+)\s*=\s*(.*?)\s*$/', $line, $m) && $m[2] !== '') {
        $map[$m[1]] = trim($m[2], "\"'");
    }
}

$allowed = [
    'APP_KEY', 'GOMBYANG_PROVIDER', 'GROQ_API_KEY',
    'CLOUDINARY_URL', 'CLOUDINARY_KEY', 'CLOUDINARY_SECRET', 'CLOUDINARY_CLOUD_NAME', 'CLOUDINARY_SECURE',
    'MAIL_MAILER', 'MAIL_HOST', 'MAIL_PORT', 'MAIL_SCHEME', 'MAIL_USERNAME', 'MAIL_PASSWORD',
    'MAIL_FROM_ADDRESS', 'MAIL_FROM_NAME', 'FONNTE_TOKEN', 'RC_OTP_TEST_EMAIL',
];
foreach ($allowed as $key) {
    if (isset($map[$key])) {
        putenv($key.'='.$map[$key]);
        $_ENV[$key] = $map[$key];
        $_SERVER[$key] = $map[$key];
    }
}
// Candidate isolation: in-memory SQLite, no persistent logs, no main database.
foreach (['APP_ENV=testing', 'APP_DEBUG=false', 'DB_CONNECTION=sqlite', 'DB_DATABASE=:memory:',
    'SESSION_DRIVER=array', 'QUEUE_CONNECTION=sync', 'CACHE_STORE=array', 'LOG_CHANNEL=null'] as $pair) {
    putenv($pair);
    [$k, $v] = explode('=', $pair, 2);
    $_ENV[$k] = $_SERVER[$k] = $v;
}

// Gate bookkeeping: any requested section that is skipped, fails, or throws
// makes the whole run non-zero, so a FAIL can never be mistaken for a pass.
$failures = [];
$fail = static function (string $code) use (&$failures): void {
    if (! in_array($code, $failures, true)) {
        $failures[] = $code;
    }
};

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Support\BmkgForecast;
use App\Support\GombyangResponses;
use App\Support\OtpService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

$only = strtolower((string) (getenv('SAMUDERA_RC_ONLY') ?: ''));
$want = static fn (string $section): bool => $only === '' || $only === $section;
$candidateDb = getenv('SAMUDERA_RC_DB');
if (is_string($candidateDb) && $candidateDb !== '' && ! is_file($candidateDb)) {
    touch($candidateDb); // fresh candidate DB file for this drill only
}
config(['database.default' => 'sqlite', 'database.connections.sqlite' => [
    'driver' => 'sqlite',
    'database' => is_string($candidateDb) && $candidateDb !== '' ? $candidateDb : ':memory:',
    'prefix' => '', 'foreign_key_constraints' => true,
]]);

// 1) Cloudinary through the production trait path, dedicated verification folder.
$uploader = new class { use App\Traits\UploadsToCloudinary; };
$upload = Closure::bind(function (UploadedFile $file, string $dir): ?string {
    return $this->uploadFotoToCloudinary($file, $dir);
}, $uploader, $uploader);
$remove = Closure::bind(function (?string $url): void {
    $this->deleteFotoFromCloudinary($url);
}, $uploader, $uploader);

if ($want('cloudinary')) {
$source = __DIR__.'/../../public/images/SAMUDERA_FIX_LOGO.png';
if (! is_file($source)) {
    $fail('cloudinary_source_missing');
    echo "CLOUDINARY_SOURCE_MISSING\n";
} else {
    $url = null;
    for ($attempt = 0; $attempt < 2 && $url === null; $attempt++) {
        $file = new UploadedFile($source, 'rc-verify.png', 'image/png', null, true);
        $url = $upload($file, 'samudera/rc-verification');
        if ($url === null && $attempt === 0) {
            sleep(2); // one retry for Admin API propagation only
        }
    }
    if ($url === null) {
        $fail('cloudinary_upload');
        echo "CLOUDINARY_UPLOAD=FAIL\n";
    } else {
        $host = parse_url($url, PHP_URL_HOST);
        echo 'CLOUDINARY_UPLOAD=OK host='.(is_string($host) ? $host : 'unknown')."\n";
        try {
            $response = Http::timeout(10)->get($url);
            if (! $response->successful()) {
                $fail('cloudinary_fetch');
            }
            echo 'CLOUDINARY_FETCH='.$response->status().' type='.substr((string) $response->header('Content-Type'), 0, 24)."\n";
        } catch (Throwable) {
            $fail('cloudinary_fetch');
            echo "CLOUDINARY_FETCH=NETWORK_FAIL\n";
        }
        try {
            $remove($url);
            echo "CLOUDINARY_DELETE=OK\n";
        } catch (Throwable) {
            $fail('cloudinary_delete');
            echo "CLOUDINARY_DELETE=FAIL\n";
        }
    }
}
}

// 2) Real BMKG marine forecast for both supported ports.
if ($want('bmkg')) {
foreach (['XJ022', 'XJ021'] as $code) {
    $result = (new BmkgForecast())->get($code);
    $data = $result['data'] ?? [];
    $items = count($data['items'] ?? []);
    printf(
        "BMKG %s status=%s items=%d valid=%s..%s\n",
        $code,
        $data['status'] ?? '?',
        $items,
        $data['valid_from'] ?? '-',
        $data['valid_to'] ?? '-'
    );
    if (($data['status'] ?? null) !== 'available' || $items === 0) {
        $fail('bmkg_'.$code);
    }
}
}

// 3) Groq Asisten via the production provider path, single request.
if ($want('groq')) {
config(['services.gombyang_provider' => 'groq', 'services.groq.key' => $map['GROQ_API_KEY'] ?? '']);
if (($map['GROQ_API_KEY'] ?? '') === '') {
    $fail('groq_no_key');
    echo "GROQ=NO_KEY\n";
} else {
    $answer = (new GombyangResponses())->answer(
        'LATIHAN verifikasi kandidat. Balas hanya satu kata: SIAP',
        (string) ($map['GROQ_API_KEY'] ?? '')
    );

    if ($answer === null) {
        $fail('groq');
    }
    echo $answer === null ? "GROQ=FAIL\n" : 'GROQ=OK len='.strlen($answer)."\n";
}
}

// 4) One real OTP email to the authorized test recipient, if provided.
if ($want('otp')) {
    $mailer = (string) config('mail.default');
echo 'MAIL_MAILER='.$mailer.' host='.(config('mail.mailers.smtp.host') ? 'set' : 'unset')."\n";
$target = (string) ($map['RC_OTP_TEST_EMAIL'] ?? '');
if ($target === '') {
    $fail('otp_email_no_recipient');
    echo "OTP_EMAIL=SKIPPED no RC_OTP_TEST_EMAIL\n";
} elseif (! filter_var($target, FILTER_VALIDATE_EMAIL)) {
    $fail('otp_email_invalid_recipient');
    echo "OTP_EMAIL=SKIPPED invalid_format\n";
} elseif (! in_array($mailer, ['smtp', 'ses', 'sendmail', 'mailgun', 'postmark'], true)) {
    $fail('otp_email_mailer_not_real');
    echo "OTP_EMAIL=SKIPPED mailer_not_real\n";
} else {
    // Candidate OTP schema: exact production migration names for the users table
    // plus the dedicated otp_challenges table. OtpService::issue also clears
    // users.otp / users.otp_expired_at and the drill inserts users.phone, so the
    // base users migration alone cannot deliver an OTP.
    $otpSchema = [
        'users' => [
            '0001_01_01_000000_create_users_table.php',
            '2026_03_01_003146_add_profil_fields_to_users_table.php',
            '2026_04_04_103521_add_phone_to_users_table.php',
            '2026_04_05_031201_add_google_id_to_users_table.php',
            '2026_04_05_071002_add_otp_to_users_table.php',
        ],
        'otp_challenges' => ['2026_09_27_134324_create_otp_challenges_table.php'],
    ];
    $required = [
        'users' => ['id', 'name', 'email', 'password', 'phone', 'otp', 'otp_expired_at'],
        'otp_challenges' => ['id', 'user_id', 'purpose', 'target', 'channel', 'code_hash', 'expires_at',
            'consumed_at', 'attempts', 'send_count', 'window_started_at', 'sent_at'],
    ];
    $schemaError = null;
    foreach ($otpSchema as $migrations) {
        foreach ($migrations as $migration) {
            if (! is_file(database_path('migrations/'.$migration))) {
                $schemaError = 'migration_missing';
                break 2;
            }
        }
    }
    // A candidate DB left behind by an earlier partial run (users without the
    // OTP columns) is rebuilt only while it is still empty; populated drills are
    // never dropped.
    if ($schemaError === null && Illuminate\Support\Facades\Schema::hasTable('users')) {
        $incomplete = array_diff($required['users'], Illuminate\Support\Facades\Schema::getColumnListing('users'));
        if ($incomplete !== [] && ! Illuminate\Support\Facades\DB::table('users')->exists()) {
            Illuminate\Support\Facades\Schema::dropIfExists('otp_challenges');
            Illuminate\Support\Facades\Schema::dropIfExists('users');
            Illuminate\Support\Facades\Schema::dropIfExists('sessions');
            Illuminate\Support\Facades\Schema::dropIfExists('password_reset_tokens');
        }
    }
    if ($schemaError === null) {
        foreach ($otpSchema as $table => $migrations) {
            if (Illuminate\Support\Facades\Schema::hasTable($table)) {
                continue;
            }
            foreach ($migrations as $migration) {
                (require database_path('migrations/'.$migration))->up();
            }
        }
        foreach ($required as $table => $columns) {
            if (! Illuminate\Support\Facades\Schema::hasTable($table)) {
                $schemaError = 'table_missing';
            } elseif (array_diff($columns, Illuminate\Support\Facades\Schema::getColumnListing($table)) !== []) {
                $schemaError = 'column_missing';
            }
            if ($schemaError !== null) {
                break;
            }
        }
    }
    if ($schemaError !== null) {
        $fail('otp_schema_'.$schemaError);
        echo 'OTP_EMAIL=FAIL schema_'.$schemaError."\n";
    } else {
        try {
            $user = User::withoutEvents(fn () => User::firstOrCreate(
                ['email' => $target],
                ['name' => 'LATIHAN RC', 'phone' => '000000000000', 'password' => Hash::make(bin2hex(random_bytes(16)))]
            ));
            (new OtpService())->issue($user, 'register', 'email', $target);
            echo "OTP_EMAIL=SENT (one practice code; confirm receipt privately, do not share the code)\n";
        } catch (Throwable $e) {
            $fail('otp_email');
            echo 'OTP_EMAIL=FAIL class='.$e::class."\n";
        }
    }
}
}

// Gate result: a non-zero exit code is mandatory on any failed or skipped
// section so an operator or CI job cannot read a FAIL as a pass.
if ($failures !== []) {
    fwrite(STDERR, 'RC_EXTERNAL_VERIFY=FAIL '.implode(',', $failures)."\n");
    exit(1);
}

echo "RC_EXTERNAL_VERIFY=OK\n";
exit(0);
