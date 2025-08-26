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
            // Drop the existing foreign key constraint
            $table->dropForeign(['account_id']);
            
            // Add new foreign key constraint to chart_of_accounts table
            $table->foreign('account_id')->references('id')->on('chart_of_accounts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journal_entry_lines', function (Blueprint $table) {
            // Drop the new foreign key constraint
            $table->dropForeign(['account_id']);
            
            // Restore the original foreign key constraint to accounts table
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        });
    }
};
