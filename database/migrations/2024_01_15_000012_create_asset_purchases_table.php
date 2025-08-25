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
        Schema::create('asset_purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('supplier_id');
            $table->string('asset_name', 255);
            $table->string('asset_code', 100)->unique();
            $table->date('purchase_date');
            $table->text('description');
            $table->decimal('purchase_cost', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->decimal('installation_cost', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2);
            $table->integer('expected_life_years');
            $table->enum('depreciation_method', ['straight_line', 'declining_balance', 'sum_of_years']);
            $table->decimal('salvage_value', 15, 2)->default(0);
            $table->integer('warranty_period_months')->nullable();
            $table->boolean('maintenance_required')->default(false);
            $table->string('location', 255)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_capital_expenditure')->default(true);
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'rejected', 'cancelled'])->default('draft');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['category_id', 'status']);
            $table->index(['department_id', 'status']);
            $table->index(['supplier_id', 'status']);
            $table->index(['purchase_date', 'status']);
            $table->index(['total_cost', 'status']);
            $table->index('asset_code');
            $table->index('created_by');
            $table->index('is_capital_expenditure');
            $table->index('depreciation_method');
            
            // Foreign keys
            $table->foreign('category_id')->references('id')->on('asset_categories')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_purchases');
    }
};
