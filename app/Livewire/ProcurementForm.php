<?php

namespace App\Livewire;

use App\Models\Procurement;
use App\Models\WarehouseProduct;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ProcurementForm extends Component
{
    /**
     * The warehouse product ID from the URL.
     */
    public int $warehouseProductId;

    /**
     * The WarehouseProduct model instance.
     */
    public ?WarehouseProduct $warehouseProduct = null;

    /**
     * Form fields.
     */
    public string $vendor_name = '';
    public string $vendor_contact = '';
    public string $order_date = '';
    public string $eta_date = '';
    public string $notes = '';

    /**
     * Flash messages.
     */
    public string $successMessage = '';
    public string $errorMessage = '';

    /**
     * Validation rules.
     */
    protected function rules(): array
    {
        return [
            'vendor_name'    => 'required|string|max:255',
            'vendor_contact' => 'nullable|string|max:255',
            'order_date'     => 'required|date|after_or_equal:today',
            'eta_date'       => 'nullable|date|after_or_equal:order_date',
            'notes'          => 'nullable|string|max:1000',
        ];
    }

    /**
     * Custom validation messages.
     */
    protected function messages(): array
    {
        return [
            'vendor_name.required'      => 'Nama vendor wajib diisi.',
            'order_date.required'       => 'Tanggal order wajib diisi.',
            'order_date.after_or_equal' => 'Tanggal order tidak boleh sebelum hari ini.',
            'eta_date.after_or_equal'   => 'Estimasi tiba harus setelah atau sama dengan tanggal order.',
        ];
    }

    public function mount(int $id): void
    {
        $this->warehouseProductId = $id;
        $this->warehouseProduct = WarehouseProduct::withoutGlobalScopes()
            ->with(['product', 'warehouse'])
            ->findOrFail($id);

        // Pre-fill order date to today
        $this->order_date = now()->format('Y-m-d');
    }

    /**
     * Submit the procurement form.
     */
    public function submit(): void
    {
        $this->validate();

        $this->errorMessage = '';
        $this->successMessage = '';

        DB::transaction(function () {
            // Create procurement record
            Procurement::create([
                'warehouse_product_id' => $this->warehouseProductId,
                'user_id'              => Auth::id(),
                'vendor_name'          => $this->vendor_name,
                'vendor_contact'       => $this->vendor_contact ?: null,
                'order_date'           => $this->order_date,
                'eta_date'             => $this->eta_date ?: null,
                'status'               => 'pending',
                'notes'                => $this->notes ?: null,
            ]);

            // Update warehouse_products status to 'on_order'
            $this->warehouseProduct->update([
                'status' => 'on_order',
            ]);
        });

        $this->successMessage = 'Pengadaan berhasil dibuat! Status produk diperbarui menjadi "On Order".';

        // Reset form
        $this->reset(['vendor_name', 'vendor_contact', 'eta_date', 'notes']);
        $this->order_date = now()->format('Y-m-d');

        // Refresh the model
        $this->warehouseProduct->refresh();
    }

    public function render()
    {
        return view('livewire.procurement-form')
            ->layout('layouts.app');
    }
}
