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
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('base_currency_id')->constrained('currencies')->onDelete('cascade');
            $table->foreignId('target_currency_id')->constrained('currencies')->onDelete('cascade');
            $table->decimal('rate', 12, 8); // Exchange rate with 8 decimal places
            $table->date('effective_date'); // Date when this rate becomes effective
            $table->string('source', 50)->default('manual'); // Source: manual, api, etc.
            $table->text('notes')->nullable(); // Additional notes
            $table->boolean('is_active')->default(true); // Is this rate currently active?
            $table->timestamps();

            // Indexes
            $table->index(['base_currency_id', 'target_currency_id']);
            $table->index(['base_currency_id', 'target_currency_id', 'effective_date'], 'idx_ex_rates_curr_date');
            $table->index('effective_date');
            $table->index('source');
            $table->index('is_active');

            // Unique constraint to ensure only one active rate per currency pair
            $table->unique(['base_currency_id', 'target_currency_id'], 'unique_active_exchange_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
