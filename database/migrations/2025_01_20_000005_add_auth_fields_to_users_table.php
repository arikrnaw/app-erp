<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'auth_provider')) {
                $table->string('auth_provider')->default('email'); // 'email', 'workos', 'google', etc.
            }
            if (!Schema::hasColumn('users', 'auth_metadata')) {
                $table->json('auth_metadata')->nullable(); // Store additional auth provider data
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable();
            }
            if (!Schema::hasColumn('users', 'last_login_ip')) {
                $table->string('last_login_ip')->nullable();
            }
            if (!Schema::hasColumn('users', 'email_verification_token')) {
                $table->string('email_verification_token')->nullable();
            }
            if (!Schema::hasColumn('users', 'password_changed_at')) {
                $table->timestamp('password_changed_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'auth_provider',
                'auth_metadata',
                'is_active',
                'last_login_at',
                'last_login_ip',
                'email_verification_token',
                'password_changed_at'
            ]);
        });
    }
};
