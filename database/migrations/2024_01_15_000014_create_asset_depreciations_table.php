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
        Schema::create('asset_depreciations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('fixed_assets')->onDelete('cascade');
            $table->date('date');
            $table->decimal('amount', 15, 2);
            $table->enum('method', ['straight_line', 'declining_balance', 'sum_of_years', 'units_of_production']);
            $table->date('period_start');
            $table->date('period_end');
            $table->text('notes')->nullable();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'asset_id']);
            $table->index(['company_id', 'date']);
            $table->index(['asset_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_depreciations');
    }
};
