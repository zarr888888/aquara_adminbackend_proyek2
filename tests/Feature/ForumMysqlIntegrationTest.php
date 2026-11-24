<?php

namespace Tests\Feature;

use App\Models\CommunityComment;
use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use PDO;
use PDOException;
use Tests\Support\ForumMysqlTarget;
use Tests\Support\UsesIsolatedApplication;
use Tests\TestCase;

/** Explicit opt-in only: creates and drops exactly one owned, synthetic MySQL database. */
class ForumMysqlIntegrationTest extends TestCase
{
    use UsesIsolatedApplication {
        createApplication as isolatedApplication;
    }

    private array $target;

    private string $database;

    private string $token;

    private ?PDO $admin = null;

    private bool $created = false;

    public function createApplication()
    {
        if (getenv('SAMUDERA_FORUM_MYSQL_TEST') !== '1') {
            $this->markTestSkipped('Explicit disposable MySQL opt-in required.');
        }
        // Inspect real effective configuration before the isolation helper overrides environment.
        $this->target = ForumMysqlTarget::inspect()['connection'];

        return $this->isolatedApplication();
    }

    public function test_mysql_migration_http_contract_constraints_and_independent_workers(): void
    {
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertSame('array', config('cache.default'));
        $this->assertSame('array', config('session.driver'));
        $this->database = 'samudera_forum_'.bin2hex(random_bytes(12));
        $this->token = bin2hex(random_bytes(32));
        // No development data is read or copied. No IF NOT EXISTS: collision must fail.
        $this->admin = ForumMysqlTarget::connect($this->target, 'mysql');
        $this->admin->exec('CREATE DATABASE `'.$this->database.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->created = true;
        $connection = [...$this->target, 'database' => $this->database];
        config(['database.default' => 'forum_disposable', 'database.connections' => ['forum_disposable' => $connection]]);
        DB::purge();
        $this->assertSame($this->database, DB::selectOne('SELECT DATABASE() AS db')->db);
        Schema::create('task_ownership', function ($table) {
            $table->string('token', 64);
        });
        DB::table('task_ownership')->insert(['token' => $this->token]);
        echo '\nMYSQL_DISPOSABLE='.$this->database.'; VERSION='.DB::selectOne('SELECT VERSION() AS version')->version.PHP_EOL;

        foreach ([
            '0001_01_01_000000_create_users_table.php',
            '2026_02_14_201420_create_personal_access_tokens_table.php',
            '2026_03_01_003146_add_profil_fields_to_users_table.php',
            '2026_02_16_041733_create_community_posts_table.php',
            '2026_02_16_041753_create_community_comments_table.php',
            '2026_02_16_064525_create_post_likes_table.php',
        ] as $migration) {
            $this->assertDisposable();
            (require database_path('migrations/'.$migration))->up();
        }
        $user = User::withoutEvents(fn () => User::factory()->create());
        $other = User::withoutEvents(fn () => User::factory()->create());
        $post = CommunityPost::create(['user_id' => $user->id, 'author_name' => 'Synthetic', 'content' => 'Synthetic post']);
        $legacy = CommunityComment::create(['community_post_id' => $post->id, 'user_id' => $user->id,
            'author_name' => 'Synthetic', 'content' => '@synthetic legacy']);
        $migration = require database_path('migrations/2026_10_24_003711_add_threads_and_likes_to_community_comments.php');
        $this->assertDisposable();
        $migration->up();
        $this->assertNull($legacy->refresh()->parent_id);
        $this->assertTrue(Schema::hasIndex('community_comments', 'comments_post_parent_id_index'));
        $this->assertTrue(Schema::hasIndex('community_comments', 'comments_parent_id_index'));
        $this->assertTrue(Schema::hasIndex('comment_likes', ['community_comment_id', 'user_id'], 'unique'));
        $foreign = Schema::getForeignKeys('community_comments');
        $this->assertTrue(collect($foreign)->contains(fn ($key) => $key['columns'] === ['parent_id'] && $key['on_delete'] === 'cascade'));
        $this->assertCount(2, Schema::getForeignKeys('comment_likes'));
        $this->getJson('/api/forum/'.$post->id.'/comments')->assertUnauthorized();
        Sanctum::actingAs($user);
        $rootPath = '/api/forum/'.$post->id.'/comment';
        $replyId = $this->postJson($rootPath, ['content' => 'Reply', 'parent_id' => $legacy->id])->assertOk()->json('data.id');
        $this->patchJson('/api/forum/comment/'.$legacy->id, ['content' => 'Edited'])->assertOk()->assertJsonPath('data.content', 'Edited');
        $this->patchJson('/api/forum/comment/'.$legacy->id, ['content' => 'Cycle', 'parent_id' => $replyId])->assertUnprocessable();
        $this->patchJson('/api/forum/comment/'.$legacy->id, ['content' => 'Forged', 'user_id' => $other->id])->assertUnprocessable();
        $this->postJson($rootPath, ['content' => 'Invalid', 'parent_id' => $replyId])->assertUnprocessable();
        $this->postJson($rootPath, ['content' => 'Invalid', 'parent_id' => 999999])->assertUnprocessable();
        $otherPost = CommunityPost::create(['user_id' => $other->id, 'author_name' => 'Other', 'content' => 'Other']);
        $this->postJson('/api/forum/'.$otherPost->id.'/comment', ['content' => 'Cross post', 'parent_id' => $legacy->id])->assertUnprocessable();
        $this->getJson('/api/forum/'.$post->id.'/comments?per_page=1')->assertOk()->assertJsonPath('data.total', 1)->assertJsonPath('data.data.0.replies_count', 1);
        $this->getJson('/api/forum/comment/'.$legacy->id.'/replies?per_page=1')->assertOk()->assertJsonPath('data.total', 1)->assertJsonPath('data.data.0.id', $replyId);
        $this->getJson('/api/forum/comment/'.$legacy->id.'/replies?per_page=51')->assertUnprocessable();
        $path = '/api/forum/comment/'.$legacy->id.'/like';
        for ($i = 0; $i < 2; $i++) {
            $this->putJson($path)->assertOk()->assertJsonPath('data.likes_count', 1)->assertJsonPath('data.is_liked_by_me', true);
        }
        $this->rejectSql(fn () => DB::table('comment_likes')->insert(['community_comment_id' => $legacy->id, 'user_id' => $user->id]), 1062);
        $this->rejectSql(fn () => DB::table('comment_likes')->insert(['community_comment_id' => 999999, 'user_id' => $user->id]), 1452);
        $this->rejectSql(fn () => DB::table('comment_likes')->insert(['community_comment_id' => $legacy->id, 'user_id' => 999999]), 1452);
        $this->rejectSql(fn () => DB::table('community_comments')->where('id', $legacy->id)->update(['parent_id' => 999999]), 1452);
        Sanctum::actingAs($other);
        $this->patchJson('/api/forum/comment/'.$legacy->id, ['content' => 'Forbidden'])->assertForbidden();
        $this->putJson($path)->assertOk()->assertJsonPath('data.likes_count', 2);
        $this->deleteJson($path)->assertOk()->assertJsonPath('data.likes_count', 1);
        $this->deleteJson($path)->assertOk()->assertJsonPath('data.likes_count', 1);
        Sanctum::actingAs($user);
        $this->deleteJson($path)->assertOk()->assertJsonPath('data.likes_count', 0);
        $this->workers($legacy->id, $user->id, 'like');
        $this->assertSame(1, DB::table('comment_likes')->count());
        $this->workers($legacy->id, $user->id, 'unlike');
        $this->assertSame(0, DB::table('comment_likes')->count());
        $this->putJson($path)->assertOk();
        $this->putJson('/api/forum/comment/'.$replyId.'/like')->assertOk();
        $this->deleteJson('/api/forum/comment/'.$legacy->id)->assertOk();
        $this->assertDatabaseCount('community_comments', 0);
        $this->assertDatabaseCount('comment_likes', 0);
        $this->getJson('/api/forum/comment/'.$legacy->id.'/replies')->assertNotFound();
        $this->postJson('/api/forum/'.$post->id.'/toggle-like')->assertOk()->assertJsonPath('status', 'liked');
        $this->postJson('/api/forum/'.$post->id.'/toggle-like')->assertOk()->assertJsonPath('status', 'unliked');
        $this->assertDisposable();
        $migration->down();
        $this->assertFalse(Schema::hasColumn('community_comments', 'parent_id'));
        $this->assertFalse(Schema::hasTable('comment_likes'));
        $id = DB::table('community_comments')->insertGetId(['community_post_id' => $post->id, 'user_id' => $user->id, 'author_name' => 'Synthetic', 'content' => 'Preserved']);
        $migration->up();
        $this->assertDatabaseHas('community_comments', ['id' => $id, 'content' => 'Preserved', 'parent_id' => null]);
        $migration->down();
        $this->assertDatabaseHas('community_comments', ['id' => $id, 'content' => 'Preserved']);
        Http::assertNothingSent();
        Mail::assertNothingSent();
        Notification::assertNothingSent();
        Queue::assertNothingPushed();
    }

    private function assertDisposable(): void
    {
        $this->assertTrue($this->created);
        $this->assertSame($this->database, DB::selectOne('SELECT DATABASE() AS db')->db);
        $this->assertSame($this->token, DB::table('task_ownership')->value('token'));
    }

    private function rejectSql(callable $operation, int $code): void
    {
        try {
            $operation();
            $this->fail('Database constraint did not reject invalid write');
        } catch (PDOException $error) {
            $this->assertSame($code, (int) $error->errorInfo[1]);
        }
    }

    private function workers(int $comment, int $user, string $action): void
    {
        $this->assertDisposable();
        $lock = ForumMysqlTarget::connect($this->target, $this->database);
        $lock->beginTransaction();
        $lock->query('SELECT id FROM community_comments WHERE id = '.(int) $comment.' FOR UPDATE');
        $workers = [];
        try {
            for ($i = 0; $i < 2; $i++) {
                $process = proc_open([PHP_BINARY, base_path('tests/Support/forum-mysql-worker.php')], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
                $this->assertIsResource($process);
                fwrite($pipes[0], json_encode(['connection' => $this->target, 'database' => $this->database, 'token' => $this->token,
                    'comment' => $comment, 'user' => $user, 'action' => $action], JSON_THROW_ON_ERROR));
                fclose($pipes[0]);
                stream_set_timeout($pipes[1], 20);
                $workers[] = [$process, $pipes];
                $this->assertSame("READY\n", fgets($pipes[1]));
            }
            // Both independent workers are alive while the parent holds the row lock.
            usleep(300000);
            foreach ($workers as [$process]) {
                $this->assertTrue(proc_get_status($process)['running']);
            }
            $lock->commit();
            foreach ($workers as [$process, $pipes]) {
                $this->assertSame("DONE\n", stream_get_contents($pipes[1]));
                $this->assertSame('', stream_get_contents($pipes[2]));
                fclose($pipes[1]);
                fclose($pipes[2]);
                $this->assertSame(0, proc_close($process));
            }
            $workers = [];
            echo 'CONCURRENCY: two independent '.$action.' workers overlapped behind parent row lock; both completed.'.PHP_EOL;
        } finally {
            if ($lock->inTransaction()) {
                $lock->rollBack();
            }
            foreach ($workers as [$process, $pipes]) {
                if (is_resource($process)) {
                    proc_terminate($process);
                    proc_close($process);
                }
            }
        }
    }

    protected function tearDown(): void
    {
        try {
            if ($this->created) {
                $this->assertDisposable();
                DB::disconnect('forum_disposable');
                // Exact name tracked after successful CREATE plus ownership token, not a prefix sweep.
                $this->admin->exec('DROP DATABASE `'.$this->database.'`');
                echo 'CLEANUP: dropped exact owned disposable database '.$this->database.PHP_EOL;
            }
        } finally {
            $this->admin = null;
            parent::tearDown();
        }
    }
}
