<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_analytics', function (Blueprint $table) {
            $table->id();
            $table->string('analysis_code')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('analysis_type', [
                'sales_analysis',
                'customer_analysis',
                'product_analysis',
                'market_analysis',
                'performance_analysis',
                'trend_analysis',
                'forecasting',
                'custom'
            ]);
            $table->enum('data_source', [
                'sales_orders',
                'customers',
                'products',
                'inventory',
                'financial_reports',
                'external_api',
                'custom'
            ]);
            $table->date('analysis_date');
            $table->date('data_start_date');
            $table->date('data_end_date');
            $table->json('key_metrics')->nullable(); // Store key performance indicators
            $table->json('insights')->nullable(); // Store analysis insights
            $table->json('recommendations')->nullable(); // Store actionable recommendations
            $table->json('visualization_data')->nullable(); // Store chart/graph data
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_analytics');
    }
};
