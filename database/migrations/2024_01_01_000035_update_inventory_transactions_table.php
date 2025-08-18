<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            // Add warehouse and location support
            $table->foreignId('warehouse_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_location_id')->nullable()->constrained()->onDelete('cascade');
            
            // Add lot and serial support
            $table->foreignId('product_lot_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('serial_numbers')->nullable(); // JSON array of serial numbers
            
            // Add reorder point tracking
            $table->boolean('triggers_reorder')->default(false);
        });

        // Update enum values using raw SQL for PostgreSQL compatibility
        DB::statement("ALTER TABLE inventory_transactions DROP CONSTRAINT IF EXISTS inventory_transactions_type_check");
        DB::statement("ALTER TABLE inventory_transactions ADD CONSTRAINT inventory_transactions_type_check CHECK (type IN ('in', 'out', 'adjustment', 'transfer', 'return', 'damage'))");
    }

    public function down(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropForeign(['warehouse_location_id']);
            $table->dropForeign(['product_lot_id']);
            $table->dropColumn(['warehouse_id', 'warehouse_location_id', 'product_lot_id', 'serial_numbers', 'triggers_reorder']);
        });

        // Restore original enum values
        DB::statement("ALTER TABLE inventory_transactions DROP CONSTRAINT IF EXISTS inventory_transactions_type_check");
        DB::statement("ALTER TABLE inventory_transactions ADD CONSTRAINT inventory_transactions_type_check CHECK (type IN ('in', 'out', 'adjustment'))");
    }
};
