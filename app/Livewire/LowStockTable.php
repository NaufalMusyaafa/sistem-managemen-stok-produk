<?php

namespace App\Livewire;

use App\Models\WarehouseProduct;
use Livewire\Component;
use Livewire\WithPagination;

class LowStockTable extends Component
{
    use WithPagination;

    public function render()
    {
        $lowStockItems = WarehouseProduct::withoutGlobalScopes()
            ->with(['product', 'warehouse'])
            ->where('status', 'low_stock')
            ->orderByRaw('(reorder_point - current_stock) DESC')
            ->paginate(10);

        return view('livewire.low-stock-table', [
            'lowStockItems' => $lowStockItems,
        ]);
    }
}
