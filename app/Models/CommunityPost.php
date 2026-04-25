<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property bool|null $is_liked_by_me
 */
class CommunityPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'author_name',
        'content',
        'image',
        'likes_count',
    ];

    public function comments(): HasMany
    {
        return $this->hasMany(CommunityComment::class, 'community_post_id')->latest(); 
    }

    public function likes(): HasMany
    {
        return $this->hasMany(PostLike::class, 'community_post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    protected static function boot() {
        parent::boot();
        static::deleting(function($post) {
            if ($post->image) { Storage::disk('public')->delete($post->image); }
        });
    }
}