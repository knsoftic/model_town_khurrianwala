<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Website settings, navigation, homepage sections, pages, SEO, statistics and social links.
     */
    public function up(): void
    {
        Schema::create('website_settings', function (Blueprint $table) {
            $table->id();
            $table->string('group', 50)->index();
            $table->string('key', 100)->unique();
            $table->longText('value')->nullable();
            $table->string('type', 30)->default('text');
            $table->timestamps();
        });

        Schema::create('navigation_items', function (Blueprint $table) {
            $table->id();
            $table->string('location', 30);
            $table->string('label', 100);
            $table->string('route_name', 100)->nullable();
            $table->string('url', 500)->nullable();
            $table->string('target', 10)->default('_self');
            $table->foreignId('parent_id')->nullable()->constrained('navigation_items')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->timestamps();

            $table->index(['location', 'is_active', 'sort_order']);
        });

        Schema::create('homepage_sections', function (Blueprint $table) {
            $table->id();
            $table->string('key', 60)->unique();
            $table->string('type', 40);
            $table->string('name', 100);
            $table->string('eyebrow', 150)->nullable();
            $table->string('heading')->nullable();
            $table->string('subheading', 500)->nullable();
            $table->text('description')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 60)->unique();
            $table->string('name', 100);
            $table->string('eyebrow', 150)->nullable();
            $table->string('heading')->nullable();
            $table->string('subheading', 500)->nullable();
            $table->longText('content')->nullable();
            $table->string('banner_image')->nullable();
            $table->string('image')->nullable();
            $table->json('images')->nullable();
            $table->string('video_url', 1000)->nullable();
            $table->string('cta_text', 100)->nullable();
            $table->string('cta_url', 500)->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('seo_settings', function (Blueprint $table) {
            $table->id();
            $table->morphs('seoable');
            $table->string('title')->nullable();
            $table->string('description', 500)->nullable();
            $table->string('keywords', 500)->nullable();
            $table->string('canonical_url', 500)->nullable();
            $table->string('og_title')->nullable();
            $table->string('og_description', 500)->nullable();
            $table->string('og_image')->nullable();
            $table->string('robots', 50)->nullable();
            $table->longText('schema_json')->nullable();
            $table->timestamps();

            $table->unique(['seoable_type', 'seoable_id']);
        });

        Schema::create('statistics', function (Blueprint $table) {
            $table->id();
            $table->string('group', 30)->default('intro');
            $table->string('label', 100);
            $table->string('value', 30);
            $table->string('prefix', 10)->nullable();
            $table->string('suffix', 10)->nullable();
            $table->string('icon', 60)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['group', 'is_active', 'sort_order']);
        });

        Schema::create('social_links', function (Blueprint $table) {
            $table->id();
            $table->string('platform', 30);
            $table->string('label', 60)->nullable();
            $table->string('url', 500);
            $table->string('icon', 60)->nullable();
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
        Schema::dropIfExists('social_links');
        Schema::dropIfExists('statistics');
        Schema::dropIfExists('seo_settings');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('homepage_sections');
        Schema::dropIfExists('navigation_items');
        Schema::dropIfExists('website_settings');
    }
};
