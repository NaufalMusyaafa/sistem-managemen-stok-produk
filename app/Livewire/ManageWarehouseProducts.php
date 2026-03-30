<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseProduct;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ManageWarehouseProducts extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterWarehouse = '';
    public bool $isAdminUp3 = false;

    public function mount(): void
    {
        $user = Auth::user();
        if ($user->role === 'admin_up3' && $user->warehouse_id) {
            $this->isAdminUp3 = true;
            $this->filterWarehouse = (string) $user->warehouse_id;
        }
    }

    // Form fields
    public ?int $editingId = null;
    public string $warehouse_id = '';
    public string $product_name = '';
    public string $product_sku = '';
    public string $product_unit = '';
    public int $current_stock = 0;
    public string $rop_mode = 'auto';
    public float $avg_daily_usage = 1.0;
    public int $lead_time = 7;
    public int $safety_stock = 10;
    public int $manual_rop = 0;

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $deletingId = null;

    public string $successMessage = '';

    protected function rules(): array
    {
        $rules = [
            'warehouse_id'    => 'required|exists:warehouses,id',
            'product_name'    => 'required|string|max:255',
            'current_stock'   => 'required|integer|min:0',
            'rop_mode'        => 'required|in:auto,manual',
        ];

        if ($this->rop_mode === 'manual') {
            $rules['manual_rop'] = 'required|integer|min:1';
        } else {
            $rules['avg_daily_usage'] = 'required|numeric|min:0.01';
            $rules['lead_time']       = 'required|integer|min:1';
            $rules['safety_stock']    = 'required|integer|min:0';
        }

        // SKU and unit required only for new items (not editing)
        if (! $this->editingId) {
            $rules['product_sku'] = 'required|string|max:50';
            $rules['product_unit'] = 'required|string|max:50';
        }

        return $rules;
    }

    protected $messages = [
        'warehouse_id.required' => 'Gudang wajib dipilih.',
        'product_name.required' => 'Nama produk wajib diisi.',
        'product_sku.required'  => 'SKU wajib diisi untuk produk baru.',
        'product_unit.required' => 'Satuan wajib diisi untuk produk baru.',
        'current_stock.min'     => 'Stok tidak boleh negatif.',
        'avg_daily_usage.min'   => 'Rata-rata penggunaan harian minimal 0.01.',
        'lead_time.min'         => 'Lead time minimal 1 hari.',
    ];

    public function openCreate(): void
    {
        $this->resetForm();
        // Auto-set warehouse for admin_up3
        if ($this->isAdminUp3) {
            $this->warehouse_id = (string) Auth::user()->warehouse_id;
        }
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $item = WarehouseProduct::withoutGlobalScopes()->with('product')->findOrFail($id);

        // Admin UP3 can only edit items in their own warehouse
        if ($this->isAdminUp3 && $item->warehouse_id !== Auth::user()->warehouse_id) {
            abort(403);
        }

        $this->editingId = $item->id;
        $this->warehouse_id = (string) $item->warehouse_id;
        $this->product_name = $item->product->name;
        $this->product_sku = $item->product->sku;
        $this->product_unit = $item->product->unit;
        $this->current_stock = $item->current_stock;
        $this->rop_mode = $item->rop_mode ?? 'auto';
        $this->avg_daily_usage = (float) $item->avg_daily_usage;
        $this->lead_time = $item->lead_time;
        $this->safety_stock = $item->safety_stock;
        $this->manual_rop = $item->reorder_point;
        $this->showModal = true;
    }

    public function save(): void
    {
        // Force warehouse_id for admin_up3
        if ($this->isAdminUp3) {
            $this->warehouse_id = (string) Auth::user()->warehouse_id;
        }

        $this->validate();

        // Find or create product
        if ($this->editingId) {
            // When editing, get the existing product_id from the record
            $existingItem = WarehouseProduct::withoutGlobalScopes()->findOrFail($this->editingId);
            $productId = $existingItem->product_id;
        } else {
            // When creating, find by name or create new
            $product = Product::where('name', $this->product_name)->first();
            if (! $product) {
                $product = Product::create([
                    'name' => $this->product_name,
                    'sku'  => $this->product_sku,
                    'unit' => $this->product_unit,
                ]);
            }
            $productId = $product->id;

            // Check duplicate
            $exists = WarehouseProduct::withoutGlobalScopes()
                ->where('warehouse_id', $this->warehouse_id)
                ->where('product_id', $productId)
                ->exists();
            if ($exists) {
                $this->addError('product_name', 'Produk ini sudah terdaftar di gudang tersebut.');
                return;
            }
        }

        $inventoryService = app(InventoryService::class);

        if ($this->rop_mode === 'manual') {
            $rop = $this->manual_rop;
            $avgDaily = 0;
            $leadTime = 0;
            $safetyStock = 0;
        } else {
            $rop = $inventoryService->calculateROP(
                $this->avg_daily_usage,
                $this->lead_time,
                $this->safety_stock
            );
            $avgDaily = $this->avg_daily_usage;
            $leadTime = $this->lead_time;
            $safetyStock = $this->safety_stock;
        }

        $status = $inventoryService->checkStatus($this->current_stock, $rop);

        $data = [
            'warehouse_id'    => $this->warehouse_id,
            'product_id'      => $productId,
            'current_stock'   => $this->current_stock,
            'avg_daily_usage' => $avgDaily,
            'lead_time'       => $leadTime,
            'safety_stock'    => $safetyStock,
            'reorder_point'   => $rop,
            'rop_mode'        => $this->rop_mode,
            'status'          => $status,
        ];

        if ($this->editingId) {
            WarehouseProduct::withoutGlobalScopes()->findOrFail($this->editingId)->update($data);
            $this->successMessage = "Stok gudang berhasil diperbarui. ROP: {$rop}, Status: {$status}";
        } else {
            WarehouseProduct::create($data);
            $this->successMessage = "Produk berhasil ditambahkan ke gudang. ROP: {$rop}, Status: {$status}";
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            $item = WarehouseProduct::withoutGlobalScopes()->findOrFail($this->deletingId);

            // Admin UP3 can only delete items in their own warehouse
            if ($this->isAdminUp3 && $item->warehouse_id !== Auth::user()->warehouse_id) {
                abort(403);
            }

            $item->delete();
            $this->successMessage = 'Item berhasil dihapus dari gudang.';
        }
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->warehouse_id = '';
        $this->product_name = '';
        $this->product_sku = '';
        $this->product_unit = '';
        $this->current_stock = 0;
        $this->rop_mode = 'auto';
        $this->avg_daily_usage = 1.0;
        $this->lead_time = 7;
        $this->safety_stock = 10;
        $this->manual_rop = 0;
        $this->resetValidation();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterWarehouse(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $items = WarehouseProduct::withoutGlobalScopes()
            ->with(['product', 'warehouse'])
            ->when($this->filterWarehouse, fn ($q) => $q->where('warehouse_id', $this->filterWarehouse))
            ->when($this->search, function ($q) {
                $q->whereHas('product', fn ($pq) =>
                    $pq->where('name', 'like', "%{$this->search}%")
                       ->orWhere('sku', 'like', "%{$this->search}%")
                );
            })
            ->orderBy('warehouse_id')
            ->orderBy('product_id')
            ->paginate(10);

        return view('livewire.manage-warehouse-products', [
            'items'      => $items,
            'warehouses' => Warehouse::orderBy('name')->get(),
            'products'   => Product::orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
