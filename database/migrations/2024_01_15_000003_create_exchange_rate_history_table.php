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
        Schema::create('exchange_rate_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('base_currency_id')->constrained('currencies')->onDelete('cascade');
            $table->foreignId('target_currency_id')->constrained('currencies')->onDelete('cascade');
            $table->decimal('rate', 12, 8); // Exchange rate with 8 decimal places
            $table->date('date'); // Date of this rate
            $table->string('source', 50)->default('manual'); // Source: manual, api, etc.
            $table->text('notes')->nullable(); // Additional notes
            $table->decimal('volume', 15, 2)->nullable(); // Trading volume if available
            $table->decimal('change', 12, 8)->nullable(); // Change from previous rate
            $table->decimal('change_percentage', 8, 4)->nullable(); // Percentage change
            $table->timestamps();

            // Indexes
            $table->index(['base_currency_id', 'target_currency_id']);
            $table->index(['base_currency_id', 'target_currency_id', 'date'], 'idx_ex_rate_hist_curr_date');
            $table->index('date');
            $table->index('source');
            $table->index('change_percentage');

            // Unique constraint to ensure only one rate per currency pair per date
            $table->unique(['base_currency_id', 'target_currency_id', 'date'], 'unique_exchange_rate_history');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rate_history');
    }
};
