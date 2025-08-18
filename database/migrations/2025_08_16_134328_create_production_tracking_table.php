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
        Schema::create('production_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('work_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('work_center_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('operator_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('operation_type', ['setup', 'production', 'inspection', 'maintenance', 'break'])->default('production');
            $table->timestamp('start_time');
            $table->timestamp('end_time')->nullable();
            $table->decimal('duration_minutes', 8, 2)->default(0);
            $table->decimal('quantity_produced', 10, 4)->default(0);
            $table->decimal('quantity_rejected', 10, 4)->default(0);
            $table->string('rejection_reason')->nullable();
            $table->enum('status', ['in_progress', 'completed', 'paused', 'cancelled'])->default('in_progress');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['work_order_id', 'start_time']);
            $table->index(['work_center_id', 'start_time']);
            $table->index(['operator_id', 'start_time']);
            $table->index(['status', 'start_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_tracking');
    }
};
