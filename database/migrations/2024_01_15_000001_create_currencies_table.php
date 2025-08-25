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
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique(); // ISO 4217 currency code (e.g., USD, EUR)
            $table->string('symbol', 5); // Currency symbol (e.g., $, €)
            $table->string('name', 100); // Currency name (e.g., US Dollar, Euro)
            $table->text('description')->nullable(); // Optional description
            $table->integer('decimal_places')->default(2); // Number of decimal places
            $table->boolean('is_base')->default(false); // Is this the base currency?
            $table->boolean('is_active')->default(true); // Is this currency active?
            $table->boolean('auto_update')->default(false); // Auto-update exchange rates?
            $table->string('exchange_rate_source', 100)->nullable(); // Source for exchange rates
            $table->timestamp('last_update')->nullable(); // Last exchange rate update
            $table->timestamp('next_update')->nullable(); // Next scheduled update
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['code', 'is_active']);
            $table->index('is_base');
            $table->index('auto_update');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
