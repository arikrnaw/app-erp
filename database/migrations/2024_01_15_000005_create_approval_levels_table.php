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
        Schema::create('approval_levels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workflow_id');
            $table->integer('level');
            $table->string('approver_role', 100);
            $table->unsignedBigInteger('approver_id')->nullable();
            $table->integer('escalation_hours')->default(24);
            $table->boolean('is_active')->default(true);
            $table->boolean('can_delegate')->default(false);
            $table->boolean('auto_approve_if_same_user')->default(false);
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['workflow_id', 'level']);
            $table->index('approver_id');
            $table->index('approver_role');
            
            // Foreign keys
            $table->foreign('workflow_id')->references('id')->on('approval_workflows')->onDelete('cascade');
            $table->foreign('approver_id')->references('id')->on('users')->onDelete('set null');
            
            // Unique constraint
            $table->unique(['workflow_id', 'level'], 'unique_workflow_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_levels');
    }
};
