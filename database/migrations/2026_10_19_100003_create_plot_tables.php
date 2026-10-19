<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Locations, plot sizes, installment plans and payment schedules.
     */
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('city', 100)->nullable();
            $table->string('area', 150)->nullable();
            $table->string('address', 500)->nullable();
            $table->string('short_description', 500)->nullable();
            $table->longText('description')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('google_maps_url', 1000)->nullable();
            $table->string('map_embed_url', 2000)->nullable();
            $table->json('landmarks')->nullable();
            $table->string('distance_airport', 60)->nullable();
            $table->string('distance_motorway', 60)->nullable();
            $table->string('distance_main_road', 60)->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('plots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->string('size_label', 60);
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('category', 30)->default('residential');
            $table->string('short_description', 500)->nullable();
            $table->longText('full_description')->nullable();
            $table->string('dimensions', 60)->nullable();
            $table->unsignedInteger('area_sqft')->nullable();
            $table->decimal('starting_price', 15, 2)->nullable();
            $table->decimal('total_price', 15, 2)->nullable();
            $table->decimal('booking_amount', 15, 2)->nullable();
            $table->decimal('down_payment', 15, 2)->nullable();
            $table->unsignedSmallInteger('installment_count')->nullable();
            $table->decimal('installment_amount', 15, 2)->nullable();
            $table->string('payment_frequency', 20)->default('monthly');
            $table->decimal('after_6_months_payment', 15, 2)->nullable();
            $table->decimal('after_12_months_payment', 15, 2)->nullable();
            $table->decimal('end_payment', 15, 2)->nullable();
            $table->decimal('development_charges', 15, 2)->nullable();
            $table->decimal('possession_charges', 15, 2)->nullable();
            $table->unsignedInteger('total_units')->default(0);
            $table->unsignedInteger('sold_units')->default(0);
            $table->string('status', 20)->default('available');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_demo_price')->default(false);
            $table->string('price_note')->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index(['is_active', 'sort_order']);
            $table->index('is_featured');
        });

        Schema::create('plot_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plot_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('caption')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('installment_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plot_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('total_price', 15, 2)->default(0);
            $table->decimal('full_payment_price', 15, 2)->nullable();
            $table->unsignedSmallInteger('duration_months')->nullable();
            $table->text('terms')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_demo')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['plot_id', 'is_active', 'sort_order']);
        });

        Schema::create('payment_schedule_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('installment_plan_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30);
            $table->string('title');
            $table->decimal('amount', 15, 2)->default(0);
            $table->unsignedSmallInteger('occurrences')->default(1);
            $table->decimal('percentage', 6, 2)->nullable();
            $table->unsignedSmallInteger('due_after_months')->nullable();
            $table->string('due_label', 120)->nullable();
            $table->string('description', 500)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['installment_plan_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_schedule_items');
        Schema::dropIfExists('installment_plans');
        Schema::dropIfExists('plot_images');
        Schema::dropIfExists('plots');
        Schema::dropIfExists('locations');
    }
};
