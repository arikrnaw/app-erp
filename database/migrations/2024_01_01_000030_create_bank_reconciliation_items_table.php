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
        Schema::create('bank_reconciliation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_reconciliation_id')->constrained()->onDelete('cascade');
            $table->foreignId('bank_transaction_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('journal_entry_id')->nullable()->constrained()->onDelete('set null');
            $table->string('description');
            $table->decimal('amount', 15, 2)->default(0);
            $table->enum('type', ['bank', 'book']);
            $table->boolean('is_matched')->default(false);
            $table->unsignedBigInteger('matched_with')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_reconciliation_items');
    }
};
