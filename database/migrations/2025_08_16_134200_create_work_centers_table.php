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
        Schema::create('work_centers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('work_center_code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->enum('type', ['machine', 'assembly_line', 'workstation', 'department'])->default('workstation');
            $table->decimal('capacity_per_hour', 8, 2)->default(1);
            $table->decimal('efficiency_rate', 5, 2)->default(100); // Percentage
            $table->decimal('hourly_rate', 10, 2)->default(0);
            $table->decimal('setup_time', 8, 2)->default(0); // Minutes
            $table->decimal('teardown_time', 8, 2)->default(0); // Minutes
            $table->boolean('is_active')->default(true);
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'is_active']);
            $table->index(['type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_centers');
    }
};
