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
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->onDelete('cascade');
            $table->foreignId('statement_id')->nullable()->constrained('bank_statements')->onDelete('cascade');
            $table->date('transaction_date');
            $table->date('value_date')->nullable();
            $table->string('description');
            $table->string('reference_number')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('IDR');
            $table->enum('transaction_type', ['deposit', 'withdrawal', 'transfer', 'charge'])->default('deposit');
            $table->string('counterparty')->nullable();
            $table->boolean('is_reconciled')->default(false);
            $table->text('reconciliation_notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['bank_account_id', 'transaction_date']);
            $table->index(['statement_id', 'is_reconciled']);
            $table->index('transaction_date');
            $table->index('is_reconciled');
            $table->index('transaction_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
    }
};
