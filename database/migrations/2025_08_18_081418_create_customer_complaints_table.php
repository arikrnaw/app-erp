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
        Schema::create('customer_complaints', function (Blueprint $table) {
            $table->id();
            $table->string('complaint_code')->unique();
            $table->string('title');
            $table->text('description');
            $table->enum('complaint_type', [
                'product_issue',
                'service_issue', 
                'billing_issue',
                'delivery_issue',
                'technical_issue',
                'quality_issue',
                'communication_issue',
                'other'
            ]);
            $table->enum('priority', ['low', 'medium', 'high', 'critical']);
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed', 'escalated']);
            $table->enum('source', ['phone', 'email', 'chat', 'social_media', 'in_person', 'website']);
            
            // Customer Information
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->string('customer_address')->nullable();
            
            // Issue Details
            $table->string('product_name')->nullable();
            $table->string('order_number')->nullable();
            $table->date('incident_date');
            $table->text('expected_resolution')->nullable();
            
            // Resolution Tracking
            $table->text('resolution_notes')->nullable();
            $table->text('action_taken')->nullable();
            $table->date('resolution_date')->nullable();
            $table->integer('resolution_time_hours')->nullable();
            $table->enum('satisfaction_rating', ['very_dissatisfied', 'dissatisfied', 'neutral', 'satisfied', 'very_satisfied'])->nullable();
            $table->text('customer_feedback')->nullable();
            
            // Assignment
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('company_id');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_complaints');
    }
};
