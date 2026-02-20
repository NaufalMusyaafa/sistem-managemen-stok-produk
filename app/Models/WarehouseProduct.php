<?php

namespace App\Models;

use App\Models\Scopes\WarehouseScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehouseProduct extends Model
{
    /**
     * The "booted" method of the model.
     *
     * Registers the WarehouseScope to auto-filter queries
     * for admin_up3 users to their assigned warehouse only.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new WarehouseScope);
    }

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'warehouse_products';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'warehouse_id',
        'product_id',
        'current_stock',
        'status',
        'avg_daily_usage',
        'lead_time',
        'safety_stock',
        'reorder_point',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'avg_daily_usage' => 'decimal:2',
            'current_stock' => 'integer',
            'lead_time' => 'integer',
            'safety_stock' => 'integer',
            'reorder_point' => 'integer',
        ];
    }

    /**
     * Get the warehouse that owns this entry.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the product for this entry.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the procurements for this warehouse product.
     */
    public function procurements(): HasMany
    {
        return $this->hasMany(Procurement::class);
    }

    /**
     * Get the stock history entries for this warehouse product.
     */
    public function stockHistories(): HasMany
    {
        return $this->hasMany(StockHistory::class);
    }
}
