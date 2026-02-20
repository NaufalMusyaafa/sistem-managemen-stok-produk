<?php

namespace App\Services;

class InventoryService
{
    /**
     * Calculate the Reorder Point (ROP).
     *
     * Formula: ROP = (Average Daily Usage × Lead Time) + Safety Stock
     *
     * @param  float  $avgUsage     Average daily usage rate
     * @param  int    $leadTime     Lead time in days for replenishment
     * @param  int    $safetyStock  Minimum safety stock buffer
     * @return int    The calculated reorder point, rounded up
     */
    public function calculateROP(float $avgUsage, int $leadTime, int $safetyStock): int
    {
        return (int) ceil(($avgUsage * $leadTime) + $safetyStock);
    }

    /**
     * Determine the stock status based on current stock, ROP, and order state.
     *
     * Logic:
     *  - If an order has already been placed → 'on_order'
     *  - If stock is at or below the ROP    → 'low_stock'
     *  - Otherwise                          → 'normal'
     *
     * @param  int   $stock      Current stock level
     * @param  int   $rop        Calculated reorder point
     * @param  bool  $isOrdered  Whether a procurement order has been placed
     * @return string One of: 'normal', 'low_stock', 'on_order'
     */
    public function checkStatus(int $stock, int $rop, bool $isOrdered = false): string
    {
        if ($isOrdered) {
            return 'on_order';
        }

        if ($stock <= $rop) {
            return 'low_stock';
        }

        return 'normal';
    }
}
