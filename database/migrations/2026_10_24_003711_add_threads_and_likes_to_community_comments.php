<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('community_comments', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->constrained('community_comments')->cascadeOnDelete();
            $table->index(['community_post_id', 'parent_id', 'id'], 'comments_post_parent_id_index');
            $table->index(['parent_id', 'id'], 'comments_parent_id_index');
        });
        Schema::create('comment_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_comment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['community_comment_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // InnoDB may replace the original implicit FK index with our composite index.
        // Restore a standalone supporting index before removing that composite index.
        if (! Schema::hasIndex('community_comments', ['community_post_id'])) {
            Schema::table('community_comments', function (Blueprint $table) {
                $table->index('community_post_id', 'community_comments_post_restore_index');
            });
        }
        Schema::dropIfExists('comment_likes');
        Schema::table('community_comments', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropIndex('comments_post_parent_id_index');
            $table->dropIndex('comments_parent_id_index');
            $table->dropColumn('parent_id');
        });
    }
};
