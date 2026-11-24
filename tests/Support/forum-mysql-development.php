<?php

use Illuminate\Container\Container;
use Illuminate\Database\Console\Migrations\MigrateCommand;
use Illuminate\Database\DatabaseServiceProvider;
use Illuminate\Database\MigrationServiceProvider;
use Illuminate\Events\EventServiceProvider;
use Illuminate\Filesystem\FilesystemServiceProvider;
use Illuminate\Support\Facades\Facade;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\Support\ForumMysqlTarget;

require dirname(__DIR__, 2).'/vendor/autoload.php';

const FORUM_MIGRATION = '2026_10_24_003711_add_threads_and_likes_to_community_comments';

function gate(bool $condition, string $message): void
{
    ForumMysqlTarget::ensure($condition, $message);
}

function snapshot(PDO $pdo): array
{
    $result = [];
    foreach (['community_posts', 'community_comments', 'users', 'pasars', 'pre_orders', 'post_likes'] as $table) {
        $columns = $pdo->query('SHOW COLUMNS FROM `'.$table.'`')->fetchAll(PDO::FETCH_COLUMN);
        $columns = array_values(array_diff($columns, ['parent_id']));
        $hash = hash_init('sha256');
        $statement = $pdo->query('SELECT `'.implode('`,`', $columns).'` FROM `'.$table.'` ORDER BY id');
        $count = 0;
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            hash_update($hash, json_encode($row, JSON_THROW_ON_ERROR));
            $count++;
        }
        $result[$table] = ['count' => $count, 'hash' => hash_final($hash)];
    }
    $order = $pdo->query('SELECT user_id,pasar_id FROM pre_orders WHERE id=1')->fetch(PDO::FETCH_ASSOC);
    gate((bool) $order, 'Pesanan #1 missing; stop for review');
    $query = $pdo->prepare('SELECT COUNT(*) FROM users WHERE id=?');
    $query->execute([$order['user_id']]);
    gate((int) $query->fetchColumn() === 1, 'Order buyer relation missing');
    $query = $pdo->prepare('SELECT COUNT(*) FROM pasars p JOIN users u ON u.id=p.user_id WHERE p.id=?');
    $query->execute([$order['pasar_id']]);
    gate((int) $query->fetchColumn() === 1, 'Order product/seller relation missing');
    $result['order_1_relations'] = hash('sha256', json_encode($order));

    return $result;
}

function schemaCheck($schema, bool $ran): void
{
    foreach (['users', 'community_posts', 'community_comments'] as $table) {
        $id = collect($schema->getColumns($table))->firstWhere('name', 'id');
        gate($id && $id['type'] === 'bigint unsigned', 'Unexpected prerequisite ID type: '.$table);
    }
    $post = collect($schema->getForeignKeys('community_comments'))->first(fn ($fk) => $fk['columns'] === ['community_post_id']);
    gate($post && $post['foreign_table'] === 'community_posts' && $post['on_delete'] === 'cascade', 'Prerequisite post FK mismatch');
    if (! $ran) {
        gate(! $schema->hasColumn('community_comments', 'parent_id') && ! $schema->hasTable('comment_likes'), 'Partial schema found; no retry');
        gate(! $schema->hasIndex('community_comments', 'comments_post_parent_id_index') && ! $schema->hasIndex('community_comments', 'comments_parent_id_index'), 'Partial indexes found');

        return;
    }
    $parent = collect($schema->getColumns('community_comments'))->firstWhere('name', 'parent_id');
    gate($parent && $parent['type'] === 'bigint unsigned' && $parent['nullable'], 'Parent type/nullability mismatch');
    gate($schema->hasIndex('community_comments', ['community_post_id', 'parent_id', 'id']), 'Root index missing');
    gate($schema->hasIndex('community_comments', ['parent_id', 'id']), 'Reply index missing');
    $parentFk = collect($schema->getForeignKeys('community_comments'))->first(fn ($fk) => $fk['columns'] === ['parent_id']);
    gate($parentFk && $parentFk['foreign_table'] === 'community_comments' && $parentFk['on_delete'] === 'cascade', 'Parent FK mismatch');
    gate($schema->hasIndex('comment_likes', ['community_comment_id', 'user_id'], 'unique'), 'Unique like index missing');
    foreach (['community_comment_id' => 'community_comments', 'user_id' => 'users'] as $column => $table) {
        $fk = collect($schema->getForeignKeys('comment_likes'))->first(fn ($key) => $key['columns'] === [$column]);
        gate($fk && $fk['foreign_table'] === $table && $fk['on_delete'] === 'cascade', 'Like FK mismatch');
    }
}

$phase = 'start';
try {
    gate(! getenv('SAMUDERA_FORUM_MYSQL_TEST'), 'Test opt-in must be absent');
    $mode = $argv[1] ?? '';
    $directory = $argv[2] ?? '';
    gate(in_array($mode, ['inspect', 'backup', 'apply'], true), 'Unsupported mode');
    gate(is_dir($directory) && ! str_starts_with(strtolower(realpath($directory)), strtolower(realpath(dirname(__DIR__, 2)))), 'Receipt directory must exist outside repo');
    $phase = 'target';
    $target = ForumMysqlTarget::inspect();
    echo json_encode($target['summary']).PHP_EOL;
    $pdo = ForumMysqlTarget::connect($target['connection'], 'aquara_proyek2');
    $identity = $pdo->query('SELECT DATABASE() AS db, VERSION() AS version, @@port AS port, @@server_uuid AS uuid')->fetch(PDO::FETCH_ASSOC);
    gate((int) $identity['port'] === (int) $target['connection']['port'], 'Server port mismatch');
    echo 'CONNECTED database='.$identity['db'].' version='.$identity['version'].' port='.$identity['port'].PHP_EOL;
    $app = Container::getInstance();
    $app['config']->set('database.connections', ['mysql' => $target['connection']]);
    $app->instance('env', $app['config']->get('app.env'));
    gate($app->environment() === 'local', 'Unexpected app environment; no force permitted');
    // Only core database/files/events providers: no application/package provider bootstrap.
    $app->register(EventServiceProvider::class);
    $app->register(FilesystemServiceProvider::class);
    $app->register(DatabaseServiceProvider::class);
    $app->register(MigrationServiceProvider::class);
    Facade::setFacadeApplication($app);
    $db = $app['db']->connection('mysql');
    gate($db->selectOne('SELECT DATABASE() AS db')->db === 'aquara_proyek2', 'Laravel target mismatch');
    $schema = $db->getSchemaBuilder();
    gate($schema->hasTable('migrations'), 'Migration repository missing');
    $ran = $db->table('migrations')->where('migration', FORUM_MIGRATION)->exists();
    foreach (['0001_01_01_000000_create_users_table', '2026_02_16_041733_create_community_posts_table', '2026_02_16_041753_create_community_comments_table'] as $prerequisite) {
        gate($db->table('migrations')->where('migration', $prerequisite)->exists(), 'Missing prerequisite: '.$prerequisite);
    }
    schemaCheck($schema, $ran);
    $phase = 'snapshot';
    $before = snapshot($pdo);
    $migrationPath = dirname(__DIR__, 2).'/database/migrations/'.FORUM_MIGRATION.'.php';
    $receipt = ['identity' => $identity, 'snapshot' => $before, 'migration_hash' => hash_file('sha256', $migrationPath), 'ran' => $ran];
    echo 'SCHEMA='.($ran ? 'Ran verified' : 'Pending clean').' COUNTS='.json_encode(array_map(fn ($v) => is_array($v) ? $v['count'] : 'verified', $before)).PHP_EOL;
    $receiptPath = $directory.'/preflight.json';
    if ($mode === 'inspect') {
        gate(! file_exists($receiptPath), 'Receipt exists; do not overwrite');
        file_put_contents($receiptPath, json_encode($receipt, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
        echo "INSPECT_OK; no writes to database.\n";
        exit(0);
    }
    $saved = json_decode(file_get_contents($receiptPath), true, 512, JSON_THROW_ON_ERROR);
    gate($saved === $receipt, 'Database/schema/file changed since preflight; stop');
    $dump = $directory.'/aquara_proyek2-before-forum.sql';
    if ($mode === 'backup') {
        $phase = 'backup';
        gate(! file_exists($dump), 'Backup already exists; preserve it');
        $nonTransactional = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_type='BASE TABLE' AND engine <> 'InnoDB'")->fetchColumn();
        gate((int) $nonTransactional === 0, 'Nontransactional tables require different backup strategy');
        $options = $directory.'/client-temporary.cnf';
        $escape = fn ($v) => '"'.str_replace(['\\', '"', "\n", "\r"], ['\\\\', '\\"', '\\n', '\\r'], (string) $v).'"';
        $config = $target['connection'];
        file_put_contents($options, "[client]\nuser=".$escape($config['username'])."\npassword=".$escape($config['password'])."\nhost=".$escape($config['host'])."\nport=".$config['port']."\nprotocol=tcp\n");
        try {
            $process = proc_open(['C:/laragon/bin/mysql/mysql-8.0.30-winx64/bin/mysqldump.exe', '--defaults-file='.$options,
                '--single-transaction', '--skip-lock-tables', '--routines', '--events', '--triggers', '--hex-blob', '--no-tablespaces', '--set-gtid-purged=OFF', '--result-file='.$dump, 'aquara_proyek2'],
                [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            gate(is_resource($process), 'Cannot start mysqldump');
            fclose($pipes[0]);
            stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $exit = proc_close($process);
            echo 'MYSQLDUMP_EXIT='.$exit.PHP_EOL;
            gate($exit === 0 && trim($stderr) === '', 'Backup failed or has diagnostics; preserve file, stop');
        } finally {
            if (is_file($options)) {
                unlink($options);
            }
        }
        gate(filesize($dump) > 0, 'Empty backup');
        $handle = fopen($dump, 'rb');
        fseek($handle, max(0, filesize($dump) - 512));
        $tail = stream_get_contents($handle);
        fclose($handle);
        gate(str_contains($tail, '-- Dump completed on'), 'Backup completion marker missing');
        gate(snapshot($pdo) === $before, 'Data changed during backup');
        $verification = ['path' => $dump, 'bytes' => filesize($dump), 'sha256' => hash_file('sha256', $dump), 'restore_tested' => false];
        file_put_contents($directory.'/backup-verification.json', json_encode($verification, JSON_PRETTY_PRINT));
        echo json_encode($verification).PHP_EOL;
        exit(0);
    }
    $phase = 'backup_gate';
    $backup = json_decode(file_get_contents($directory.'/backup-verification.json'), true, 512, JSON_THROW_ON_ERROR);
    gate($backup['path'] === $dump && filesize($dump) === $backup['bytes'] && hash_file('sha256', $dump) === $backup['sha256'], 'Backup verification mismatch');
    if (! $ran) {
        $phase = 'apply_single_migration';
        gate(! file_exists($directory.'/apply-attempt.json'), 'An apply was already attempted; no automatic retry');
        file_put_contents($directory.'/apply-attempt.json', json_encode(['started' => date(DATE_ATOM), 'migration' => FORUM_MIGRATION]));
        $command = $app->make(MigrateCommand::class);
        $command->setLaravel($app);
        $input = new ArrayInput(['--database' => 'mysql', '--path' => [$migrationPath], '--realpath' => true]);
        $input->setInteractive(false);
        $output = new BufferedOutput;
        $exit = $command->run($input, $output);
        echo 'MIGRATE_SINGLE_PATH_EXIT='.$exit.PHP_EOL;
        gate($exit === 0, 'Migration command failed; do not retry');
    }
    $phase = 'verify';
    gate($db->table('migrations')->where('migration', FORUM_MIGRATION)->count() === 1, 'Migration not recorded exactly once');
    schemaCheck($schema, true);
    gate(snapshot($pdo) === $before, 'Existing rows/relations changed');
    gate($db->table('community_comments')->whereNotNull('parent_id')->count() === 0, 'Unexpected existing parent');
    gate($db->table('comment_likes')->count() === 0, 'Unexpected likes');
    file_put_contents($directory.'/applied-verification.json', json_encode(['ran' => true, 'schema' => 'verified', 'existing_rows_unchanged' => true, 'order_1_relations' => 'verified']));
    echo "VERIFIED: Ran; schema/indexes/FKs; all snapshot counts and hashes unchanged; Pesanan #1 relations intact.\n";
} catch (Throwable $error) {
    echo 'BLOCKED phase='.$phase.' type='.get_class($error).PHP_EOL;
    if ($error instanceof RuntimeException && ! $error instanceof PDOException) {
        echo $error->getMessage().PHP_EOL;
    }
    exit(1);
}
