<?php

namespace Tests\Feature;

use App\Models\CommunityComment;
use App\Models\CommunityPost;
use App\Models\Pasar;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\Support\UsesIsolatedApplication;
use Tests\TestCase;

class OmniSearchTest extends TestCase
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

    private function login(): User
    {
        $user = User::withoutEvents(fn () => User::factory()->create());
        Sanctum::actingAs($user);

        return $user;
    }

    private function seedContent(User $user, string $text): array
    {
        return [
            'pasar' => Pasar::create(['user_id' => $user->id, 'nama_ikan' => $text,
                'deskripsi' => 'Preview pasar', 'harga' => 100, 'nomor_wa' => '0811111111', 'lokasi' => 'Fixture']),
            'forum' => CommunityPost::create(['user_id' => $user->id, 'author_name' => 'Legacy private author', 'content' => $text]),
            'berita' => Post::create(['title' => $text, 'content' => 'Preview berita', 'category' => 'Info', 'author' => 'Private author']),
        ];
    }

    private function search(array $params = [])
    {
        return $this->getJson('/api/search?'.http_build_query($params));
    }

    public function test_guest_and_invalid_token_are_rejected(): void
    {
        $this->search(['q' => 'ikan'])->assertUnauthorized();
        $this->withToken('invalid')->search(['q' => 'ikan'])->assertUnauthorized();
    }

    public function test_groups_have_exact_allowlists_actual_ids_and_existing_read_access(): void
    {
        $user = $this->login();
        $items = $this->seedContent($user, 'Ikan laut');
        $response = $this->search(['q' => '  iKAN  '])->assertOk();
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertSame(['success', 'data'], array_keys($response->json()));
        $this->assertSame(['pasar', 'forum', 'berita'], array_keys($response->json('data')));
        foreach ($items as $type => $item) {
            $response->assertJsonPath("data.$type.data", [[
                'type' => $type, 'id' => $item->id, 'title' => $type === 'forum' ? null : 'Ikan laut',
                'summary' => match ($type) {
                    'pasar' => 'Preview pasar', 'forum' => 'Ikan laut', 'berita' => 'Preview berita'
                },
                'thumbnail' => null,
            ]])->assertJsonPath("data.$type.meta", [
                'current_page' => 1, 'per_page' => 10, 'total' => 1, 'last_page' => 1, 'has_more_pages' => false,
            ]);
        }
        foreach (['/api/pasars', '/api/forum', '/api/posts'] as $path) {
            $this->getJson($path)->assertOk()->assertJsonPath('data.0.id', 1);
        }
        $this->search(['q' => 'absent'])->assertOk()
            ->assertJsonPath('data.pasar.data', [])->assertJsonPath('data.forum.data', [])->assertJsonPath('data.berita.data', []);
    }

    public function test_only_selected_fields_match_not_comments_replies_or_author(): void
    {
        $user = $this->login();
        $items = $this->seedContent($user, 'ordinary');
        $items['pasar']->update(['deskripsi' => 'needle']);
        $items['berita']->update(['content' => 'needle']);
        $root = CommunityComment::create(['community_post_id' => $items['forum']->id, 'user_id' => $user->id,
            'author_name' => 'needle', 'content' => 'needle']);
        CommunityComment::create(['community_post_id' => $items['forum']->id, 'user_id' => $user->id,
            'author_name' => 'needle', 'content' => 'needle', 'parent_id' => $root->id]);
        $this->search(['q' => 'needle'])->assertOk()->assertJsonPath('data.pasar.meta.total', 0)
            ->assertJsonPath('data.forum.meta.total', 0)->assertJsonPath('data.berita.meta.total', 0);
        $items['forum']->update(['content' => 'Posting berisi needle']);
        $this->search(['q' => 'needle'])->assertOk()->assertJsonPath('data.forum.data.0.id', $items['forum']->id);
        $this->search(['q' => 'Private author'])->assertOk()->assertJsonPath('data.forum.meta.total', 0)
            ->assertJsonPath('data.berita.meta.total', 0);
    }

    public function test_invalid_input_is_rejected_and_unicode_length_is_validated(): void
    {
        $this->login();
        foreach ([[], ['q' => ''], ['q' => '   '], ['q' => "\u{00A0}\u{2003}"], ['q' => ['x']],
            ['q' => str_repeat('魚', 101)]] as $params) {
            $this->search($params)->assertUnprocessable()->assertJsonValidationErrors('q');
        }
        foreach (['per_page', 'pasar_page', 'forum_page', 'berita_page'] as $key) {
            foreach ([0, -1, 'abc', '1.5', [], $key === 'per_page' ? 51 : 1001] as $value) {
                // Empty arrays are omitted by http_build_query; send array values explicitly.
                $this->search(['q' => 'ikan', $key => $value === [] ? ['x'] : $value])
                    ->assertUnprocessable()->assertJsonValidationErrors($key);
            }
        }
        $this->search(['q' => str_repeat('魚', 100), 'per_page' => 50])->assertOk();
    }

    public function test_unicode_wildcards_and_sql_payload_are_literal_bound_values(): void
    {
        $user = $this->login();
        $this->seedContent($user, 'unrelated');
        foreach (['魚 laut 🐟', '%', '_', '!', '\\', "' OR 1=1 --"] as $term) {
            $items = $this->seedContent($user, 'prefix '.$term.' suffix');
            DB::enableQueryLog();
            DB::flushQueryLog();
            $response = $this->search(['q' => $term])->assertOk();
            foreach ($items as $type => $item) {
                $response->assertJsonPath("data.$type.meta.total", 1)->assertJsonPath("data.$type.data.0.id", $item->id);
            }
            foreach (DB::getQueryLog() as $query) {
                if (str_contains($query['query'], 'LOWER(')) {
                    $this->assertStringContainsString("LIKE ? ESCAPE '!'", $query['query']);
                    $this->assertCount(1, $query['bindings']);
                }
            }
            DB::disableQueryLog();
        }
    }

    public function test_independent_database_pagination_and_stable_order(): void
    {
        $user = $this->login();
        for ($i = 0; $i < 12; $i++) {
            $this->seedContent($user, 'ikan');
        }
        DB::enableQueryLog();
        DB::flushQueryLog();
        $response = $this->search(['q' => 'ikan', 'per_page' => 2, 'pasar_page' => 2, 'forum_page' => 3])->assertOk();
        $this->assertCount(6, DB::getQueryLog());
        DB::disableQueryLog();
        $response->assertJsonPath('data.pasar.data.0.id', 10)->assertJsonPath('data.pasar.data.1.id', 9)
            ->assertJsonPath('data.forum.data.0.id', 8)->assertJsonPath('data.berita.data.0.id', 12)
            ->assertJsonPath('data.pasar.meta.last_page', 6)->assertJsonPath('data.pasar.meta.has_more_pages', true);
        $this->search(['q' => 'ikan'])->assertOk()->assertJsonCount(10, 'data.pasar.data');
        $this->search(['q' => 'ikan', 'pasar_page' => 1000])->assertOk()->assertJsonPath('data.pasar.data', [])
            ->assertJsonPath('data.pasar.meta.total', 12)->assertJsonPath('data.pasar.meta.current_page', 1000);
    }

    public function test_deleted_content_and_scoped_out_content_never_leak_and_deleted_author_is_safe(): void
    {
        $user = $this->login();
        $items = $this->seedContent($user, 'deleted');
        foreach ($items as $item) {
            $item->delete();
        }
        $response = $this->search(['q' => 'deleted'])->assertOk();
        foreach (array_keys($items) as $type) {
            $response->assertJsonPath("data.$type.meta.total", 0);
        }
        $author = User::withoutEvents(fn () => User::factory()->create());
        $orphan = $this->seedContent($author, 'orphan')['forum'];
        User::withoutEvents(fn () => $author->delete());
        $this->search(['q' => 'orphan'])->assertOk()->assertJsonPath('data.forum.data.0.id', $orphan->id)
            ->assertJsonMissingPath('data.forum.data.0.author_name')->assertJsonMissingPath('data.forum.data.0.user');

        // Existing models have no moderation/status columns. Prove search does not bypass
        // Eloquent visibility scopes if supplied; do not invent a production status rule.
        $scopes = CommunityPost::getAllGlobalScopes();
        try {
            foreach ([Pasar::class, CommunityPost::class, Post::class] as $model) {
                $model::addGlobalScope('test_visibility', fn ($query) => $query->where('id', '<', 0));
            }
            $response = $this->search(['q' => 'orphan'])->assertOk();
            foreach (array_keys($items) as $type) {
                $response->assertJsonPath("data.$type.meta.total", 0);
            }
        } finally {
            CommunityPost::setAllGlobalScopes($scopes);
        }
    }

    public function test_search_has_its_own_throttle_without_changing_login(): void
    {
        $this->login();
        for ($i = 0; $i < 60; $i++) {
            $this->search(['q' => 'ikan'])->assertOk();
        }
        $this->search(['q' => 'ikan'])->assertStatus(429)->assertHeader('Retry-After');
        $this->postJson('/api/login', [])->assertUnprocessable();
    }

    public function test_maximum_page_size_and_preview_use_only_stored_content(): void
    {
        $user = $this->login();
        for ($i = 0; $i < 51; $i++) {
            $this->seedContent($user, 'ikan');
        }
        $post = CommunityPost::findOrFail(51);
        $post->update(['content' => '<p>ikan '.str_repeat('laut ', 100).'</p>', 'image' => 'https://example.test/stored.png']);
        $response = $this->search(['q' => 'ikan', 'per_page' => 50])->assertOk();
        foreach (['pasar', 'forum', 'berita'] as $type) {
            $response->assertJsonCount(50, "data.$type.data")->assertJsonPath("data.$type.meta.total", 51)
                ->assertJsonPath("data.$type.meta.last_page", 2)->assertJsonPath("data.$type.data.0.id", 51);
        }
        $response->assertJsonPath('data.forum.data.0.title', null)
            ->assertJsonPath('data.forum.data.0.thumbnail', 'https://example.test/stored.png');
        $this->assertStringNotContainsString('<p>', $response->json('data.forum.data.0.summary'));
        $this->assertLessThanOrEqual(203, mb_strlen($response->json('data.forum.data.0.summary')));
        $this->search(['q' => 'ikan', 'per_page' => 50, 'forum_page' => 2])->assertOk()
            ->assertJsonCount(1, 'data.forum.data')->assertJsonPath('data.forum.data.0.id', 1);
    }
}
