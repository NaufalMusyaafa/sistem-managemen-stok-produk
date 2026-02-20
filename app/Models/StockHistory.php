<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockHistory extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'warehouse_product_id',
        'user_id',
        'previous_stock',
        'current_stock',
        'difference',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'previous_stock' => 'integer',
            'current_stock' => 'integer',
            'difference' => 'integer',
        ];
    }

    /**
     * Get the warehouse product associated with this history entry.
     */
    public function warehouseProduct(): BelongsTo
    {
        return $this->belongsTo(WarehouseProduct::class);
    }

    /**
     * Get the user who made this stock change.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
