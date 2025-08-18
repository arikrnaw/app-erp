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
        Schema::create('employee_benefits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('benefit_name');
            $table->text('description')->nullable();
            $table->enum('benefit_type', ['health_insurance', 'life_insurance', 'dental_insurance', 'vision_insurance', 'retirement', 'transportation', 'meal', 'housing', 'education', 'gym', 'other'])->default('other');
            $table->enum('calculation_type', ['fixed_amount', 'percentage', 'per_day', 'per_month', 'per_year'])->default('fixed_amount');
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('percentage', 5, 2)->nullable(); // if calculation_type is percentage
            $table->string('currency', 3)->default('USD');
            $table->date('effective_date');
            $table->date('expiry_date')->nullable();
            $table->enum('frequency', ['one_time', 'monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->boolean('is_taxable')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_benefits');
    }
};
