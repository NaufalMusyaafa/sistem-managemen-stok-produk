<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Procurement extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'warehouse_product_id',
        'user_id',
        'vendor_name',
        'vendor_contact',
        'order_date',
        'eta_date',
        'status',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'eta_date' => 'date',
        ];
    }

    /**
     * Get the warehouse product associated with this procurement.
     */
    public function warehouseProduct(): BelongsTo
    {
        return $this->belongsTo(WarehouseProduct::class);
    }

    /**
     * Get the user (manager) who created this procurement.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
