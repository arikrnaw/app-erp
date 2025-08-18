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
        Schema::create('production_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('work_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('production_tracking_id')->nullable()->constrained('production_tracking')->onDelete('set null');
            $table->enum('cost_type', ['material', 'labor', 'overhead', 'machine', 'other'])->default('other');
            $table->string('cost_category')->nullable();
            $table->string('description');
            $table->decimal('quantity', 10, 4)->default(1);
            $table->string('unit')->nullable();
            $table->decimal('unit_cost', 15, 2);
            $table->decimal('total_cost', 15, 2);
            $table->date('cost_date');
            $table->foreignId('recorded_by')->constrained('users')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['work_order_id', 'cost_type']);
            $table->index(['cost_date']);
            $table->index(['cost_type', 'cost_category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_costs');
    }
};
