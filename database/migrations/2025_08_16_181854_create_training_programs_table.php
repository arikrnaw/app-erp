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
        Schema::create('training_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('program_code')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('objectives')->nullable();
            $table->text('curriculum')->nullable();
            $table->enum('training_type', ['internal', 'external', 'online', 'workshop', 'seminar', 'certification', 'degree'])->default('internal');
            $table->enum('category', ['technical', 'soft_skills', 'leadership', 'compliance', 'safety', 'product', 'other'])->default('other');
            $table->string('instructor')->nullable();
            $table->string('institution')->nullable();
            $table->string('location')->nullable();
            $table->integer('duration_hours')->default(0);
            $table->integer('max_participants')->nullable();
            $table->decimal('cost_per_participant', 15, 2)->default(0);
            $table->string('currency', 3)->default('IDR');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'in_progress', 'completed', 'cancelled'])->default('draft');
            $table->boolean('is_mandatory')->default(false);
            $table->boolean('requires_certification')->default(false);
            $table->text('prerequisites')->nullable();
            $table->text('materials')->nullable();
            $table->text('evaluation_criteria')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_programs');
    }
};
