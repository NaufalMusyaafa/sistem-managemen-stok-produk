<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Warehouse extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'location',
    ];

    /**
     * Get the users assigned to this warehouse.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the warehouse product entries for this warehouse.
     */
    public function warehouseProducts(): HasMany
    {
        return $this->hasMany(WarehouseProduct::class);
    }

    /**
     * The products that belong to this warehouse.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'warehouse_products')
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
