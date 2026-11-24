<?php

namespace Tests\Feature;

use App\Models\CommentLike;
use App\Models\CommunityComment;
use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\Support\UsesIsolatedApplication;
use Tests\TestCase;

class ForumCommentsTest extends TestCase
{
    use UsesIsolatedApplication;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertSame('array', config('cache.default'));
        $this->assertSame('array', config('session.driver'));
        foreach ([
            '0001_01_01_000000_create_users_table.php',
            '2026_02_14_201420_create_personal_access_tokens_table.php',
            '2026_03_01_003146_add_profil_fields_to_users_table.php',
            '2026_02_16_041733_create_community_posts_table.php',
            '2026_02_16_041753_create_community_comments_table.php',
            '2026_02_16_064525_create_post_likes_table.php',
            '2026_10_24_003711_add_threads_and_likes_to_community_comments.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
    }

    protected function tearDown(): void
    {
        Http::assertNothingSent();
        Mail::assertNothingSent();
        Notification::assertNothingSent();
        Queue::assertNothingPushed();
        parent::tearDown();
    }

    private function user(): User
    {
        return User::withoutEvents(fn () => User::factory()->create());
    }

    private function forumPost(User $user): CommunityPost
    {
        return CommunityPost::create(['user_id' => $user->id, 'author_name' => $user->name, 'content' => 'Diskusi']);
    }

    private function comment(User $user, CommunityPost $post, ?int $parent = null): CommunityComment
    {
        return CommunityComment::create(['user_id' => $user->id, 'author_name' => $user->name,
            'community_post_id' => $post->id, 'content' => 'Komentar', 'parent_id' => $parent]);
    }

    public function test_guest_and_invalid_token_are_rejected(): void
    {
        foreach ([['GET', '/forum/1/comments'], ['GET', '/forum/comment/1/replies'],
            ['PATCH', '/forum/comment/1'], ['PUT', '/forum/comment/1/like'],
            ['DELETE', '/forum/comment/1/like'], ['POST', '/forum/1/comment'],
            ['DELETE', '/forum/comment/1']] as [$method, $path]) {
            $this->json($method, '/api'.$path)->assertUnauthorized();
        }
        $this->withToken('invalid')->getJson('/api/forum/1/comments')->assertUnauthorized();
    }

    public function test_edit_ownership_validation_and_immutable_identity(): void
    {
        $owner = $this->user();
        $comment = $this->comment($owner, $this->forumPost($owner));
        Sanctum::actingAs($this->user());
        $this->patchJson('/api/forum/comment/'.$comment->id, ['content' => 'Hijack'])->assertForbidden();
        $this->deleteJson('/api/forum/comment/'.$comment->id)->assertForbidden();
        Sanctum::actingAs($owner);
        foreach ([null, '', '   ', [], str_repeat('x', 5001)] as $invalid) {
            $this->patchJson('/api/forum/comment/'.$comment->id, ['content' => $invalid])->assertUnprocessable()->assertJsonValidationErrors('content');
        }
        foreach (['user_id', 'author_name', 'community_post_id', 'parent_id'] as $field) {
            foreach ([999, null] as $value) {
                $this->patchJson('/api/forum/comment/'.$comment->id, ['content' => 'Changed', $field => $value])
                    ->assertUnprocessable()->assertJsonValidationErrors($field);
            }
        }
        $this->assertSame('Komentar', $comment->refresh()->content);
        $this->patchJson('/api/forum/comment/'.$comment->id, ['content' => 'Updated'])
            ->assertOk()->assertJsonPath('data.content', 'Updated')->assertJsonPath('data.likes_count', 0);
        $this->assertSame((string) $owner->id, (string) $comment->refresh()->user_id);
        $this->patchJson('/api/forum/comment/999999', ['content' => 'Updated'])->assertNotFound();
    }

    public function test_likes_are_idempotent_server_counted_and_available_to_self_and_others(): void
    {
        $owner = $this->user();
        $comment = $this->comment($owner, $this->forumPost($owner));
        $path = '/api/forum/comment/'.$comment->id.'/like';
        Sanctum::actingAs($owner);
        for ($i = 0; $i < 3; $i++) {
            $this->putJson($path, ['user_id' => 999, 'likes_count' => 100])->assertOk()
                ->assertJsonPath('data.is_liked_by_me', true)->assertJsonPath('data.likes_count', 1);
        }
        Sanctum::actingAs($this->user());
        $this->putJson($path)->assertOk()->assertJsonPath('data.likes_count', 2);
        for ($i = 0; $i < 3; $i++) {
            $this->deleteJson($path)->assertOk()->assertJsonPath('data.is_liked_by_me', false)->assertJsonPath('data.likes_count', 1);
        }
        Sanctum::actingAs($owner);
        $this->getJson('/api/forum/'.$comment->community_post_id.'/comments')->assertOk()
            ->assertJsonPath('data.data.0.likes_count', 1)->assertJsonPath('data.data.0.is_liked_by_me', true);
        $this->deleteJson($path)->assertOk()->assertJsonPath('data.likes_count', 0);
        $this->assertDatabaseCount('comment_likes', 0);
        $this->putJson('/api/forum/comment/999999/like')->assertNotFound();
        $this->deleteJson('/api/forum/comment/999999/like')->assertNotFound();
    }

    public function test_database_enforces_unique_comment_like(): void
    {
        $user = $this->user();
        $comment = $this->comment($user, $this->forumPost($user));
        $data = ['community_comment_id' => $comment->id, 'user_id' => $user->id];
        CommentLike::create($data);
        try {
            CommentLike::create($data);
            $this->fail('Duplicate like must violate database constraint.');
        } catch (QueryException $exception) {
            $this->assertStringContainsString('UNIQUE constraint failed', $exception->getMessage());
        }
        $this->assertDatabaseCount('comment_likes', 1);
    }

    public function test_real_parent_same_post_and_no_cycles_or_text_inference(): void
    {
        $user = $this->user();
        $post = $this->forumPost($user);
        $root = $this->comment($user, $post);
        $foreign = $this->comment($user, $this->forumPost($user));
        Sanctum::actingAs($user);
        $path = '/api/forum/'.$post->id.'/comment';
        $reply = $this->postJson($path, ['content' => 'Balasan', 'parent_id' => $root->id])->assertOk()
            ->assertJsonPath('data.parent_id', $root->id)->json('data.id');
        foreach ([$foreign->id, 999999, $reply, 'bad'] as $parent) {
            $this->postJson($path, ['content' => 'Invalid', 'parent_id' => $parent])->assertUnprocessable()->assertJsonValidationErrors('parent_id');
        }
        foreach ([$root->id, $reply] as $parent) {
            $this->patchJson('/api/forum/comment/'.$root->id, ['content' => 'Cycle', 'parent_id' => $parent])->assertUnprocessable();
        }
        $this->postJson($path, ['content' => '@someone text only', 'user_id' => 999, 'author_name' => 'Forged'])
            ->assertOk()->assertJsonPath('data.parent_id', null)->assertJsonPath('data.author_name', $user->name);
        $this->assertDatabaseHas('community_comments', ['id' => $reply, 'parent_id' => $root->id, 'community_post_id' => $post->id]);
        $this->assertNull($root->refresh()->parent_id);
        foreach (['', str_repeat('x', 5001), []] as $content) {
            $this->postJson($path, ['content' => $content])->assertUnprocessable();
        }
        $this->postJson('/api/forum/999999/comment', ['content' => 'Missing'])->assertNotFound();
    }

    public function test_pagination_counts_bounded_feed_and_query_count(): void
    {
        $user = $this->user();
        $post = $this->forumPost($user);
        $root = $this->comment($user, $post);
        for ($i = 0; $i < 5; $i++) {
            $this->comment($user, $post);
            $this->comment($user, $post, $root->id);
        }
        Sanctum::actingAs($user);
        $path = '/api/forum/'.$post->id.'/comments';
        $this->getJson($path.'?per_page=2')->assertOk()->assertJsonCount(2, 'data.data')
            ->assertJsonPath('data.total', 6)->assertJsonPath('data.last_page', 3)
            ->assertJsonPath('data.data.0.replies_count', 5)->assertJsonPath('data.data.0.parent_id', null);
        $this->getJson($path.'?per_page=2&page=3')->assertOk()->assertJsonCount(2, 'data.data')->assertJsonPath('data.current_page', 3);
        $this->getJson($path.'?page=99')->assertOk()->assertJsonCount(0, 'data.data');
        $this->getJson('/api/forum/comment/'.$root->id.'/replies?per_page=2&page=2')->assertOk()
            ->assertJsonCount(2, 'data.data')->assertJsonPath('data.total', 5)->assertJsonPath('data.data.0.parent_id', $root->id);
        foreach (['per_page=51', 'per_page=0', 'page=-1', 'page=bad'] as $query) {
            $this->getJson($path.'?'.$query)->assertUnprocessable();
            $this->getJson('/api/forum/comment/'.$root->id.'/replies?'.$query)->assertUnprocessable();
            $this->getJson('/api/forum?'.$query)->assertUnprocessable();
        }
        $this->getJson('/api/forum/999999/comments')->assertNotFound();
        $this->getJson('/api/forum/comment/999999/replies')->assertNotFound();
        $this->getJson('/api/forum')->assertOk()->assertJsonCount(3, 'data.0.comments')
            ->assertJsonPath('data.0.comments_count', 11)->assertJsonPath('data.0.root_comments_count', 6)->assertJsonPath('meta.total', 1);
        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->getJson($path.'?per_page=1')->assertOk();
        $small = count(DB::getQueryLog());
        DB::flushQueryLog();
        $this->getJson($path.'?per_page=50')->assertOk();
        $this->assertSame($small, count(DB::getQueryLog()), 'Query count must not grow per comment.');
        DB::disableQueryLog();
        $otherPost = $this->forumPost($user);
        for ($i = 0; $i < 4; $i++) {
            $this->comment($user, $otherPost);
        }
        $this->getJson('/api/forum')->assertOk()->assertJsonCount(3, 'data.0.comments')->assertJsonCount(3, 'data.1.comments');
        $this->getJson('/api/forum?per_page=1&page=2')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('meta.total', 2);
    }

    public function test_parent_deletion_cascades_replies_and_likes_and_preserves_other_roots(): void
    {
        $owner = $this->user();
        $other = $this->user();
        $post = $this->forumPost($owner);
        $root = $this->comment($owner, $post);
        $reply = $this->comment($other, $post, $root->id);
        $unrelated = $this->comment($other, $post);
        Sanctum::actingAs($other);
        $this->putJson('/api/forum/comment/'.$root->id.'/like')->assertOk();
        $this->putJson('/api/forum/comment/'.$reply->id.'/like')->assertOk();
        $this->deleteJson('/api/forum/comment/'.$root->id)->assertForbidden();
        Sanctum::actingAs($owner);
        $this->deleteJson('/api/forum/comment/'.$root->id)->assertOk()->assertExactJson(['success' => true]);
        $this->assertDatabaseMissing('community_comments', ['id' => $reply->id]);
        $this->assertDatabaseHas('community_comments', ['id' => $unrelated->id]);
        $this->assertDatabaseCount('comment_likes', 0);
        $this->getJson('/api/forum/comment/'.$root->id.'/replies')->assertNotFound();
        $this->deleteJson('/api/forum/comment/'.$root->id)->assertNotFound();
        $this->getJson('/api/forum')->assertOk()->assertJsonPath('data.0.comments_count', 1);
    }

    public function test_existing_add_delete_and_post_toggle_like_remain_functional(): void
    {
        $user = $this->user();
        $post = $this->forumPost($user);
        Sanctum::actingAs($user);
        $id = $this->postJson('/api/forum/'.$post->id.'/comment', ['content' => 'Legacy'])->assertOk()->assertJsonPath('success', true)->json('data.id');
        $this->deleteJson('/api/forum/comment/'.$id)->assertOk();
        $this->assertDatabaseCount('community_comments', 0);
        $this->postJson('/api/forum/'.$post->id.'/toggle-like')->assertOk()->assertJsonPath('status', 'liked');
        $this->getJson('/api/forum?mine=1')->assertOk()->assertJsonPath('data.0.likes_count', 1)->assertJsonPath('data.0.is_liked_by_me', true);
        $this->postJson('/api/forum/'.$post->id.'/toggle-like')->assertOk()->assertJsonPath('status', 'unliked');
        $this->assertDatabaseCount('post_likes', 0);
        $this->assertDatabaseCount('comment_likes', 0);
        Sanctum::actingAs($this->user());
        $this->getJson('/api/forum?mine=1')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_reply_deletion_preserves_parent_and_updates_counts(): void
    {
        $user = $this->user();
        $post = $this->forumPost($user);
        $root = $this->comment($user, $post);
        $reply = $this->comment($user, $post, $root->id);
        Sanctum::actingAs($user);
        $this->putJson('/api/forum/comment/'.$reply->id.'/like')->assertOk();
        $this->deleteJson('/api/forum/comment/'.$reply->id)->assertOk();
        $this->assertDatabaseHas('community_comments', ['id' => $root->id]);
        $this->assertDatabaseCount('comment_likes', 0);
        $this->getJson('/api/forum/'.$post->id.'/comments')->assertOk()->assertJsonPath('data.data.0.replies_count', 0);
    }

    public function test_migration_round_trip_preserves_legacy_comments_without_inferring_mentions(): void
    {
        $migration = require database_path('migrations/2026_10_24_003711_add_threads_and_likes_to_community_comments.php');
        $migration->down();
        $user = $this->user();
        $post = $this->forumPost($user);
        $id = DB::table('community_comments')->insertGetId([
            'community_post_id' => $post->id, 'user_id' => $user->id,
            'author_name' => $user->name, 'content' => '@someone legacy reply',
        ]);
        $migration->up();
        $this->assertDatabaseHas('community_comments', ['id' => $id, 'parent_id' => null, 'content' => '@someone legacy reply']);
        $migration->down();
        $this->assertDatabaseHas('community_comments', ['id' => $id, 'content' => '@someone legacy reply']);
    }
}
