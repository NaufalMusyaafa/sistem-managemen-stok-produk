<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'sku',
        'name',
        'unit',
    ];

    /**
     * Get the warehouse product entries for this product.
     */
    public function warehouseProducts(): HasMany
    {
        return $this->hasMany(WarehouseProduct::class);
    }

    /**
     * The warehouses that stock this product.
     */
    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class, 'warehouse_products')
            ->withPivot([
                'current_stock',
                'status',
                'avg_daily_usage',
                'lead_time',
                'safety_stock',
                'reorder_point',
            ])
            ->withTimestamps();
    }
}
