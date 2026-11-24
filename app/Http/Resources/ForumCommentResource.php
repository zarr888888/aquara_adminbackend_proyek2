<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ForumCommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'community_post_id' => $this->community_post_id,
            'user_id' => $this->user_id,
            'author_name' => $this->author_name,
            'content' => $this->content,
            'parent_id' => $this->parent_id,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'likes_count' => $this->likes_count,
            'replies_count' => $this->replies_count,
            'is_liked_by_me' => (bool) $this->is_liked_by_me,
            'user' => ProfileResource::author($this->resource, $request),
        ];
    }
}
