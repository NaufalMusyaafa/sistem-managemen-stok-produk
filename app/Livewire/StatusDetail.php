<?php

namespace App\Livewire;

use App\Models\Warehouse;
use App\Models\WarehouseProduct;
use Livewire\Component;
use Livewire\WithPagination;

class StatusDetail extends Component
{
    use WithPagination;

    public string $type = '';
    public string $search = '';
    public string $filterWarehouse = '';
    public bool $isRent = false;

    public function mount(string $type): void
    {
        if (! in_array($type, ['normal', 'low_stock', 'on_order', 'total_low'])) {
            abort(404);
        }
        $this->type = $type;
        $this->isRent = auth()->user()?->role === 'rent';
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterWarehouse(): void
    {
        $this->resetPage();
    }

    public function getTitle(): string
    {
        return match ($this->type) {
            'normal'    => 'Stok Normal',
            'low_stock' => 'Stok Rendah',
            'on_order'  => 'Dalam Pesanan',
            'total_low' => 'Total Stok Rendah',
            default     => 'Status Detail',
        };
    }

    public function render()
    {
        $query = WarehouseProduct::withoutGlobalScopes()
            ->with(['product', 'warehouse']);

        // Filter by status type
        if ($this->type === 'total_low') {
            $query->whereIn('status', ['low_stock', 'on_order']);
        } else {
            $query->where('status', $this->type);
        }

        // Search by product name or SKU
        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('product', function ($pq) {
                    $pq->where('name', 'like', "%{$this->search}%")
                       ->orWhere('sku', 'like', "%{$this->search}%");
                })
                ->orWhereHas('warehouse', function ($wq) {
                    $wq->where('name', 'like', "%{$this->search}%");
                });
            });
        }

        // Filter by warehouse
        if ($this->filterWarehouse) {
            $query->where('warehouse_id', $this->filterWarehouse);
        }

        $items = $query->orderBy('updated_at', 'desc')->paginate(15);
        $warehouses = Warehouse::orderBy('name')->get();

        $showOrderBtn = $this->isRent && in_array($this->type, ['low_stock', 'total_low']);

        return view('livewire.status-detail', [
            'items'         => $items,
            'warehouses'    => $warehouses,
            'title'         => $this->getTitle(),
            'isTotalLow'    => $this->type === 'total_low',
            'showOrderBtn'  => $showOrderBtn,
        ])->layout('layouts.app');
    }
}
