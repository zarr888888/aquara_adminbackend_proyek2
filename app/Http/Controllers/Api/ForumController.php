<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ForumPostResource;
use App\Models\CommunityPost;
use App\Models\CommunityComment;
use App\Models\PostLike;
use App\Support\CloudinaryUrl;
use App\Traits\UploadsToCloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ForumController extends Controller
{
    use UploadsToCloudinary;

    public function index(Request $request)
    {
        $currentUserId = $request->user()->id;

        $request->validate(['page' => 'sometimes|integer|min:1', 'per_page' => 'sometimes|integer|min:1|max:50']);
        $posts = CommunityPost::with(['user:id,name,foto_profil', 'comments' => fn ($query) => ForumCommentController::presentation($query, $request)->whereNull('parent_id')->limit(3)])
            ->when($request->boolean('mine'), fn ($query) => $query->where('user_id', $currentUserId))
            ->withCount(['likes', 'comments'])
            ->withCount(['comments as root_comments_count' => fn ($query) => $query->whereNull('parent_id')])
            ->withExists(['likes as is_liked_by_me' => fn ($query) => $query->where('user_id', $currentUserId)])
            ->latest()->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 20));

        return response()->json(['success' => true, 'data' => ForumPostResource::collection($posts->getCollection()), 'meta' => [
            'current_page' => $posts->currentPage(), 'per_page' => $posts->perPage(),
            'total' => $posts->total(), 'last_page' => $posts->lastPage(),
        ]]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:5000',
            'image' => 'nullable|image|max:5120',
        ]);
        
        $imagePath = $request->file('image')
            ? $this->uploadFotoToCloudinary($request->file('image'), 'samudera/forum-images')
            : null;
        
        $post = CommunityPost::create([
            'user_id' => $request->user()->id,
            'author_name' => $request->user()->name,
            'content' => $request->content, 
            'image' => $imagePath
        ]);

                \App\Models\Notification::create([
            'user_id' => null,
            'title' => '💬 Diskusi Baru di Forum SAMUDERA',
            'message' => "{$request->author_name} bertanya: \"" . \Illuminate\Support\Str::limit($request->content, 40) . "\"",
            'type' => 'forum',
            'reference_id' => $post->id

        ]);

        // PELATUK FIREBASE: ADA POSTINGAN FORUM BARU!
        try {
            $firebase = (new \Kreait\Firebase\Factory)->withServiceAccount(storage_path('app/firebase-auth.json'));
            $messaging = $firebase->createMessaging();
            
            $pesanSingkat = \Illuminate\Support\Str::limit($request->content, 40);

            $message = \Kreait\Firebase\Messaging\CloudMessage::new()
                ->withNotification(\Kreait\Firebase\Messaging\Notification::create(
                    '💬 Diskusi Baru di Forum SAMUDERA',
                    "{$request->author_name} bertanya: \"{$pesanSingkat}\". Yuk bantu jawab!"
                ))
                ->withData(['type' => 'forum'])->toTopic('all_users');

            $messaging->send($message);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('FCM Error Forum: ' . $e->getMessage());
        }

        return response()->json(['success' => true]);
    }

    public function updatePost(Request $request, $id)
    {
        $post = CommunityPost::find($id);
        if (!$post) return response()->json(['success' => false, 'message' => 'Not found'], 404);
        abort_unless((int) $post->user_id === (int) $request->user()->id, 403);

        $request->validate(['content' => 'required', 'image' => 'nullable|image|max:10240']);
        
        $dataToUpdate = ['content' => $request->content];

        if ($request->hasFile('image')) {
            if ($post->image) {
                if (CloudinaryUrl::isCloudinaryUrl($post->image)) {
                    $this->deleteFotoFromCloudinary($post->image);
                } else {
                    // Foto lama (sebelum Fase 1) masih tersimpan di disk `public`.
                    Storage::disk('public')->delete($post->image);
                }
            }
            $dataToUpdate['image'] = $this->uploadFotoToCloudinary($request->file('image'), 'samudera/forum-images');
        }

        $post->update($dataToUpdate);
        return response()->json(['success' => true]);
    }

    public function destroy(Request $request, $id)
    {
        $post = CommunityPost::find($id);
        
        if ($post) { 
            abort_unless((int) $post->user_id === (int) $request->user()->id, 403);
            \App\Models\Notification::where('type', 'forum')->where('reference_id', $id)->delete();
            
            $post->delete(); 
            
            return response()->json(['success' => true]); 
        }
        
        return response()->json(['success' => false], 404);
    }

    public function toggleLike(Request $request, $id)
    {
        $userId = $request->user()->id;
        $post = CommunityPost::find($id);
        if (!$post) return response()->json(['success' => false], 404);

        $existingLike = PostLike::where('user_id', $userId)->where('community_post_id', $id)->first();

        if ($existingLike) {
            $existingLike->delete(); 
            $status = 'unliked';
        } else {
            PostLike::create(['user_id' => $userId, 'community_post_id' => $id]); 
            $status = 'liked';
        }

        return response()->json(['success' => true, 'status' => $status]);
    }

    public function storeComment(Request $request, $id)
    {
        return app(ForumCommentController::class)->store($request, $id);
    }

    public function destroyComment(Request $request, $id)
    {
        $comment = CommunityComment::find($id);
        if ($comment) {
            abort_unless((int) $comment->user_id === (int) $request->user()->id, 403);
            $comment->delete();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }
}
