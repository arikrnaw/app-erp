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
        Schema::create('project_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('cost_name');
            $table->text('description')->nullable();
            $table->enum('cost_type', ['labor', 'equipment', 'material', 'software', 'travel', 'overhead', 'other'])->default('labor');
            $table->enum('cost_category', ['direct', 'indirect'])->default('direct');
            $table->decimal('estimated_cost', 15, 2)->default(0);
            $table->decimal('actual_cost', 15, 2)->default(0);
            $table->decimal('budgeted_cost', 15, 2)->default(0);
            $table->date('incurred_date');
            $table->enum('status', ['planned', 'incurred', 'approved', 'rejected', 'pending'])->default('planned');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->date('approved_date')->nullable();
            $table->string('invoice_number')->nullable();
            $table->string('vendor')->nullable();
            $table->json('attachments')->nullable();
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
        Schema::dropIfExists('project_costs');
    }
};
