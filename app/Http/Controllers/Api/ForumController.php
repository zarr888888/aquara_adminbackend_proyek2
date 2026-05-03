<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CommunityPost;
use App\Models\CommunityComment;
use App\Models\PostLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ForumController extends Controller
{
    public function index(Request $request)
    {
        $currentUserId = $request->query('user_id'); 

        $posts = CommunityPost::with(['comments', 'likes', 'user', 'comments.user'])
            ->withCount(['likes', 'comments']) 
            ->latest()
            ->get()
            ->map(function ($post) use ($currentUserId) {
                $post->is_liked_by_me = $post->likes->contains('user_id', $currentUserId);
                return $post;
            });

        return response()->json(['success' => true, 'data' => $posts]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required', 'author_name' => 'required', 'content' => 'required',
            'image' => 'nullable|image|max:2048',
        ]);
        
        $imagePath = $request->file('image') ? $request->file('image')->store('forum_images', 'public') : null;
        
        $post = CommunityPost::create([
            'user_id' => $request->user_id, 
            'author_name' => $request->author_name, 
            'content' => $request->content, 
            'image' => $imagePath
        ]);

                \App\Models\Notification::create([
            'user_id' => null,
            'title' => '💬 Diskusi Baru di Forum AQUARA',
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
                    '💬 Diskusi Baru di Forum AQUARA',
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

        $request->validate(['content' => 'required', 'image' => 'nullable|image|max:2048']);
        
        $dataToUpdate = ['content' => $request->content];

        if ($request->hasFile('image')) {
            if ($post->image) { Storage::disk('public')->delete($post->image); }
            $dataToUpdate['image'] = $request->file('image')->store('forum_images', 'public');
        }

        $post->update($dataToUpdate);
        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $post = CommunityPost::find($id);
        
        if ($post) { 
            \App\Models\Notification::where('type', 'forum')->where('reference_id', $id)->delete();
            
            $post->delete(); 
            
            return response()->json(['success' => true]); 
        }
        
        return response()->json(['success' => false], 404);
    }

    public function toggleLike(Request $request, $id)
    {
        $userId = $request->user_id;
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
        CommunityComment::create([
            'community_post_id' => $id, 
            'user_id' => $request->user_id, 
            'author_name' => $request->author_name, 
            'content' => $request->content
        ]);
        return response()->json(['success' => true]);
    }

    public function destroyComment($id)
    {
        $comment = CommunityComment::find($id);
        if ($comment) {
            $comment->delete();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }
}
