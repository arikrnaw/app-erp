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
        Schema::create('performance_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('review_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            
            // Review Period
            $table->date('review_start_date');
            $table->date('review_end_date');
            $table->enum('review_type', ['probation', 'monthly', 'quarterly', 'annual', 'project', 'special'])->default('annual');
            $table->enum('status', ['draft', 'in_progress', 'completed', 'approved', 'cancelled'])->default('draft');
            
            // Reviewers
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('second_reviewer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('hr_reviewer_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Self Assessment
            $table->text('self_assessment')->nullable();
            $table->decimal('self_rating', 3, 1)->nullable(); // 1.0 to 5.0
            
            // Manager Assessment
            $table->text('manager_assessment')->nullable();
            $table->decimal('manager_rating', 3, 1)->nullable();
            $table->text('strengths')->nullable();
            $table->text('areas_for_improvement')->nullable();
            $table->text('goals_achieved')->nullable();
            $table->text('goals_not_achieved')->nullable();
            
            // Final Assessment
            $table->decimal('final_rating', 3, 1)->nullable();
            $table->enum('performance_level', ['excellent', 'good', 'satisfactory', 'needs_improvement', 'unsatisfactory'])->nullable();
            $table->text('overall_comments')->nullable();
            $table->text('recommendations')->nullable();
            
            // Actions
            $table->text('action_plan')->nullable();
            $table->text('training_needs')->nullable();
            $table->text('career_development')->nullable();
            $table->decimal('salary_increase_percentage', 5, 2)->nullable();
            $table->decimal('bonus_amount', 15, 2)->nullable();
            
            // Approval
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            
            // Employee Acknowledgment
            $table->boolean('employee_acknowledged')->default(false);
            $table->timestamp('acknowledged_at')->nullable();
            $table->text('employee_comments')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_reviews');
    }
};
