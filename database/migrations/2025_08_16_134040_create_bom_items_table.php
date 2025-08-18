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
        Schema::create('bom_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_of_material_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); // Component/raw material
            $table->string('item_name');
            $table->text('description')->nullable();
            $table->decimal('quantity_required', 10, 4);
            $table->string('unit');
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->integer('sequence')->default(0); // For ordering items
            $table->boolean('is_critical')->default(false); // Critical component
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['bill_of_material_id', 'sequence']);
            $table->index(['product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bom_items');
    }
};
