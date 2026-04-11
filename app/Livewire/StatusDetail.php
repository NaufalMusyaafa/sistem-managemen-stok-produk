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
        // Capture to local vars so closures work correctly
        $type            = $this->type;
        $search          = trim($this->search);
        $filterWarehouse = $this->filterWarehouse;

        $query = WarehouseProduct::withoutGlobalScopes()
            ->with([
                'product',
                'warehouse',
                'procurements' => fn ($q) => $q->where('status', 'ordered'),
            ]);

        // Filter by status type
        if ($type === 'total_low') {
            $query->whereIn('status', ['low_stock', 'on_order']);
        } else {
            $query->where('status', $type);
        }

        // Search by product name, SKU, or warehouse name
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('product', function ($pq) use ($search) {
                    $pq->where('name', 'like', "%{$search}%")
                       ->orWhere('sku', 'like', "%{$search}%");
                })
                ->orWhereHas('warehouse', function ($wq) use ($search) {
                    $wq->where('name', 'like', "%{$search}%");
                });
            });
        }

        // Filter by warehouse
        if ($filterWarehouse !== '') {
            $query->where('warehouse_id', (int) $filterWarehouse);
        }

        $items = $query->orderBy('updated_at', 'desc')->paginate(15);
        $warehouses = Warehouse::orderBy('name')->get();

        $showOrderBtn      = $this->isRent && in_array($this->type, ['low_stock', 'total_low']);
        $showOrderedQty    = in_array($type, ['on_order', 'total_low']);

        return view('livewire.status-detail', [
            'items'          => $items,
            'warehouses'     => $warehouses,
            'title'          => $this->getTitle(),
            'isTotalLow'     => $this->type === 'total_low',
            'showOrderBtn'   => $showOrderBtn,
            'showOrderedQty' => $showOrderedQty,
            'type'           => $type,
        ])->layout('layouts.app');
    }
}
