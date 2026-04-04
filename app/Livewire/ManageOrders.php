<?php

namespace App\Livewire;

use App\Models\Procurement;
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
     * Mark a procurement as received.
     * User can mark as received at any time while status is still 'ordered'.
     * Also updates the warehouse_product status accordingly.
     */
    public function markReceived(int $id): void
    {
        $procurement = Procurement::with('warehouseProduct')->findOrFail($id);

        $user = Auth::user();
        if (! in_array($user->role, ['rent', 'admin_uid'])) {
            abort(403);
        }

        if ($procurement->status !== 'ordered') {
            $this->successMessage = 'Pesanan ini tidak dapat ditandai sebagai diterima.';
            return;
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

            // Check if there are OTHER active (ordered) procurements for this item
            $otherActive = Procurement::where('warehouse_product_id', $wp->id)
                ->where('status', 'ordered')
                ->where('id', '!=', $id)
                ->exists();

            $newStatus = $inventoryService->checkStatus($wp->current_stock, $rop, $otherActive);
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
            $inventoryService = app(InventoryService::class);
            $rop = $inventoryService->calculateROP(
                (float) $wp->avg_daily_usage,
                (int) $wp->lead_time,
                (int) $wp->safety_stock
            );

            $otherActive = Procurement::where('warehouse_product_id', $wp->id)
                ->where('status', 'ordered')
                ->where('id', '!=', $id)
                ->exists();

            $newStatus = $inventoryService->checkStatus($wp->current_stock, $rop, $otherActive);
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
            'orders'     => $orders,
            'canManage'  => in_array($user->role, ['rent', 'admin_uid']),
        ])->layout('layouts.app');
    }
}
