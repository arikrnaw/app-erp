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
        // Drop the existing check constraint
        DB::statement("ALTER TABLE bills DROP CONSTRAINT bills_status_check");
        
        // Add new check constraint with 'posted' status
        DB::statement("ALTER TABLE bills ADD CONSTRAINT bills_status_check CHECK (status IN ('draft', 'posted', 'received', 'paid', 'overdue', 'cancelled'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the new constraint
        DB::statement("ALTER TABLE bills DROP CONSTRAINT bills_status_check");
        
        // Restore the original constraint
        DB::statement("ALTER TABLE bills ADD CONSTRAINT bills_status_check CHECK (status IN ('draft', 'received', 'paid', 'overdue', 'cancelled'))");
    }
};
