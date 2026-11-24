<?php

namespace Tests\Feature;

use App\Http\Resources\ProfileProductResource;
use App\Http\Resources\ProfileResource;
use App\Models\CommunityComment;
use App\Models\CommunityPost;
use App\Models\Pasar;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\Support\UsesIsolatedApplication;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use UsesIsolatedApplication;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        foreach ([
            '0001_01_01_000000_create_users_table.php',
            '2026_02_14_201420_create_personal_access_tokens_table.php',
            '2026_03_01_003146_add_profil_fields_to_users_table.php',
            '2026_04_04_103521_add_phone_to_users_table.php',
            '2026_04_05_031201_add_google_id_to_users_table.php',
            '2026_04_05_071002_add_otp_to_users_table.php',
            '2026_02_28_074508_create_pasars_table.php',
            '2026_09_27_062343_add_katalog_fields_to_pasars_table.php',
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

    private function user(array $attributes = []): User
    {
        $sequence = User::max('id') + 1;

        return User::withoutEvents(fn () => User::factory()->create([
            'phone' => '08111111'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT), 'google_id' => 'private-google-'.$sequence,
            'foto_profil' => 'https://example.test/avatar.png', ...$attributes,
        ]));
    }

    private function product(User $owner): Pasar
    {
        return Pasar::create([
            'user_id' => $owner->id, 'nama_ikan' => 'Lele sintetis', 'deskripsi' => 'Fixture',
            'harga' => 25000, 'nomor_wa' => '082222222222', 'lokasi' => 'Lokasi jual',
            'stok' => 10, 'kategori' => 'tawar', 'tipe_penjualan' => 'eceran',
        ]);
    }

    private function assertSafe(array $json): void
    {
        $forbidden = ['email', 'phone', 'password', 'remember_token', 'otp', 'otp_expired_at',
            'google_id', 'email_verified_at', 'token', 'tokens', 'role', 'roles',
            'permissions', 'notifications', 'pre_orders', 'admin_id', 'latitude', 'longitude',
            'alamat', 'address', 'internal_metadata'];
        foreach ($json as $key => $value) {
            $this->assertNotContains($key, $forbidden);
            if (is_array($value)) {
                $this->assertSafe($value);
            }
        }
    }

    public function test_guest_and_invalid_token_cannot_read_profiles_or_products(): void
    {
        foreach (['/api/profiles/1', '/api/profiles/1/pasars'] as $path) {
            $this->getJson($path)->assertUnauthorized();
            $this->withToken('invalid')->getJson($path)->assertUnauthorized();
        }
    }

    public function test_self_and_other_have_exact_same_minimal_profile_contract(): void
    {
        $viewer = $this->user();
        $other = $this->user(['foto_profil' => null, 'name' => '']);
        Sanctum::actingAs($viewer);
        foreach ([$viewer, $other] as $target) {
            $response = $this->getJson('/api/profiles/'.$target->id)->assertOk()
                ->assertExactJson(['success' => true, 'data' => [
                    'id' => $target->id, 'name' => $target->name, 'foto_profil' => $target->foto_profil,
                    'fokus_budidaya' => $target->fokus_budidaya,
                ]]);
            $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
            $this->assertSafe($response->json());
        }
        $this->getJson('/api/user/'.$other->id)->assertNotFound();
        $this->getJson('/api/profiles')->assertNotFound();
    }

    public function test_missing_deleted_and_empty_are_distinct(): void
    {
        $viewer = $this->user();
        $deleted = $this->user();
        User::withoutEvents(fn () => $deleted->delete());
        Sanctum::actingAs($viewer);
        foreach ([$deleted->id, 99999, 'invalid'] as $id) {
            $this->getJson('/api/profiles/'.$id)->assertNotFound();
            $this->getJson('/api/profiles/'.$id.'/pasars')->assertNotFound();
        }
        $this->getJson('/api/profiles/'.$viewer->id.'/pasars')->assertOk()
            ->assertExactJson(['success' => true, 'data' => [], 'meta' => [
                'current_page' => 1, 'per_page' => 20, 'total' => 0, 'last_page' => 1,
            ]]);
    }

    public function test_focus_is_nullable_free_text_and_preserves_legacy_values(): void
    {
        $viewer = $this->user();
        Sanctum::actingAs($viewer);
        foreach ([null, '', 'Lele', 'Budidaya air tawar', 'Tambak/laut', 'Nelayan',
            'Pengolahan hasil perikanan', 'Lele, udang dan pengolahan', str_repeat('a', 255)] as $focus) {
            $target = $this->user(['fokus_budidaya' => $focus]);
            $response = $this->getJson('/api/profiles/'.$target->id)->assertOk()
                ->assertExactJson(['success' => true, 'data' => [
                    'id' => $target->id, 'name' => $target->name,
                    'foto_profil' => $target->foto_profil, 'fokus_budidaya' => $focus,
                ]]);
            $this->assertSafe($response->json());
        }
    }

    public function test_existing_profile_update_validates_focus_and_only_changes_authenticated_owner(): void
    {
        $owner = $this->user(['fokus_budidaya' => 'Lele']);
        $other = $this->user(['fokus_budidaya' => 'Udang']);
        $path = '/api/profil/update';
        $this->postJson($path, ['name' => 'Guest', 'fokus_budidaya' => 'Nelayan'])->assertUnauthorized();
        $this->withToken('invalid')->postJson($path, ['name' => 'Guest'])->assertUnauthorized();
        Sanctum::actingAs($owner);

        // Suppress activity-log model events only; requests still use the real route/middleware/controller.
        User::withoutEvents(function () use ($owner, $other, $path) {
            foreach (['Lele', 'Budidaya air tawar', 'Tambak/laut', 'Nelayan',
                'Pengolahan hasil perikanan', str_repeat('a', 255)] as $focus) {
                $this->postJson($path, ['name' => $owner->name, 'fokus_budidaya' => $focus,
                    'id' => $other->id, 'user_id' => $other->id])->assertOk()
                    ->assertJsonPath('data.id', $owner->id)->assertJsonPath('data.fokus_budidaya', $focus);
                $this->assertSame($focus, $owner->fresh()->fokus_budidaya);
                $this->getJson('/api/profiles/'.$owner->id)->assertOk()->assertJsonPath('data.fokus_budidaya', $focus);
                $this->getJson('/api/user')->assertOk()->assertJsonPath('fokus_budidaya', $focus);
                $this->assertSame('Udang', $other->fresh()->fokus_budidaya);
            }
            $saved = $owner->fresh()->fokus_budidaya;
            foreach ([[], ['fokus_budidaya' => null], ['fokus_budidaya' => ''], ['fokus_budidaya' => '   ']] as $input) {
                $this->postJson($path, ['name' => $owner->name, ...$input])->assertOk();
                $this->assertSame($saved, $owner->fresh()->fokus_budidaya);
            }
            foreach ([[], ['Nelayan'], 42, true, str_repeat('a', 256)] as $invalid) {
                $this->postJson($path, ['name' => $owner->name, 'fokus_budidaya' => $invalid])
                    ->assertUnprocessable()->assertJsonValidationErrors('fokus_budidaya');
                $this->assertSame($saved, $owner->fresh()->fokus_budidaya);
            }
        });
        $this->assertSame('Udang', $other->fresh()->fokus_budidaya);
        $this->assertSame($other->name, $other->fresh()->name);
    }

    public function test_owner_products_keep_all_categories_product_ids_and_seller_identity_across_pages(): void
    {
        $owner = $this->user(['fokus_budidaya' => 'Nelayan']);
        $viewer = $this->user();
        $expected = [];
        foreach (['tawar', 'laut', 'olahan'] as $category) {
            $product = $this->product($owner);
            $product->update(['kategori' => $category, 'stok' => 99]);
            $expected[$product->id] = $category;
            $this->product($viewer)->update(['kategori' => $category]);
        }
        Sanctum::actingAs($viewer);
        $seen = [];
        for ($page = 1; $page <= 3; $page++) {
            $response = $this->getJson('/api/profiles/'.$owner->id.'/pasars?per_page=1&page='.$page.'&user_id='.$viewer->id)
                ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('meta.total', 3)
                ->assertJsonPath('meta.current_page', $page)->assertJsonPath('meta.last_page', 3)
                ->assertJsonPath('data.0.user_id', $owner->id)->assertJsonPath('data.0.stok', 99)
                ->assertJsonPath('data.0.user', ['id' => $owner->id, 'name' => $owner->name, 'foto_profil' => $owner->foto_profil]);
            $item = $response->json('data.0');
            $this->assertSame($expected[$item['id']], $item['kategori']);
            $this->assertNotSame($item['stok'], $item['id']);
            $this->assertSafe($response->json());
            $seen[] = $item['id'];
        }
        $this->assertSame(array_reverse(array_keys($expected)), $seen);
        $this->getJson('/api/profiles/'.$owner->id)->assertOk()->assertJsonPath('data.fokus_budidaya', 'Nelayan');
    }

    public function test_owner_filter_pagination_and_deterministic_order(): void
    {
        $owner = $this->user();
        $other = $this->user();
        $ids = [];
        for ($i = 0; $i < 52; $i++) {
            $product = $this->product($owner);
            $product->forceFill(['created_at' => '2026-01-01 00:00:00'])->save();
            $ids[] = $product->id;
        }
        $this->product($other);
        Sanctum::actingAs($other);
        $base = '/api/profiles/'.$owner->id.'/pasars';
        $response = $this->getJson($base.'?user_id='.$other->id)->assertOk()
            ->assertJsonCount(20, 'data')->assertJsonPath('meta.total', 52);
        $this->assertSame(array_slice(array_reverse($ids), 0, 20), array_column($response->json('data'), 'id'));
        $this->assertSafe($response->json());
        $this->assertSame('082222222222', $response->json('data.0.nomor_wa'));
        $this->getJson($base.'?per_page=50')->assertOk()->assertJsonCount(50, 'data');
        $this->getJson($base.'?per_page=50&page=2')->assertOk()->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $ids[1])->assertJsonPath('meta.last_page', 2);
        $this->getJson($base.'?page=99')->assertOk()->assertJsonCount(0, 'data')->assertJsonPath('meta.total', 52);
        foreach (['per_page=51', 'per_page=0', 'per_page=-1', 'per_page=x', 'page=0', 'page=1.5', 'page[]=1'] as $query) {
            $this->getJson($base.'?'.$query)->assertUnprocessable();
        }
        $old = $this->product($owner);
        $old->forceFill(['created_at' => '2025-01-01 00:00:00'])->save();
        $this->getJson($base)->assertOk()->assertJsonPath('data.0.id', $ids[51]);
    }

    public function test_reads_are_bounded_read_only_and_do_not_grant_mutation_rights(): void
    {
        $owner = $this->user();
        $product = $this->product($owner);
        Sanctum::actingAs($this->user());
        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->sql;
        });
        $this->getJson('/api/profiles/'.$owner->id)->assertOk();
        $queries = [];
        $this->getJson('/api/profiles/'.$owner->id.'/pasars')->assertOk();
        $count = count($queries);
        foreach ($queries as $query) {
            $this->assertMatchesRegularExpression('/^select\b/i', $query);
        }
        $this->assertLessThanOrEqual(4, $count);
        for ($i = 0; $i < 12; $i++) {
            $this->product($owner);
        }
        $queries = [];
        $this->getJson('/api/profiles/'.$owner->id.'/pasars')->assertOk();
        $this->assertCount($count, $queries);
        $this->postJson('/api/pasars/'.$product->id.'/update', ['nama_ikan' => 'Hijack'])->assertForbidden();
        $this->deleteJson('/api/pasars/'.$product->id)->assertForbidden();
        $this->assertDatabaseHas('pasars', ['id' => $product->id, 'nama_ikan' => 'Lele sintetis', 'user_id' => $owner->id]);
    }

    public function test_forum_comments_replies_and_market_have_safe_author_avatars(): void
    {
        $owner = $this->user();
        $post = CommunityPost::create(['user_id' => $owner->id, 'author_name' => $owner->name, 'content' => 'Forum']);
        $root = CommunityComment::create(['community_post_id' => $post->id, 'user_id' => $owner->id,
            'author_name' => $owner->name, 'content' => 'Root']);
        CommunityComment::create(['community_post_id' => $post->id, 'user_id' => $owner->id,
            'author_name' => $owner->name, 'content' => 'Reply', 'parent_id' => $root->id]);
        $this->product($owner);
        Sanctum::actingAs($owner);
        foreach ([
            ['/api/forum?mine=1', 'data.0.user'],
            ['/api/forum', 'data.0.comments.0.user'],
            ['/api/forum/'.$post->id.'/comments', 'data.data.0.user'],
            ['/api/forum/comment/'.$root->id.'/replies', 'data.data.0.user'],
            ['/api/pasars', 'data.0.user'],
        ] as [$path, $key]) {
            $response = $this->getJson($path)->assertOk()->assertJsonPath($key, [
                'id' => $owner->id, 'name' => $owner->name, 'foto_profil' => $owner->foto_profil,
            ]);
            $this->assertSafe($response->json());
        }
        $created = $this->postJson('/api/forum/'.$post->id.'/comment', ['content' => 'New'])->assertOk();
        $this->assertSafe($created->json());
        $this->assertSafe($this->patchJson('/api/forum/comment/'.$root->id, ['content' => 'Edited'])->assertOk()->json());
        User::withoutEvents(fn () => $owner->delete());
        Sanctum::actingAs($this->user());
        $this->getJson('/api/forum')->assertOk()->assertJsonPath('data.0.user', null)
            ->assertJsonPath('data.0.comments.0.user', null);
        $this->getJson('/api/forum/'.$post->id.'/comments')->assertOk()->assertJsonPath('data.data.0.user', null);
        $this->getJson('/api/forum/comment/'.$root->id.'/replies')->assertOk()->assertJsonPath('data.data.0.user', null);
    }

    public function test_legacy_identity_is_never_resolved_by_name_or_numeric_prefix(): void
    {
        $user = $this->user();
        Sanctum::actingAs($user);
        $post = CommunityPost::create(['user_id' => $user->id, 'author_name' => $user->name, 'content' => 'Forum']);
        $root = CommunityComment::create(['community_post_id' => $post->id, 'user_id' => $user->id,
            'author_name' => $user->name, 'content' => 'Identified root']);
        foreach ([$user->name, $user->id.'legacy', '0'.$user->id, '99999'] as $identity) {
            CommunityComment::create(['community_post_id' => $post->id, 'user_id' => $identity,
                'author_name' => $user->name, 'content' => 'Legacy']);
            CommunityComment::create(['community_post_id' => $post->id, 'user_id' => $identity,
                'author_name' => $user->name, 'content' => 'Legacy reply', 'parent_id' => $root->id]);
        }
        $response = $this->getJson('/api/forum/'.$post->id.'/comments')->assertOk();
        $this->assertSame($user->id, $response->json('data.data.0.user.id'));
        foreach (array_slice($response->json('data.data'), 1) as $comment) {
            $this->assertNull($comment['user']);
        }
        $replies = $this->getJson('/api/forum/comment/'.$root->id.'/replies')->assertOk()->assertJsonCount(4, 'data.data');
        foreach ($replies->json('data.data') as $comment) {
            $this->assertNull($comment['user']);
        }
    }

    public function test_resources_ignore_loaded_private_relations_and_future_attributes(): void
    {
        $user = $this->user();
        $user->setAttribute('internal_metadata', 'private');
        $user->setRelation('tokens', collect([['token' => 'private']]));
        $product = $this->product($user);
        $product->setAttribute('internal_metadata', 'private');
        $product->setRelation('user', $user);
        $product->setRelation('preOrders', collect([['token' => 'private']]));
        $this->assertSame(['id', 'name', 'foto_profil', 'fokus_budidaya'], array_keys((new ProfileResource($user))->resolve()));
        $json = (new ProfileProductResource($product))->resolve();
        $this->assertSafe($json);
        $this->assertArrayNotHasKey('internal_metadata', $json);
        $this->assertArrayNotHasKey('internal_metadata', $json['user']);
        $product->setRelation('user', null);
        $this->assertNull((new ProfileProductResource($product))->resolve()['user']);
    }
}
