<?php

namespace App\Livewire;

use App\Models\Procurement;
use App\Models\WarehouseProduct;
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

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    /**
     * Mark a procurement as received (completed).
     * Also updates the warehouse_product status.
     */
    public function markReceived(int $id): void
    {
        $procurement = Procurement::with('warehouseProduct')->findOrFail($id);

        // Only manager or admin_uid can change status
        $user = Auth::user();
        if (! in_array($user->role, ['manager', 'admin_uid'])) {
            abort(403);
        }

        $procurement->update(['status' => 'received']);

        // Update warehouse product status
        $wp = $procurement->warehouseProduct;
        if ($wp) {
            $inventoryService = app(InventoryService::class);
            $rop = $inventoryService->calculateROP(
                (float) $wp->avg_daily_usage,
                (int) $wp->lead_time,
                (int) $wp->safety_stock
            );

            // Check if there are other active procurements for this item
            $otherActive = Procurement::where('warehouse_product_id', $wp->id)
                ->whereIn('status', ['pending', 'approved', 'ordered'])
                ->where('id', '!=', $id)
                ->exists();

            $newStatus = $inventoryService->checkStatus($wp->current_stock, $rop, $otherActive);
            $wp->update(['status' => $newStatus]);
        }

        $this->successMessage = 'Pesanan ditandai sebagai diterima.';
    }

    /**
     * Cancel a procurement.
     * Also re-evaluates warehouse_product status.
     */
    public function cancelOrder(int $id): void
    {
        $procurement = Procurement::with('warehouseProduct')->findOrFail($id);

        $user = Auth::user();
        if (! in_array($user->role, ['manager', 'admin_uid'])) {
            abort(403);
        }

        $procurement->update(['status' => 'cancelled']);

        // Re-evaluate warehouse product status
        $wp = $procurement->warehouseProduct;
        if ($wp) {
            $inventoryService = app(InventoryService::class);
            $rop = $inventoryService->calculateROP(
                (float) $wp->avg_daily_usage,
                (int) $wp->lead_time,
                (int) $wp->safety_stock
            );

            $otherActive = Procurement::where('warehouse_product_id', $wp->id)
                ->whereIn('status', ['pending', 'approved', 'ordered'])
                ->where('id', '!=', $id)
                ->exists();

            $newStatus = $inventoryService->checkStatus($wp->current_stock, $rop, $otherActive);
            $wp->update(['status' => $newStatus]);
        }

        $this->successMessage = 'Pesanan berhasil dibatalkan.';
    }

    public function render()
    {
        $user = Auth::user();

        $query = Procurement::with(['warehouseProduct.product', 'warehouseProduct.warehouse', 'user'])
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->search, function ($q) {
                $q->where(function ($q2) {
                    $q2->where('vendor_name', 'like', "%{$this->search}%")
                       ->orWhereHas('warehouseProduct.product', fn ($pq) =>
                           $pq->where('name', 'like', "%{$this->search}%")
                       )
                       ->orWhereHas('warehouseProduct.warehouse', fn ($wq) =>
                           $wq->where('name', 'like', "%{$this->search}%")
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
            'orders' => $orders,
            'canManage' => in_array($user->role, ['manager', 'admin_uid']),
        ])->layout('layouts.app');
    }
}
