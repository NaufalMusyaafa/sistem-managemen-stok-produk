<?php

namespace App\Livewire;

use App\Models\WarehouseProduct;
use App\Models\StockHistory;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DailyStockInput extends Component
{
    /**
     * Array of stock inputs keyed by warehouse_product id.
     * Format: [id => ['current_stock' => int, 'difference' => int, 'rop' => int]]
     */
    public array $stockInputs = [];

    /**
     * Flash message after save.
     */
    public string $successMessage = '';

    /**
     * Search filter for product name/SKU.
     */
    public string $search = '';

    public function mount(): void
    {
        $this->loadProducts();
    }

    /**
     * Load products for the authenticated user's warehouse.
     */
    public function loadProducts(): void
    {
        $items = WarehouseProduct::with('product')
            ->get();

        $inventoryService = app(InventoryService::class);

        foreach ($items as $item) {
            $rop = $inventoryService->calculateROP(
                (float) $item->avg_daily_usage,
                (int) $item->lead_time,
                (int) $item->safety_stock
            );

            $this->stockInputs[$item->id] = [
                'current_stock' => $item->current_stock,
                'new_stock'     => $item->current_stock,
                'difference'    => 0,
                'rop'           => $rop,
                'product_name'  => $item->product->name,
                'product_sku'   => $item->product->sku,
                'product_unit'  => $item->product->unit,
            ];
        }
    }

    /**
     * Called when user updates the "new stock" input field.
     * Auto-calculates the difference.
     */
    public function updatedStockInputs($value, $key): void
    {
        // $key format: "123.new_stock"
        $parts = explode('.', $key);
        if (count($parts) === 2 && $parts[1] === 'new_stock') {
            $id = $parts[0];
            if (isset($this->stockInputs[$id])) {
                $newStock = (int) ($this->stockInputs[$id]['new_stock'] ?? 0);
                $oldStock = (int) ($this->stockInputs[$id]['current_stock'] ?? 0);
                $this->stockInputs[$id]['difference'] = $newStock - $oldStock;
            }
        }
    }

    /**
     * Save all stock changes to DB and create history records.
     */
    public function saveAll(): void
    {
        $user = Auth::user();
        $inventoryService = app(InventoryService::class);
        $updatedCount = 0;

        DB::transaction(function () use ($user, $inventoryService, &$updatedCount) {
            foreach ($this->stockInputs as $id => $input) {
                $newStock = (int) ($input['new_stock'] ?? 0);
                $oldStock = (int) ($input['current_stock'] ?? 0);

                // Skip if no change
                if ($newStock === $oldStock) {
                    continue;
                }

                $warehouseProduct = WarehouseProduct::withoutGlobalScopes()->find($id);
                if (! $warehouseProduct) {
                    continue;
                }

                // Check if there's an active procurement (on_order)
                $isOrdered = $warehouseProduct->procurements()
                    ->whereIn('status', ['pending', 'approved', 'ordered'])
                    ->exists();

                // Calculate new status
                $rop = $inventoryService->calculateROP(
                    (float) $warehouseProduct->avg_daily_usage,
                    (int) $warehouseProduct->lead_time,
                    (int) $warehouseProduct->safety_stock
                );

                $newStatus = $inventoryService->checkStatus($newStock, $rop, $isOrdered);

                // Update warehouse product
                $warehouseProduct->update([
                    'current_stock' => $newStock,
                    'status'        => $newStatus,
                    'reorder_point' => $rop,
                ]);

                // Create stock history record
                StockHistory::create([
                    'warehouse_product_id' => $id,
                    'user_id'              => $user->id,
                    'previous_stock'       => $oldStock,
                    'current_stock'        => $newStock,
                    'difference'           => $newStock - $oldStock,
                ]);

                // Update local state
                $this->stockInputs[$id]['current_stock'] = $newStock;
                $this->stockInputs[$id]['difference'] = 0;

                $updatedCount++;
            }
        });

        $this->successMessage = $updatedCount > 0
            ? "Berhasil menyimpan {$updatedCount} perubahan stok."
            : "Tidak ada perubahan stok untuk disimpan.";

        $this->dispatch('stock-saved');
    }

    /**
     * Get filtered items based on search.
     */
    public function getFilteredItemsProperty(): array
    {
        if (empty($this->search)) {
            return $this->stockInputs;
        }

        $search = strtolower($this->search);
        return array_filter($this->stockInputs, function ($item) use ($search) {
            return str_contains(strtolower($item['product_name']), $search)
                || str_contains(strtolower($item['product_sku']), $search);
        });
    }

    public function render()
    {
        return view('livewire.daily-stock-input', [
            'filteredItems' => $this->filteredItems,
        ])->layout('layouts.app');
    }
}
