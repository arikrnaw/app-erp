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
        Schema::create('service_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_code')->unique();
            $table->string('title');
            $table->text('description');
            $table->enum('ticket_type', [
                'technical_support',
                'billing_support',
                'product_support',
                'account_support',
                'feature_request',
                'bug_report',
                'general_inquiry',
                'escalation',
                'other'
            ]);
            $table->enum('priority', ['low', 'medium', 'high', 'critical']);
            $table->enum('status', ['open', 'assigned', 'in_progress', 'waiting_customer', 'resolved', 'closed']);
            $table->enum('source', ['phone', 'email', 'chat', 'web_form', 'social_media', 'in_person']);
            
            // Customer Information
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->string('customer_company')->nullable();
            
            // Issue Details
            $table->text('issue_details');
            $table->text('steps_to_reproduce')->nullable();
            $table->text('error_messages')->nullable();
            $table->string('affected_product')->nullable();
            $table->string('affected_version')->nullable();
            $table->text('attachments')->nullable(); // JSON array of file paths
            
            // Assignment and Escalation
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->unsignedBigInteger('escalated_to')->nullable();
            $table->date('escalation_date')->nullable();
            $table->text('escalation_reason')->nullable();
            $table->integer('escalation_level')->default(0);
            
            // Resolution Tracking
            $table->text('resolution_notes')->nullable();
            $table->text('solution_provided')->nullable();
            $table->text('internal_notes')->nullable();
            $table->date('first_response_date')->nullable();
            $table->date('resolution_date')->nullable();
            $table->integer('response_time_hours')->nullable();
            $table->integer('resolution_time_hours')->nullable();
            
            // SLA Tracking
            $table->date('sla_due_date')->nullable();
            $table->boolean('sla_breached')->default(false);
            $table->integer('sla_breach_hours')->nullable();
            
            // Customer Satisfaction
            $table->enum('satisfaction_rating', ['very_dissatisfied', 'dissatisfied', 'neutral', 'satisfied', 'very_satisfied'])->nullable();
            $table->text('customer_feedback')->nullable();
            $table->boolean('customer_responded')->default(false);
            
            // Tags and Categories
            $table->json('tags')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            
            // Metadata
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('company_id');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->foreign('escalated_to')->references('id')->on('users')->onDelete('set null');
            $table->foreign('category_id')->references('id')->on('service_categories')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_tickets');
    }
};
