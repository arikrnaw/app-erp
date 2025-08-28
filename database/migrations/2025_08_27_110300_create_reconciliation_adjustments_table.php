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
        Schema::create('reconciliation_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reconciliation_id')->nullable()->constrained('bank_reconciliations')->onDelete('cascade');
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->onDelete('cascade');
            $table->enum('type', ['bank_charge', 'interest_earned', 'service_fee', 'other']);
            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->date('date');
            $table->string('reference')->nullable();
            $table->foreignId('related_transaction_id')->nullable()->constrained('bank_transactions')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->boolean('approved')->default(false);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['reconciliation_id', 'type']);
            $table->index(['bank_account_id', 'type']);
            $table->index('type');
            $table->index('amount');
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reconciliation_adjustments');
    }
};
