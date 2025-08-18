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
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreignId('purchase_request_id')->nullable()->after('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->nullable()->after('supplier_id')->constrained()->onDelete('cascade');
            $table->string('reference_number')->nullable()->after('po_number');
            $table->enum('payment_terms', ['immediate', 'net_15', 'net_30', 'net_45', 'net_60'])->default('net_30')->after('expected_delivery_date');
            $table->string('shipping_address')->nullable()->after('payment_terms');
            $table->string('billing_address')->nullable()->after('shipping_address');
            $table->decimal('shipping_cost', 12, 2)->default(0)->after('discount_amount');
            $table->decimal('handling_cost', 12, 2)->default(0)->after('shipping_cost');
            $table->enum('delivery_method', ['pickup', 'delivery', 'express'])->default('delivery')->after('handling_cost');
            $table->string('carrier')->nullable()->after('delivery_method');
            $table->string('tracking_number')->nullable()->after('carrier');
            $table->foreignId('approved_by')->nullable()->after('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('approval_notes')->nullable()->after('approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['purchase_request_id']);
            $table->dropForeign(['warehouse_id']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'purchase_request_id',
                'warehouse_id',
                'reference_number',
                'payment_terms',
                'shipping_address',
                'billing_address',
                'shipping_cost',
                'handling_cost',
                'delivery_method',
                'carrier',
                'tracking_number',
                'approved_by',
                'approved_at',
                'approval_notes'
            ]);
        });
    }
};
