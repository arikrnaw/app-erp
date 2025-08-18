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
        Schema::create('project_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['planned', 'in_progress', 'completed', 'delayed', 'cancelled'])->default('planned');
            $table->date('planned_date');
            $table->date('actual_date')->nullable();
            $table->decimal('progress_percentage', 5, 2)->default(0);
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->json('deliverables')->nullable();
            $table->json('dependencies')->nullable();
            $table->foreignId('responsible_person')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_milestones');
    }
};
