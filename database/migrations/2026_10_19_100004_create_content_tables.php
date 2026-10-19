<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Features, development progress, testimonials, gallery, videos and FAQs.
     */
    public function up(): void
    {
        Schema::create('project_features', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->string('icon', 60)->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('development_progress', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->unsignedTinyInteger('percentage')->default(0);
            $table->text('description')->nullable();
            $table->string('icon', 60)->nullable();
            $table->string('image')->nullable();
            $table->string('status', 20)->default('in_progress');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plot_id')->nullable()->constrained()->nullOnDelete();
            $table->string('client_name', 150);
            $table->string('client_title', 150)->nullable();
            $table->string('client_image')->nullable();
            $table->string('location', 150)->nullable();
            $table->text('review');
            $table->unsignedTinyInteger('rating')->default(5);
            $table->string('video_url', 1000)->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('gallery_albums', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 150)->unique();
            $table->string('description', 500)->nullable();
            $table->string('cover_image')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('gallery_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_album_id')->constrained()->cascadeOnDelete();
            $table->string('title', 150)->nullable();
            $table->string('path');
            $table->string('caption', 500)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['gallery_album_id', 'is_active', 'sort_order']);
        });

        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->string('provider', 20)->default('youtube');
            $table->string('url', 1000)->nullable();
            $table->string('file_path')->nullable();
            $table->string('thumbnail')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question', 500);
            $table->text('answer');
            $table->string('category', 60)->nullable()->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('videos');
        Schema::dropIfExists('gallery_images');
        Schema::dropIfExists('gallery_albums');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('development_progress');
        Schema::dropIfExists('project_features');
    }
};
