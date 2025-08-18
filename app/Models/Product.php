<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'category_id',
        'name',
        'sku',
        'barcode',
        'description',
        'cost_price',
        'selling_price',
        'stock_quantity',
        'min_stock_level',
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
        'last_stock_out_date',
        'unit',
        'image',
        'status',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'min_stock_level' => 'integer',
        'max_stock_level' => 'integer',
        'reorder_point' => 'integer',
        'reorder_quantity' => 'integer',
        'track_lots' => 'boolean',
        'track_serials' => 'boolean',
        'auto_reorder' => 'boolean',
        'average_cost' => 'decimal:2',
        'last_cost' => 'decimal:2',
        'last_stock_in_date' => 'date',
        'last_stock_out_date' => 'date',
        'status' => 'string',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function defaultWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'default_warehouse_id');
    }

    public function defaultLocation(): BelongsTo
    {
        return $this->belongsTo(WarehouseLocation::class, 'default_location_id');
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function salesOrderItems(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function inventoryTransactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function productLots(): HasMany
    {
        return $this->hasMany(ProductLot::class);
    }

    public function productSerials(): HasMany
    {
        return $this->hasMany(ProductSerial::class);
    }

    public function reorderAlerts(): HasMany
    {
        return $this->hasMany(ReorderAlert::class);
    }

    public function getProfitMarginAttribute(): float
    {
        if ($this->cost_price > 0) {
            return (($this->selling_price - $this->cost_price) / $this->cost_price) * 100;
        }
        return 0;
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock_quantity <= $this->min_stock_level;
    }

    public function getNeedsReorderAttribute(): bool
    {
        return $this->stock_quantity <= $this->reorder_point;
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->stock_quantity <= $this->min_stock_level) {
            return 'low';
        } elseif ($this->stock_quantity >= $this->max_stock_level) {
            return 'high';
        } else {
            return 'normal';
        }
    }
}
