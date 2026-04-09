<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fish_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); 
            $table->string('image')->nullable(); 

            $table->decimal('fcr_ratio', 8, 2)->default(1.0); 
            $table->integer('harvest_duration_days'); 
            $table->integer('standard_density_per_m2'); 
            $table->decimal('survival_rate', 5, 2)->default(90); 
            
            $table->decimal('market_price_per_kg', 12, 2); 
            $table->decimal('feed_price_per_kg', 12, 2); 

            $table->longText('cultivation_guide')->nullable(); 

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fish_types');
    }
};