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
            if (!$post->image) {
                return;
            }

            // Foto baru (Fase 1) disimpan sebagai URL Cloudinary.
            // Foto lama (sebelum Fase 1) masih berupa path relatif di disk `public`.
            if (\App\Support\CloudinaryUrl::isCloudinaryUrl($post->image)) {
                try {
                    $path = \App\Support\CloudinaryUrl::extractPath($post->image);
                    if ($path) {
                        Storage::disk('cloudinary')->delete($path);
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Cloudinary Delete Error (Forum): ' . $e->getMessage());
                }
            } else {
                Storage::disk('public')->delete($post->image);
            }
        });
    }
}