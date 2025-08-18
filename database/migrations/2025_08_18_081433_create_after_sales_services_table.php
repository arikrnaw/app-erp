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
        Schema::create('after_sales_services', function (Blueprint $table) {
            $table->id();
            $table->string('service_code')->unique();
            $table->string('title');
            $table->text('description');
            $table->enum('service_type', [
                'warranty_service',
                'repair_service',
                'maintenance_service',
                'installation_service',
                'training_service',
                'consultation_service',
                'replacement_service',
                'upgrade_service',
                'other'
            ]);
            $table->enum('priority', ['low', 'medium', 'high', 'critical']);
            $table->enum('status', ['pending', 'scheduled', 'in_progress', 'completed', 'cancelled', 'on_hold']);
            
            // Customer Information
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->text('customer_address')->nullable();
            
            // Service Details
            $table->string('product_name')->nullable();
            $table->string('product_model')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('order_number')->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('warranty_expiry')->nullable();
            
            // Service Schedule
            $table->date('requested_date');
            $table->date('scheduled_date')->nullable();
            $table->time('scheduled_time')->nullable();
            $table->text('service_location')->nullable();
            $table->text('special_instructions')->nullable();
            
            // Service Execution
            $table->text('service_notes')->nullable();
            $table->text('work_performed')->nullable();
            $table->text('parts_used')->nullable();
            $table->decimal('labor_cost', 10, 2)->default(0);
            $table->decimal('parts_cost', 10, 2)->default(0);
            $table->decimal('total_cost', 10, 2)->default(0);
            $table->boolean('is_warranty_covered')->default(false);
            
            // Completion
            $table->date('completion_date')->nullable();
            $table->integer('service_duration_hours')->nullable();
            $table->enum('service_quality', ['poor', 'fair', 'good', 'excellent'])->nullable();
            $table->text('customer_signature')->nullable();
            $table->text('technician_signature')->nullable();
            
            // Follow-up
            $table->date('follow_up_date')->nullable();
            $table->text('follow_up_notes')->nullable();
            $table->enum('customer_satisfaction', ['very_dissatisfied', 'dissatisfied', 'neutral', 'satisfied', 'very_satisfied'])->nullable();
            $table->text('customer_feedback')->nullable();
            
            // Assignment
            $table->unsignedBigInteger('assigned_technician')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('company_id');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->foreign('assigned_technician')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('after_sales_services');
    }
};
