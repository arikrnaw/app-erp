<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('schedule_code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('report_type', ['financial_report', 'business_analytics']);
            $table->string('report_template'); // Template or configuration for the report
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly']);
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])->nullable();
            $table->integer('day_of_month')->nullable(); // For monthly reports
            $table->time('delivery_time')->default('09:00:00');
            $table->enum('delivery_method', ['email', 'dashboard', 'pdf', 'excel', 'api']);
            $table->json('recipients')->nullable(); // Email addresses or user IDs
            $table->json('parameters')->nullable(); // Report parameters and filters
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_generated_at')->nullable();
            $table->timestamp('next_generation_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_schedules');
    }
};
