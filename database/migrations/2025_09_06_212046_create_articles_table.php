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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content')->nullable();
            $table->text('description')->nullable();
            $table->string('url')->unique();
            $table->string('image_url')->nullable();
            $table->string('source_name');
            $table->string('source_id')->nullable();
            $table->string('category')->nullable();
            $table->timestamp('published_at');
            $table->timestamps();

            // Add indexes for better query performance
            $table->index(['source_name', 'published_at']);
            $table->index(['category', 'published_at']);
            $table->index('published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
