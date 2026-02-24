<?php

namespace App\Livewire;

use App\Models\Warehouse;
use App\Models\WarehouseProduct;
use App\Services\InventoryService;
use Livewire\Component;

class WarehouseStockDetail extends Component
{
    public int $warehouseId;
    public string $search = '';

    /**
     * Warehouse model instance.
     */
    public ?Warehouse $warehouse = null;

    /**
     * Array of stock items for this warehouse.
     * Format: [id => ['current_stock' => int, 'rop' => int, ...]]
     */
    public array $stockItems = [];

    public function mount(int $id): void
    {
        $this->warehouseId = $id;
        $this->warehouse = Warehouse::findOrFail($id);
        $this->loadProducts();
    }

    /**
     * Load all products for this warehouse.
     */
    public function loadProducts(): void
    {
        $items = WarehouseProduct::withoutGlobalScopes()
            ->with('product')
            ->where('warehouse_id', $this->warehouseId)
            ->get();

        $inventoryService = app(InventoryService::class);

        foreach ($items as $item) {
            $rop = $inventoryService->calculateROP(
                (float) $item->avg_daily_usage,
                (int) $item->lead_time,
                (int) $item->safety_stock
            );

            $this->stockItems[$item->id] = [
                'current_stock' => $item->current_stock,
                'rop'           => $rop,
                'status'        => $item->status,
                'product_name'  => $item->product->name,
                'product_sku'   => $item->product->sku,
                'product_unit'  => $item->product->unit,
            ];
        }
    }

    /**
     * Get filtered items based on search.
     */
    public function getFilteredItemsProperty(): array
    {
        if (empty($this->search)) {
            return $this->stockItems;
        }

        $search = strtolower($this->search);
        return array_filter($this->stockItems, function ($item) use ($search) {
            return str_contains(strtolower($item['product_name']), $search)
                || str_contains(strtolower($item['product_sku']), $search);
        });
    }

    public function render()
    {
        return view('livewire.warehouse-stock-detail', [
            'filteredItems' => $this->filteredItems,
        ])->layout('layouts.app');
    }
}
