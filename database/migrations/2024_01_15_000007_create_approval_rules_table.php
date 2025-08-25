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
        Schema::create('approval_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workflow_id');
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->string('rule_type', 50); // amount, department, user_role, time_based, custom
            $table->json('rule_conditions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(1);
            $table->boolean('auto_trigger')->default(false);
            $table->string('notification_template', 255)->nullable();
            $table->json('escalation_rules')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['workflow_id', 'is_active']);
            $table->index('rule_type');
            $table->index('priority');
            $table->index('auto_trigger');
            
            // Foreign keys
            $table->foreign('workflow_id')->references('id')->on('approval_workflows')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_rules');
    }
};
