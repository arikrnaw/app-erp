<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, add new columns with new names
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->string('name_new')->nullable()->after('account_name');
            $table->decimal('balance_new', 15, 2)->nullable()->after('current_balance');
            $table->string('status_new')->nullable()->after('is_active');
        });
        
        // Copy data to new columns
        DB::statement("UPDATE bank_accounts SET name_new = account_name");
        DB::statement("UPDATE bank_accounts SET balance_new = current_balance");
        DB::statement("UPDATE bank_accounts SET status_new = CASE WHEN is_active = true THEN 'active' ELSE 'inactive' END");
        
        // Drop old columns
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropColumn(['account_name', 'current_balance', 'is_active']);
        });
        
        // Rename new columns
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->renameColumn('name_new', 'name');
            $table->renameColumn('balance_new', 'balance');
            $table->renameColumn('status_new', 'status');
        });
        
        // Make columns not nullable
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
            $table->decimal('balance', 15, 2)->nullable(false)->change();
            $table->string('status')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add old columns back
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->string('account_name_old')->nullable()->after('name');
            $table->decimal('current_balance_old', 15, 2)->nullable()->after('balance');
            $table->boolean('is_active_old')->nullable()->after('status');
        });
        
        // Copy data back
        DB::statement("UPDATE bank_accounts SET account_name_old = name");
        DB::statement("UPDATE bank_accounts SET current_balance_old = balance");
        DB::statement("UPDATE bank_accounts SET is_active_old = CASE WHEN status = 'active' THEN true ELSE false END");
        
        // Drop new columns
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropColumn(['name', 'balance', 'status']);
        });
        
        // Rename old columns back
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->renameColumn('account_name_old', 'account_name');
            $table->renameColumn('current_balance_old', 'current_balance');
            $table->renameColumn('is_active_old', 'is_active');
        });
    }
};
