<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ForumPostResource;
use App\Http\Resources\PostDetailResource;
use App\Http\Resources\ProfileProductResource;
use App\Models\CommunityPost;
use App\Models\Pasar;
use App\Models\Post;
use Illuminate\Http\Request;

class ContentDetailController extends Controller
{
    public function pasar(Request $request, string $id)
    {
        $item = $this->validId($id) ? Pasar::with('user:id,name,foto_profil')->find($id) : null;

        return $this->detail($item ? new ProfileProductResource($item) : null);
    }

    public function forum(Request $request, string $id)
    {
        $item = $this->validId($id) ? CommunityPost::with('user:id,name,foto_profil')
            ->withCount(['likes', 'comments'])
            ->withCount(['comments as root_comments_count' => fn ($query) => $query->whereNull('parent_id')])
            ->withExists(['likes as is_liked_by_me' => fn ($query) => $query->where('user_id', $request->user()->id)])
            ->find($id) : null;
        if ($item) {
            // Keep the existing parser's array contract without fetching any threads.
            $item->setRelation('comments', $item->comments()->getRelated()->newCollection());
        }

        return $this->detail($item ? new ForumPostResource($item) : null);
    }

    public function berita(Request $request, string $id)
    {
        $item = $this->validId($id) ? Post::find($id) : null;

        return $this->detail($item ? new PostDetailResource($item) : null);
    }

    private function validId(string $id): bool
    {
        // Reject malformed/overflow IDs before binding to an integer database column.
        return preg_match('/^[1-9][0-9]*$/D', $id) === 1
            && filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false;
    }

    private function detail($resource)
    {
        return ($resource
            ? response()->json(['success' => true, 'data' => $resource])
            : response()->json(['message' => 'Not Found'], 404))
            ->header('Cache-Control', 'private, no-store');
    }
}
