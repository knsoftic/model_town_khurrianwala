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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->string('description')->nullable();
            $table->boolean('is_super')->default(false);
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->string('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('phone', 30)->nullable()->after('email');
            $table->string('avatar')->nullable()->after('phone');
            $table->boolean('is_active')->default(true)->after('password')->index();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
            $table->dropColumn(['phone', 'avatar', 'is_active', 'last_login_at', 'last_login_ip', 'deleted_at']);
        });

        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
