<?php

namespace App\Livewire;

use App\Models\Procurement;
use App\Models\StockHistory;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ManageOrders extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';

    public string $successMessage = '';

    // Detail panel
    public ?int $selectedOrderId = null;
    public ?Procurement $selectedOrder = null;
    public string $newEtaDate = '';

    // Confirmation dialog for above-ROP receipt
    public bool $showStockConfirm = false;
    public ?int $pendingReceiveId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    /**
     * Open detail panel for a procurement.
     */
    public function openDetail(int $id): void
    {
        $this->selectedOrderId = $id;
        $this->selectedOrder = Procurement::with([
            'warehouseProduct.product',
            'warehouseProduct.warehouse',
            'user',
        ])->findOrFail($id);
        $this->newEtaDate = $this->selectedOrder->eta_date?->format('Y-m-d') ?? '';
    }

    /**
     * Close detail panel.
     */
    public function closeDetail(): void
    {
        $this->selectedOrderId = null;
        $this->selectedOrder = null;
        $this->newEtaDate = '';
        $this->successMessage = '';
        $this->showStockConfirm = false;
        $this->pendingReceiveId = null;
    }

    /**
     * Update the ETA date for a procurement.
     */
    public function updateEta(): void
    {
        $this->validate([
            'newEtaDate' => 'required|date',
        ], [
            'newEtaDate.required' => 'Tanggal ETA wajib diisi.',
            'newEtaDate.date'     => 'Format tanggal tidak valid.',
        ]);

        $procurement = Procurement::findOrFail($this->selectedOrderId);
        $procurement->update(['eta_date' => $this->newEtaDate]);

        $this->selectedOrder = $procurement->fresh([
            'warehouseProduct.product',
            'warehouseProduct.warehouse',
            'user',
        ]);
        $this->newEtaDate = $this->selectedOrder->eta_date?->format('Y-m-d') ?? '';
        $this->successMessage = 'Tanggal ETA berhasil diperbarui.';
    }

    /**
     * Resolve the effective ROP for a warehouse product,
     * respecting rop_mode: 'manual' uses stored reorder_point,
     * 'auto' calculates from avg_daily_usage, lead_time, safety_stock.
     */
    private function getRop(\App\Models\WarehouseProduct $wp): int
    {
        if ($wp->rop_mode === 'manual') {
            return (int) $wp->reorder_point;
        }

        return app(InventoryService::class)->calculateROP(
            (float) $wp->avg_daily_usage,
            (int) $wp->lead_time,
            (int) $wp->safety_stock
        );
    }

    /**
     * Initiate the "mark received" flow.
     * If stock is already above ROP, show confirmation dialog first.
     * If stock is still below ROP, mark received immediately and add stock.
     */
    public function confirmReceive(int $id): void
    {
        $user = Auth::user();
        if (! in_array($user->role, ['rent', 'admin_uid'])) {
            abort(403);
        }

        $procurement = Procurement::with('warehouseProduct')->findOrFail($id);

        if ($procurement->status !== 'ordered') {
            $this->successMessage = 'Pesanan ini tidak dapat ditandai sebagai diterima.';
            return;
        }

        $wp  = $procurement->warehouseProduct;
        $rop = $this->getRop($wp);

        if ($wp->current_stock > $rop) {
            // Stock already above ROP — ask user whether to add stock anyway
            $this->pendingReceiveId = $id;
            $this->showStockConfirm = true;
        } else {
            // Stock still below ROP — directly receive and add stock
            $this->doMarkReceived($id, addStock: true);
        }
    }

    /**
     * Called after confirmation dialog: user chooses whether to add stock.
     */
    public function receiveWithStockChoice(bool $addStock): void
    {
        $this->showStockConfirm = false;
        if ($this->pendingReceiveId) {
            $this->doMarkReceived($this->pendingReceiveId, $addStock);
        }
        $this->pendingReceiveId = null;
    }

    /**
     * Core logic: mark procurement as received, optionally add ordered_quantity to stock.
     */
    private function doMarkReceived(int $id, bool $addStock): void
    {
        $procurement = Procurement::with('warehouseProduct')->findOrFail($id);
        $procurement->update(['status' => 'received']);

        $wp = $procurement->warehouseProduct;
        if ($wp) {
            if ($addStock && $procurement->ordered_quantity > 0) {
                $previousStock = $wp->current_stock;
                $newStock      = $previousStock + $procurement->ordered_quantity;

                // Fix 2: use update() instead of save() to avoid full-model side effects
                $wp->update(['current_stock' => $newStock]);

                // Record stock history
                StockHistory::create([
                    'warehouse_product_id' => $wp->id,
                    'user_id'              => Auth::id(),
                    'previous_stock'       => $previousStock,
                    'current_stock'        => $newStock,
                    'difference'           => $procurement->ordered_quantity,
                ]);

                // Refresh local attribute after update
                $wp->current_stock = $newStock;
            }

            // Fix 1: Re-evaluate status using getRop() which respects rop_mode
            $rop = $this->getRop($wp);

            $otherActive = Procurement::where('warehouse_product_id', $wp->id)
                ->where('status', 'ordered')
                ->where('id', '!=', $id)
                ->exists();

            $wp->update(['status' => app(InventoryService::class)->checkStatus($wp->current_stock, $rop, $otherActive)]);
        }

        // Refresh detail panel if open
        if ($this->selectedOrderId === $id) {
            $this->selectedOrder = $procurement->fresh([
                'warehouseProduct.product',
                'warehouseProduct.warehouse',
                'user',
            ]);
        }

        $this->successMessage = $addStock
            ? "Pesanan diterima. Stok ditambah {$procurement->ordered_quantity} unit."
            : 'Pesanan ditandai sebagai diterima (stok tidak berubah).';
    }

    /**
     * Cancel a procurement.
     * Also re-evaluates warehouse_product status.
     */
    public function cancelOrder(int $id): void
    {
        $procurement = Procurement::with('warehouseProduct')->findOrFail($id);

        $user = Auth::user();
        if (! in_array($user->role, ['rent', 'admin_uid'])) {
            abort(403);
        }

        if ($procurement->status !== 'ordered') {
            $this->successMessage = 'Pesanan ini tidak dapat dibatalkan.';
            return;
        }

        $procurement->update(['status' => 'canceled']);

        // Re-evaluate warehouse product status
        $wp = $procurement->warehouseProduct;
        if ($wp) {
            // Fix 1: use getRop() to respect rop_mode
            $rop = $this->getRop($wp);

            $otherActive = Procurement::where('warehouse_product_id', $wp->id)
                ->where('status', 'ordered')
                ->where('id', '!=', $id)
                ->exists();

            $newStatus = app(InventoryService::class)->checkStatus($wp->current_stock, $rop, $otherActive);
            $wp->update(['status' => $newStatus]);
        }

        // Refresh detail panel if open
        if ($this->selectedOrderId === $id) {
            $this->selectedOrder = $procurement->fresh([
                'warehouseProduct.product',
                'warehouseProduct.warehouse',
                'user',
            ]);
        }

        $this->successMessage = 'Pesanan berhasil dibatalkan.';
    }

    public function render()
    {
        $user = Auth::user();
        $search = $this->search;

        $query = Procurement::with(['warehouseProduct.product', 'warehouseProduct.warehouse', 'user'])
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('vendor_name', 'like', "%{$search}%")
                       ->orWhereHas('warehouseProduct.product', fn ($pq) =>
                           $pq->where('name', 'like', "%{$search}%")
                       )
                       ->orWhereHas('warehouseProduct.warehouse', fn ($wq) =>
                           $wq->where('name', 'like', "%{$search}%")
                       );
                });
            });

        // Admin UP3 can only see orders for their warehouse
        if ($user->role === 'admin_up3' && $user->warehouse_id) {
            $query->whereHas('warehouseProduct', fn ($q) =>
                $q->where('warehouse_id', $user->warehouse_id)
            );
        }

        $orders = $query->orderByDesc('created_at')->paginate(10);

        return view('livewire.manage-orders', [
            'orders'     => $orders,
            'canManage'  => in_array($user->role, ['rent', 'admin_uid']),
        ])->layout('layouts.app');
    }
}
