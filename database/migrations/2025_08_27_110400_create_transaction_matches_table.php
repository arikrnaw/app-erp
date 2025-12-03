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
        Schema::create('transaction_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reconciliation_id')->nullable()->constrained('bank_reconciliations')->onDelete('cascade');
            $table->foreignId('bank_transaction_id')->constrained('bank_transactions')->onDelete('cascade');
            $table->foreignId('book_transaction_id')->constrained('journal_entry_lines')->onDelete('cascade');
            $table->integer('match_score')->default(0); // 0-100 percentage
            $table->enum('match_type', ['exact', 'partial', 'manual'])->default('manual');
            $table->integer('confidence_score')->default(0); // 0-100 percentage (alias for match_score)
            $table->foreignId('matched_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('matched_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['reconciliation_id', 'match_type']);
            $table->index(['bank_transaction_id', 'book_transaction_id'], 'idx_trans_matches_ids');
            $table->index('match_score');
            $table->index('match_type');

            // Unique constraint to prevent duplicate matches
            $table->unique(['bank_transaction_id', 'book_transaction_id'], 'unique_transaction_match');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_matches');
    }
};
