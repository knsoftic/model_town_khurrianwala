<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Leads, booking requests, notes, contact messages, newsletter subscribers and activity logs.
     */
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plot_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 150);
            $table->string('phone', 30);
            $table->string('email', 150)->nullable();
            $table->string('budget', 100)->nullable();
            $table->string('payment_preference', 30)->nullable();
            $table->string('preferred_contact_method', 20)->nullable();
            $table->string('preferred_time', 60)->nullable();
            $table->text('message')->nullable();
            $table->string('source', 30)->default('inquiry');
            $table->string('status', 20)->default('new');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->date('follow_up_date')->nullable();
            $table->timestamp('last_contacted_at')->nullable();
            $table->string('page_url', 500)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('source');
            $table->index('follow_up_date');
            $table->index('created_at');
            $table->index('phone');
        });

        Schema::create('booking_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 30)->unique();
            $table->foreignId('plot_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('installment_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 150);
            $table->string('phone', 30);
            $table->string('email', 150)->nullable();
            $table->text('cnic')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('preferred_contact_method', 20)->nullable();
            $table->text('message')->nullable();
            $table->string('status', 20)->default('pending');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('created_at');
        });

        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->morphs('notable');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 150);
            $table->string('email', 150)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('subject', 200)->nullable();
            $table->text('message');
            $table->timestamp('read_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('read_at');
        });

        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email', 150)->unique();
            $table->boolean('is_active')->default(true);
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 50);
            $table->nullableMorphs('subject');
            $table->string('description', 500);
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('newsletter_subscribers');
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('notes');
        Schema::dropIfExists('booking_requests');
        Schema::dropIfExists('leads');
    }
};
