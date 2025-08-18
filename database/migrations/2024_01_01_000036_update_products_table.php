<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Add enhanced inventory management fields
            $table->integer('max_stock_level')->default(0)->after('min_stock_level');
            $table->integer('reorder_point')->default(0)->after('max_stock_level');
            $table->integer('reorder_quantity')->default(0)->after('reorder_point');
            $table->boolean('track_lots')->default(false)->after('reorder_quantity');
            $table->boolean('track_serials')->default(false)->after('track_lots');
            $table->boolean('auto_reorder')->default(false)->after('track_serials');
            $table->string('default_warehouse_id')->nullable()->after('auto_reorder');
            $table->string('default_location_id')->nullable()->after('default_warehouse_id');
            $table->decimal('average_cost', 10, 2)->default(0)->after('default_location_id');
            $table->decimal('last_cost', 10, 2)->default(0)->after('average_cost');
            $table->date('last_stock_in_date')->nullable()->after('last_cost');
            $table->date('last_stock_out_date')->nullable()->after('last_stock_in_date');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'max_stock_level',
                'reorder_point',
                'reorder_quantity',
                'track_lots',
                'track_serials',
                'auto_reorder',
                'default_warehouse_id',
                'default_location_id',
                'average_cost',
                'last_cost',
                'last_stock_in_date',
                'last_stock_out_date'
            ]);
        });
    }
};
