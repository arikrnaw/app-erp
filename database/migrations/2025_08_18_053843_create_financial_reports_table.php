<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_code')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('report_type', [
                'income_statement',
                'balance_sheet', 
                'cash_flow',
                'profit_loss',
                'revenue_analysis',
                'expense_analysis',
                'budget_variance',
                'custom'
            ]);
            $table->enum('period_type', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly']);
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_revenue', 15, 2)->default(0);
            $table->decimal('total_expenses', 15, 2)->default(0);
            $table->decimal('net_profit', 15, 2)->default(0);
            $table->decimal('gross_margin', 5, 2)->default(0); // percentage
            $table->decimal('operating_margin', 5, 2)->default(0); // percentage
            $table->json('financial_metrics')->nullable(); // Store additional metrics
            $table->json('chart_data')->nullable(); // Store chart configurations
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_reports');
    }
};
