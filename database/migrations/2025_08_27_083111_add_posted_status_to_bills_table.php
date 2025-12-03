<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the enum column to include 'posted' status
        DB::statement("ALTER TABLE bills MODIFY COLUMN status ENUM('draft', 'posted', 'received', 'paid', 'overdue', 'cancelled') DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore the original enum values
        DB::statement("ALTER TABLE bills MODIFY COLUMN status ENUM('draft', 'received', 'paid', 'overdue', 'cancelled') DEFAULT 'draft'");
    }
};
