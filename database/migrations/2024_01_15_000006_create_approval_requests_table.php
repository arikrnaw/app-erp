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
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workflow_id');
            $table->unsignedBigInteger('requestor_id');
            $table->unsignedBigInteger('approver_id');
            $table->string('approvable_type', 255);
            $table->unsignedBigInteger('approvable_id');
            $table->decimal('amount', 15, 2);
            $table->text('description');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->date('due_date')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed', 'escalated', 'cancelled'])->default('pending');
            $table->integer('current_level')->default(1);
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('escalated_at')->nullable();
            $table->text('requestor_comments')->nullable();
            $table->text('approver_comments')->nullable();
            $table->text('escalation_reason')->nullable();
            $table->unsignedBigInteger('delegated_to')->nullable();
            $table->timestamp('delegated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['workflow_id', 'status']);
            $table->index(['requestor_id', 'status']);
            $table->index(['approver_id', 'status']);
            $table->index(['approvable_type', 'approvable_id']);
            $table->index(['status', 'priority']);
            $table->index('due_date');
            $table->index('current_level');
            
            // Foreign keys
            $table->foreign('workflow_id')->references('id')->on('approval_workflows')->onDelete('cascade');
            $table->foreign('requestor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approver_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('delegated_to')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_requests');
    }
};
