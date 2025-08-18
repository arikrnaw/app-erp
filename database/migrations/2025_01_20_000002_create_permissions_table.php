<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('module'); // e.g., 'crm', 'projects', 'reports', 'settings'
            $table->string('action'); // e.g., 'view', 'create', 'edit', 'delete'
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->unique(['company_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
