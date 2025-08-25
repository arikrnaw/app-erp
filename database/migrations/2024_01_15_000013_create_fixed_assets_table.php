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
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('tag_number')->unique();
            $table->foreignId('category_id')->constrained('asset_categories')->onDelete('restrict');
            $table->string('location')->nullable();
            $table->date('purchase_date');
            $table->decimal('purchase_value', 15, 2);
            $table->decimal('current_value', 15, 2)->default(0);
            $table->enum('depreciation_method', ['straight_line', 'declining_balance', 'sum_of_years', 'units_of_production'])->default('straight_line');
            $table->integer('useful_life_years')->default(0);
            $table->decimal('salvage_value', 15, 2)->default(0);
            $table->decimal('accumulated_depreciation', 15, 2)->default(0);
            $table->enum('status', ['active', 'maintenance', 'disposed', 'retired'])->default('active');
            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->text('insurance_info')->nullable();
            $table->text('maintenance_schedule')->nullable();
            $table->date('last_maintenance_date')->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->date('disposal_date')->nullable();
            $table->decimal('disposal_value', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'category_id']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'purchase_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fixed_assets');
    }
};
