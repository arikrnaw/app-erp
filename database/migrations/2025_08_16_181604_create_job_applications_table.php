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
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('job_posting_id')->constrained()->onDelete('cascade');
            $table->string('application_number')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->string('national_id')->nullable();
            
            // Education & Experience
            $table->string('highest_education')->nullable();
            $table->string('institution')->nullable();
            $table->integer('years_of_experience')->default(0);
            $table->text('current_company')->nullable();
            $table->text('current_position')->nullable();
            $table->decimal('current_salary', 15, 2)->nullable();
            $table->decimal('expected_salary', 15, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            
            // Application Details
            $table->text('cover_letter')->nullable();
            $table->text('skills')->nullable();
            $table->text('references')->nullable();
            $table->string('resume_file')->nullable();
            $table->string('portfolio_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->enum('source', ['website', 'job_board', 'referral', 'social_media', 'direct', 'other'])->default('website');
            $table->text('source_details')->nullable();
            
            // Status & Process
            $table->enum('status', ['applied', 'screening', 'interview_scheduled', 'interviewed', 'shortlisted', 'offer_sent', 'hired', 'rejected', 'withdrawn'])->default('applied');
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->date('interview_date')->nullable();
            $table->time('interview_time')->nullable();
            $table->string('interview_location')->nullable();
            $table->text('interview_notes')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
