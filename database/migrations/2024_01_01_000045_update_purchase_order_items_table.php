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
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->foreignId('purchase_request_item_id')->nullable()->after('purchase_order_id')->constrained()->onDelete('cascade');
            $table->string('item_description')->nullable()->after('product_id');
            $table->string('specifications')->nullable()->after('item_description');
            $table->string('unit')->default('pcs')->after('quantity');
            $table->decimal('discount_percentage', 5, 2)->default(0)->after('unit_price');
            $table->decimal('discount_amount', 12, 2)->default(0)->after('discount_percentage');
            $table->decimal('tax_percentage', 5, 2)->default(0)->after('discount_amount');
            $table->decimal('tax_amount', 12, 2)->default(0)->after('tax_percentage');
            $table->integer('received_quantity')->default(0)->after('total_price');
            $table->integer('returned_quantity')->default(0)->after('received_quantity');
            $table->date('expected_delivery_date')->nullable()->after('returned_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropForeign(['purchase_request_item_id']);
            $table->dropColumn([
                'purchase_request_item_id',
                'item_description',
                'specifications',
                'unit',
                'discount_percentage',
                'discount_amount',
                'tax_percentage',
                'tax_amount',
                'received_quantity',
                'returned_quantity',
                'expected_delivery_date'
            ]);
        });
    }
};
