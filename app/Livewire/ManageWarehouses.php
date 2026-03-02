<?php

namespace App\Livewire;

use App\Models\Warehouse;
use Livewire\Component;

class ManageWarehouses extends Component
{
    public string $search = '';

    public ?int $editingId = null;
    public string $name = '';
    public string $location = '';

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $deletingId = null;

    public string $successMessage = '';

    protected function rules(): array
    {
        return [
            'name'     => 'required|string|max:255',
            'location' => 'required|string|max:255',
        ];
    }

    protected $messages = [
        'name.required'     => 'Nama gudang wajib diisi.',
        'location.required' => 'Lokasi wajib diisi.',
    ];

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $warehouse = Warehouse::findOrFail($id);
        $this->editingId = $warehouse->id;
        $this->name = $warehouse->name;
        $this->location = $warehouse->location;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            Warehouse::findOrFail($this->editingId)->update([
                'name'     => $this->name,
                'location' => $this->location,
            ]);
            $this->successMessage = 'Gudang berhasil diperbarui.';
        } else {
            Warehouse::create([
                'name'     => $this->name,
                'location' => $this->location,
            ]);
            $this->successMessage = 'Gudang berhasil ditambahkan.';
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
            $warehouse = Warehouse::findOrFail($this->deletingId);
            // Check if warehouse has products or users
            if ($warehouse->warehouseProducts()->count() > 0) {
                $this->successMessage = '⚠️ Gudang tidak bisa dihapus karena masih memiliki produk terdaftar.';
                $this->showDeleteModal = false;
                $this->deletingId = null;
                return;
            }
            $warehouse->delete();
            $this->successMessage = 'Gudang berhasil dihapus.';
        }
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->location = '';
        $this->resetValidation();
    }

    public function getFilteredWarehousesProperty()
    {
        return Warehouse::query()
            ->withCount(['warehouseProducts', 'users'])
            ->when($this->search, function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('location', 'like', "%{$this->search}%");
            })
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.manage-warehouses', [
            'warehouses' => $this->filteredWarehouses,
        ])->layout('layouts.app');
    }
}
