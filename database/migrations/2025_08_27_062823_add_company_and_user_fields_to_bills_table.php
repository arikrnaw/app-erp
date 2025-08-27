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
        Schema::table('bills', function (Blueprint $table) {
            $table->foreignId('company_id')->after('id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->after('balance_amount')->constrained('users')->onDelete('cascade');
            $table->timestamp('posted_at')->nullable()->after('created_by');
            $table->foreignId('journal_entry_id')->nullable()->after('posted_at')->constrained('journal_entries')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['journal_entry_id']);
            $table->dropColumn(['company_id', 'created_by', 'posted_at', 'journal_entry_id']);
        });
    }
};
