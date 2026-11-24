<?php

namespace Tests\Support;

use Illuminate\Database\ConfigurationUrlParser;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use PDO;
use RuntimeException;

/** Read configuration without registering/booting any application providers. Never log credentials. */
final class ForumMysqlTarget
{
    public static function inspect(): array
    {
        $app = require dirname(__DIR__, 2).'/bootstrap/app.php';
        (new LoadEnvironmentVariables)->bootstrap($app);
        $cached = $app->configurationIsCached();
        (new LoadConfiguration)->bootstrap($app);
        $config = $app['config'];
        $name = $config->get('database.default');
        $raw = $config->get('database.connections.'.$name);
        self::ensure(is_array($raw), 'Default connection missing');
        $connection = (new ConfigurationUrlParser)->parseConfiguration($raw);
        self::ensure($name === 'mysql' && ($connection['driver'] ?? null) === 'mysql', 'Default is not mysql');
        self::ensure(! isset($connection['read']) && ! isset($connection['write']), 'Read/write override requires review');
        self::ensure(in_array($connection['host'] ?? null, ['127.0.0.1', '::1', 'localhost'], true), 'Non-loopback or ambiguous host');
        self::ensure(($connection['database'] ?? null) === 'aquara_proyek2', 'Unexpected database');
        self::ensure(empty($connection['unix_socket']) && empty($connection['options']) && empty($connection['prefix']), 'Socket/options/prefix requires review');
        self::ensure(ctype_digit((string) ($connection['port'] ?? '')) && (int) $connection['port'] > 0 && (int) $connection['port'] <= 65535, 'Invalid port');
        $migration = require dirname(__DIR__, 2).'/database/migrations/2026_10_24_003711_add_threads_and_likes_to_community_comments.php';
        self::ensure($migration->getConnection() === null, 'Migration has custom connection');
        // Pin localhost to numeric loopback; never depend on DNS during connections.
        $connection['host'] = $connection['host'] === 'localhost' ? '127.0.0.1' : $connection['host'];
        unset($connection['url']);

        return ['connection' => $connection, 'summary' => [
            'default' => $name, 'driver' => 'mysql', 'host' => $connection['host'],
            'port' => (int) $connection['port'], 'database' => $connection['database'],
            'config_cached' => $cached, 'url_present' => ! empty($raw['url']),
            'read_write_overrides' => false, 'migration_connection' => 'default',
        ]];
    }

    public static function connect(array $connection, string $database): PDO
    {
        self::ensure(in_array($connection['host'], ['127.0.0.1', '::1'], true), 'Connection host not pinned');
        self::ensure((bool) preg_match('/^[a-zA-Z0-9_]+$/D', $database), 'Invalid database identifier');
        try {
            $pdo = new PDO('mysql:host='.$connection['host'].';port='.$connection['port'].';dbname='.$database.';charset=utf8mb4',
                $connection['username'], $connection['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5]);
        } catch (\PDOException $error) {
            // Authentication errors may contain account identifiers: expose only numeric codes.
            throw new RuntimeException('MySQL connection failed; SQLSTATE '.$error->getCode().'; driver code '.($error->errorInfo[1] ?? 'unknown'));
        }
        self::ensure($pdo->query('SELECT DATABASE()')->fetchColumn() === $database, 'Connected database mismatch');

        return $pdo;
    }

    public static function ensure(bool $condition, string $safeMessage): void
    {
        if (! $condition) {
            throw new RuntimeException($safeMessage);
        }
    }
}
