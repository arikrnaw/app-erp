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
        Schema::create('project_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('resource_type'); // human, equipment, material, software
            $table->string('resource_name');
            $table->text('description')->nullable();
            $table->enum('role', ['developer', 'designer', 'tester', 'analyst', 'manager', 'consultant', 'other'])->nullable();
            $table->decimal('hourly_rate', 10, 2)->default(0);
            $table->integer('allocated_hours')->default(0);
            $table->integer('actual_hours')->default(0);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('availability', ['full_time', 'part_time', 'on_demand'])->default('full_time');
            $table->enum('status', ['available', 'allocated', 'unavailable', 'overallocated'])->default('available');
            $table->decimal('utilization_percentage', 5, 2)->default(0);
            $table->json('skills')->nullable();
            $table->json('certifications')->nullable();
            $table->json('custom_fields')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_resources');
    }
};
