<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ForumCommentResource;
use App\Models\CommunityComment;
use App\Models\CommunityPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ForumCommentController extends Controller
{
    public static function presentation($query, Request $request)
    {
        return $query->with('user:id,name,foto_profil')
            ->withCount(['likes', 'replies'])
            ->withExists(['likes as is_liked_by_me' => fn ($likes) => $likes->where('user_id', $request->user()->id)]);
    }

    public function index(Request $request, $id)
    {
        abort_unless(CommunityPost::whereKey($id)->exists(), 404);

        return $this->listing($request, CommunityComment::where('community_post_id', $id)->whereNull('parent_id'));
    }

    public function replies(Request $request, $id)
    {
        $parent = CommunityComment::find($id);
        abort_unless($parent, 404);

        return $this->listing($request, $parent->replies());
    }

    private function listing(Request $request, $query)
    {
        $request->validate(['page' => 'sometimes|integer|min:1', 'per_page' => 'sometimes|integer|min:1|max:50']);
        $page = self::presentation($query, $request)->orderBy('id')->paginate((int) $request->input('per_page', 20));
        $page->through(fn ($comment) => (new ForumCommentResource($comment))->resolve($request));

        return response()->json(['success' => true, 'data' => $page]);
    }

    public function store(Request $request, $id)
    {
        abort_unless(CommunityPost::whereKey($id)->exists(), 404);
        $data = $request->validate([
            'content' => 'required|string|max:5000',
            'parent_id' => ['nullable', 'integer', Rule::exists('community_comments', 'id')->where('community_post_id', $id)->whereNull('parent_id')],
        ]);
        // Legacy identity fields are ignored, never mass-assigned.
        $comment = DB::transaction(function () use ($request, $id, $data) {
            if (isset($data['parent_id'])) {
                // Serialize creation with parent deletion so no orphan reply is inserted.
                abort_unless(CommunityComment::whereKey($data['parent_id'])->lockForUpdate()->first(), 404);
            }

            return CommunityComment::create([
                'community_post_id' => $id,
                'user_id' => $request->user()->id,
                'author_name' => $request->user()->name,
                'content' => $data['content'],
                'parent_id' => $data['parent_id'] ?? null,
            ]);
        });

        return response()->json(['success' => true, 'data' => new ForumCommentResource(self::presentation(CommunityComment::query(), $request)->findOrFail($comment->id))]);
    }

    public function update(Request $request, $id)
    {
        $comment = CommunityComment::find($id);
        abort_unless($comment, 404);
        abort_unless((int) $comment->user_id === (int) $request->user()->id, 403);
        $data = $request->validate([
            'content' => 'required|string|max:5000',
            'user_id' => 'missing', 'author_name' => 'missing',
            'community_post_id' => 'missing', 'parent_id' => 'missing',
        ]);
        $comment->update(['content' => $data['content']]);

        return response()->json(['success' => true, 'data' => new ForumCommentResource(self::presentation(CommunityComment::query(), $request)->findOrFail($id))]);
    }

    public function like(Request $request, $id)
    {
        return $this->setLike($request, $id, true);
    }

    public function unlike(Request $request, $id)
    {
        return $this->setLike($request, $id, false);
    }

    private function setLike(Request $request, $id, bool $liked)
    {
        return DB::transaction(function () use ($request, $id, $liked) {
            $comment = CommunityComment::whereKey($id)->lockForUpdate()->first();
            abort_unless($comment, 404);
            if ($liked) {
                $comment->likes()->firstOrCreate(['user_id' => $request->user()->id]);
            } else {
                $comment->likes()->where('user_id', $request->user()->id)->delete();
            }

            return response()->json(['success' => true, 'data' => [
                'id' => $comment->id,
                'is_liked_by_me' => $comment->likes()->where('user_id', $request->user()->id)->exists(),
                'likes_count' => $comment->likes()->count(),
            ]]);
        });
    }
}
