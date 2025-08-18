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
        Schema::create('project_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['project_manager', 'team_lead', 'developer', 'designer', 'tester', 'analyst', 'consultant', 'stakeholder', 'observer'])->default('developer');
            $table->enum('status', ['active', 'inactive', 'pending'])->default('active');
            $table->date('joined_date');
            $table->date('left_date')->nullable();
            $table->decimal('allocation_percentage', 5, 2)->default(100);
            $table->decimal('hourly_rate', 10, 2)->default(0);
            $table->json('responsibilities')->nullable();
            $table->json('skills')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_teams');
    }
};
