<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;

class ManageProducts extends Component
{
    public string $search = '';

    // Form fields
    public ?int $editingId = null;
    public string $name = '';
    public string $sku = '';
    public string $unit = '';

    // Modal state
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $deletingId = null;

    public string $successMessage = '';

    protected function rules(): array
    {
        $uniqueSku = $this->editingId
            ? 'unique:products,sku,' . $this->editingId
            : 'unique:products,sku';

        return [
            'name' => 'required|string|max:255',
            'sku'  => 'required|string|max:50|' . $uniqueSku,
            'unit' => 'required|string|max:50',
        ];
    }

    protected $messages = [
        'name.required' => 'Nama produk wajib diisi.',
        'sku.required'  => 'SKU wajib diisi.',
        'sku.unique'    => 'SKU sudah digunakan.',
        'unit.required' => 'Satuan wajib diisi.',
    ];

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $product = Product::findOrFail($id);
        $this->editingId = $product->id;
        $this->name = $product->name;
        $this->sku = $product->sku;
        $this->unit = $product->unit;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            $product = Product::findOrFail($this->editingId);
            $product->update([
                'name' => $this->name,
                'sku'  => $this->sku,
                'unit' => $this->unit,
            ]);
            $this->successMessage = 'Produk berhasil diperbarui.';
        } else {
            Product::create([
                'name' => $this->name,
                'sku'  => $this->sku,
                'unit' => $this->unit,
            ]);
            $this->successMessage = 'Produk berhasil ditambahkan.';
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
            Product::findOrFail($this->deletingId)->delete();
            $this->successMessage = 'Produk berhasil dihapus.';
        }
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->sku = '';
        $this->unit = '';
        $this->resetValidation();
    }

    public function getFilteredProductsProperty()
    {
        return Product::query()
            ->when($this->search, function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('sku', 'like', "%{$this->search}%");
            })
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.manage-products', [
            'products' => $this->filteredProducts,
        ])->layout('layouts.app');
    }
}
