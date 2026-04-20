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
        Schema::create('notifications', function (Blueprint $string) {
            $string->id();
            $string->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $string->string('title');
            $string->text('message');
            $string->string('type');
            $string->boolean('is_read')->default(false);
            $string->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
