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
        Schema::table('bank_accounts', function (Blueprint $table) {
            // Add new columns that don't exist
            if (!Schema::hasColumn('bank_accounts', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
            if (!Schema::hasColumn('bank_accounts', 'bank_branch')) {
                $table->string('bank_branch')->nullable()->after('bank_name');
            }
            if (!Schema::hasColumn('bank_accounts', 'swift_code')) {
                $table->string('swift_code', 11)->nullable()->after('bank_branch');
            }
            if (!Schema::hasColumn('bank_accounts', 'iban')) {
                $table->string('iban', 50)->nullable()->after('swift_code');
            }
            if (!Schema::hasColumn('bank_accounts', 'currency')) {
                $table->string('currency', 3)->default('IDR')->after('iban');
            }
            if (!Schema::hasColumn('bank_accounts', 'opening_date')) {
                $table->date('opening_date')->nullable()->after('opening_balance');
            }
            if (!Schema::hasColumn('bank_accounts', 'reconcile_automatically')) {
                $table->boolean('reconcile_automatically')->default(false)->after('status');
            }
            if (!Schema::hasColumn('bank_accounts', 'allow_overdraft')) {
                $table->boolean('allow_overdraft')->default(false)->after('reconcile_automatically');
            }
            if (!Schema::hasColumn('bank_accounts', 'include_in_cash_flow')) {
                $table->boolean('include_in_cash_flow')->default(true)->after('allow_overdraft');
            }
            if (!Schema::hasColumn('bank_accounts', 'notes')) {
                $table->text('notes')->nullable()->after('include_in_cash_flow');
            }
            if (!Schema::hasColumn('bank_accounts', 'last_reconciled_date')) {
                $table->date('last_reconciled_date')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('bank_accounts', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            // Remove new columns
            $table->dropColumn([
                'description', 'bank_branch', 'swift_code', 'iban', 'currency',
                'opening_date', 'reconcile_automatically', 'allow_overdraft',
                'include_in_cash_flow', 'notes', 'last_reconciled_date', 'deleted_at'
            ]);
        });
    }
};
