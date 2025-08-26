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
        Schema::table('journal_entry_lines', function (Blueprint $table) {
            // Add new columns
            $table->decimal('debit_amount', 15, 2)->default(0)->after('amount');
            $table->decimal('credit_amount', 15, 2)->default(0)->after('debit_amount');
            $table->unsignedInteger('line_number')->after('credit_amount');
        });

        // Drop old columns after adding new ones
        Schema::table('journal_entry_lines', function (Blueprint $table) {
            $table->dropColumn(['type', 'amount']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journal_entry_lines', function (Blueprint $table) {
            // Re-add old columns
            $table->enum('type', ['debit', 'credit'])->after('account_id');
            $table->decimal('amount', 15, 2)->after('type');
            
            // Drop new columns
            $table->dropColumn(['debit_amount', 'credit_amount', 'line_number']);
        });
    }
};
