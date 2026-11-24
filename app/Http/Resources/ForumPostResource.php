<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ForumPostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'author_name' => $this->author_name,
            'content' => $this->content,
            'image' => $this->image,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'likes_count' => $this->likes_count,
            'comments_count' => $this->comments_count,
            'root_comments_count' => $this->root_comments_count,
            'is_liked_by_me' => (bool) $this->is_liked_by_me,
            'user' => ProfileResource::author($this->resource, $request),
            'comments' => ForumCommentResource::collection($this->whenLoaded('comments')),
        ];
    }
}
