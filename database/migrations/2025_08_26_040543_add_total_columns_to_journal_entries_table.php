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
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->decimal('total_debit', 15, 2)->default(0)->after('description');
            $table->decimal('total_credit', 15, 2)->default(0)->after('total_debit');
            $table->timestamp('posted_at')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropColumn(['total_debit', 'total_credit', 'posted_at']);
        });
    }
};
