<?php

namespace App\Livewire;

use App\Models\Procurement;
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

    /**
     * Checkbox state per item for confirming unchanged stock.
     * Format: [warehouse_product_id => true/false]
     */
    public array $confirmed = [];

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

            // Get the latest stock history date for this item
            $lastHistory = StockHistory::where('warehouse_product_id', $item->id)
                ->latest('created_at')
                ->first();

            $this->stockInputs[$item->id] = [
                'current_stock' => $item->current_stock,
                'new_stock'     => $item->current_stock,
                'difference'    => 0,
                'rop'           => $rop,
                'product_name'  => $item->product->name,
                'product_sku'   => $item->product->sku,
                'product_unit'  => $item->product->unit,
                'last_updated'  => $lastHistory ? $lastHistory->created_at->format('Y-m-d') : null,
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
                $isConfirmed = !empty($this->confirmed[$id]);

                // Skip if no change AND not confirmed
                if ($newStock === $oldStock && !$isConfirmed) {
                    continue;
                }

                $warehouseProduct = WarehouseProduct::withoutGlobalScopes()->find($id);
                if (! $warehouseProduct) {
                    continue;
                }

                // Only update stock & status if there's an actual change
                if ($newStock !== $oldStock) {
                    // Check if there's an active procurement (on_order)
                    $activeProcurements = $warehouseProduct->procurements()
                        ->whereIn('status', ['pending', 'approved', 'ordered'])
                        ->get();
                    $isOrdered = $activeProcurements->isNotEmpty();

                    // Calculate new status
                    $rop = $inventoryService->calculateROP(
                        (float) $warehouseProduct->avg_daily_usage,
                        (int) $warehouseProduct->lead_time,
                        (int) $warehouseProduct->safety_stock
                    );

                    // Auto-complete procurement if stock rises above ROP
                    if ($isOrdered && $newStock > $rop) {
                        foreach ($activeProcurements as $procurement) {
                            $procurement->update(['status' => 'received']);
                        }
                        $isOrdered = false;
                    }

                    $newStatus = $inventoryService->checkStatus($newStock, $rop, $isOrdered);

                    // Update warehouse product
                    $warehouseProduct->update([
                        'current_stock' => $newStock,
                        'status'        => $newStatus,
                        'reorder_point' => $rop,
                    ]);
                }

                // Create stock history record (for both changed and confirmed items)
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
                $this->stockInputs[$id]['last_updated'] = now()->format('Y-m-d');

                $updatedCount++;
            }
        });

        // Reset all checkboxes
        $this->confirmed = [];

        $this->successMessage = $updatedCount > 0
            ? "Berhasil menyimpan {$updatedCount} item."
            : "Tidak ada perubahan stok atau konfirmasi untuk disimpan.";

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
