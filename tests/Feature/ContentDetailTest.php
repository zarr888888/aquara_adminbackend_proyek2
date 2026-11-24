<?php

namespace Tests\Feature;

use App\Models\CommunityComment;
use App\Models\CommunityPost;
use App\Models\Pasar;
use App\Models\Post;
use App\Models\PostLike;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\Support\UsesIsolatedApplication;
use Tests\TestCase;

class ContentDetailTest extends TestCase
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
            '2026_02_28_074508_create_pasars_table.php',
            '2026_09_27_062343_add_katalog_fields_to_pasars_table.php',
            '2026_02_16_024634_create_posts_table.php',
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

    private function content(User $owner): array
    {
        return [
            'pasars' => Pasar::create(['user_id' => $owner->id, 'nama_ikan' => 'ikan',
                'deskripsi' => str_repeat('Deskripsi lengkap ', 30), 'harga' => 25000,
                'nomor_wa' => '0811111111', 'lokasi' => 'Lokasi jual', 'stok' => 23,
                'kategori' => 'laut', 'tipe_penjualan' => 'grosir']),
            'forum' => CommunityPost::create(['user_id' => $owner->id, 'author_name' => 'Penulis tersimpan',
                'content' => str_repeat('ikan isi lengkap ', 30), 'likes_count' => 999]),
            'posts' => Post::create(['title' => 'ikan', 'content' => str_repeat('Artikel lengkap ', 30),
                'category' => 'Edukasi', 'author' => 'Dinas']),
        ];
    }

    private function safe(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->assertNotContains($key, ['email', 'phone', 'password', 'token', 'tokens', 'otp',
                'otp_expired_at', 'remember_token', 'google_id', 'email_verified_at', 'role', 'roles',
                'permissions', 'admin_id', 'internal_metadata']);
            if (is_array($value)) {
                $this->safe($value);
            }
        }
    }

    public function test_guest_and_invalid_token_cannot_read_any_detail(): void
    {
        foreach (['pasars', 'forum', 'posts'] as $path) {
            $this->getJson("/api/$path/1")->assertUnauthorized();
            $this->withToken('invalid')->getJson("/api/$path/1")->assertUnauthorized();
        }
    }

    public function test_other_users_content_has_complete_existing_presentation_contract(): void
    {
        $owner = $this->user();
        $items = $this->content($owner);
        Sanctum::actingAs($this->user());
        $expected = [
            'pasars' => ['id', 'user_id', 'nama_ikan', 'deskripsi', 'harga', 'foto', 'nomor_wa', 'lokasi',
                'stok', 'kategori', 'tipe_penjualan', 'created_at', 'updated_at', 'user'],
            'forum' => ['id', 'user_id', 'author_name', 'content', 'image', 'created_at', 'updated_at',
                'likes_count', 'comments_count', 'root_comments_count', 'is_liked_by_me', 'user', 'comments'],
            'posts' => ['id', 'title', 'content', 'image', 'category', 'author', 'created_at', 'updated_at'],
        ];
        foreach ($items as $path => $item) {
            $response = $this->getJson("/api/$path/{$item->id}?mine=1&user_id=999")->assertOk()
                ->assertJsonPath('data.id', $item->id);
            $this->assertSame(['success', 'data'], array_keys($response->json()));
            $this->assertSame($expected[$path], array_keys($response->json('data')));
            $this->safe($response->json());
            $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
            $this->assertSame($item->created_at->toISOString(), $response->json('data.created_at'));
            if ($path !== 'posts') {
                $response->assertJsonPath('data.user', ['id' => $owner->id, 'name' => $owner->name, 'foto_profil' => $owner->foto_profil]);
            }
            $fullField = $path === 'pasars' ? 'deskripsi' : 'content';
            $response->assertJsonPath("data.$fullField", $item->{$fullField});
            $this->assertGreaterThan(200, mb_strlen($response->json("data.$fullField")));
            $listing = $this->getJson("/api/$path")->assertOk()->json('data.0');
            $this->assertEquals($listing, $response->json('data'));
        }
        $this->getJson('/api/pasars/1')->assertJsonPath('data.harga', 25000)->assertJsonPath('data.stok', 23)
            ->assertJsonPath('data.nomor_wa', '0811111111');
        $this->getJson('/api/forum/1')->assertJsonPath('data.comments', [])->assertJsonPath('data.likes_count', 0)
            ->assertJsonPath('data.is_liked_by_me', false);
    }

    public function test_search_ids_open_directly_without_loading_feed_pages(): void
    {
        $owner = $this->user();
        $items = $this->content($owner);
        foreach (['pasars' => 71, 'forum' => 82, 'posts' => 93] as $path => $id) {
            $items[$path]->id = $id;
            $items[$path]->save();
        }
        Sanctum::actingAs($this->user());
        $search = $this->getJson('/api/search?q=ikan')->assertOk();
        foreach (['pasar' => 'pasars', 'forum' => 'forum', 'berita' => 'posts'] as $type => $path) {
            $id = $search->json("data.$type.data.0.id");
            $this->assertSame($items[$path]->id, $id);
            $this->getJson("/api/$path/$id")->assertOk()->assertJsonPath('data.id', $id);
            $this->getJson("/api/$path/1")->assertNotFound()->assertExactJson(['message' => 'Not Found']);
        }
        $this->getJson('/api/pasars/82')->assertNotFound();
        $this->getJson('/api/posts/71')->assertNotFound();
    }

    public function test_invalid_missing_deleted_and_scoped_out_ids_have_generic_404(): void
    {
        $owner = $this->user();
        $items = $this->content($owner);
        Sanctum::actingAs($owner);
        foreach (array_keys($items) as $path) {
            foreach (['0', '-1', 'abc', '1.0', '1e0', '01', '999', str_repeat('9', 40), '1%20OR%201=1'] as $id) {
                $response = $this->getJson("/api/$path/$id")->assertNotFound()->assertExactJson(['message' => 'Not Found']);
                $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
            }
        }
        $scopes = Post::getAllGlobalScopes();
        try {
            foreach ($items as $path => $item) {
                $item::addGlobalScope('detail_visibility', fn ($query) => $query->where('id', '<', 0));
                $this->getJson("/api/$path/{$item->id}")->assertNotFound()->assertExactJson(['message' => 'Not Found']);
            }
        } finally {
            Post::setAllGlobalScopes($scopes);
        }
        foreach ($items as $path => $item) {
            $item->delete();
            $this->getJson("/api/$path/{$item->id}")->assertNotFound()->assertExactJson(['message' => 'Not Found']);
        }
    }

    public function test_deleted_or_unavailable_user_relation_is_safe(): void
    {
        $owner = $this->user();
        $items = $this->content($owner);
        Sanctum::actingAs($this->user());
        $scopes = User::getAllGlobalScopes();
        try {
            User::addGlobalScope('unavailable_identity', fn ($query) => $query->where('id', '<', 0));
            foreach (['pasars', 'forum'] as $path) {
                $response = $this->getJson("/api/$path/{$items[$path]->id}")->assertOk()->assertJsonPath('data.user', null);
                $this->safe($response->json());
            }
        } finally {
            User::setAllGlobalScopes($scopes);
        }
        User::withoutEvents(fn () => $owner->delete());
        $this->getJson('/api/forum/'.$items['forum']->id)->assertOk()->assertJsonPath('data.user', null);
        // The actual Pasar FK cascades on owner deletion, unlike legacy Forum user_id.
        $this->getJson('/api/pasars/'.$items['pasars']->id)->assertNotFound()->assertExactJson(['message' => 'Not Found']);
        $items['forum']->update(['user_id' => 'legacy-unknown']);
        $this->getJson('/api/forum/'.$items['forum']->id)->assertOk()->assertJsonPath('data.user', null);
    }

    public function test_forum_counts_liked_state_and_paginated_threads_remain_separate(): void
    {
        $owner = $this->user();
        $post = $this->content($owner)['forum'];
        $viewer = $this->user();
        Sanctum::actingAs($viewer);
        $root = null;
        for ($i = 0; $i < 30; $i++) {
            $root = CommunityComment::create(['community_post_id' => $post->id, 'user_id' => $owner->id,
                'author_name' => 'Author', 'content' => 'Comment']);
        }
        CommunityComment::create(['community_post_id' => $post->id, 'user_id' => $owner->id,
            'author_name' => 'Author', 'content' => 'Reply', 'parent_id' => $root->id]);
        PostLike::create(['community_post_id' => $post->id, 'user_id' => $viewer->id]);
        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->getJson("/api/forum/{$post->id}")->assertOk()->assertJsonPath('data.comments', [])
            ->assertJsonPath('data.comments_count', 31)->assertJsonPath('data.root_comments_count', 30)
            ->assertJsonPath('data.likes_count', 1)->assertJsonPath('data.is_liked_by_me', true);
        $this->assertCount(2, DB::getQueryLog());
        DB::disableQueryLog();
        $this->getJson("/api/forum/{$post->id}/comments?per_page=2")->assertOk()->assertJsonCount(2, 'data.data')
            ->assertJsonPath('data.total', 30);
        $this->getJson("/api/forum/comment/{$root->id}/replies")->assertOk()->assertJsonCount(1, 'data.data');
        Sanctum::actingAs($owner);
        $this->getJson("/api/forum/{$post->id}")->assertOk()->assertJsonPath('data.is_liked_by_me', false);
    }

    public function test_detail_quota_is_shared_but_search_and_login_are_independent(): void
    {
        Sanctum::actingAs($this->user());
        for ($i = 0; $i < 60; $i++) {
            $path = ['pasars', 'forum', 'posts'][$i % 3];
            $this->getJson("/api/$path/999")->assertNotFound();
        }
        foreach (['pasars', 'forum', 'posts'] as $path) {
            $this->getJson("/api/$path/999")->assertStatus(429)->assertHeader('Retry-After');
        }
        $this->getJson('/api/search?q=ikan')->assertOk();
        $this->postJson('/api/login', [])->assertUnprocessable();
    }
}
