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
    public int $ordered_quantity = 1;
    public string $order_date = '';
    public string $eta_date = '';
    public string $notes = '';

    /**
     * URL for the back button (determined by the 'from' query param).
     */
    public string $backUrl = '';

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
            'vendor_name'      => 'required|string|max:255',
            'vendor_contact'   => 'nullable|string|max:255',
            'ordered_quantity' => 'required|integer|min:1',
            'order_date'       => 'required|date|after_or_equal:today',
            'eta_date'         => 'nullable|date|after_or_equal:order_date',
            'notes'            => 'nullable|string|max:1000',
        ];
    }

    /**
     * Custom validation messages.
     */
    protected function messages(): array
    {
        return [
            'vendor_name.required'       => 'Nama vendor wajib diisi.',
            'ordered_quantity.required'  => 'Jumlah pesanan wajib diisi.',
            'ordered_quantity.min'       => 'Jumlah pesanan minimal 1.',
            'order_date.required'        => 'Tanggal order wajib diisi.',
            'order_date.after_or_equal'  => 'Tanggal order tidak boleh sebelum hari ini.',
            'eta_date.after_or_equal'    => 'Estimasi tiba harus setelah atau sama dengan tanggal order.',
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

        // Determine back URL from 'from' query param
        $from = request()->query('from');
        if ($from === 'low_stock') {
            $this->backUrl = route('status.detail', 'low_stock');
        } elseif ($from === 'total_low') {
            $this->backUrl = route('status.detail', 'total_low');
        } else {
            $this->backUrl = route('warehouse.detail', $this->warehouseProduct->warehouse_id);
        }
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
                'ordered_quantity'     => $this->ordered_quantity,
                'order_date'           => $this->order_date,
                'eta_date'             => $this->eta_date ?: null,
                'status'               => 'ordered',
                'notes'                => $this->notes ?: null,
            ]);

            // Update warehouse_products status to 'on_order'
            $this->warehouseProduct->update([
                'status' => 'on_order',
            ]);
        });

        // Dispatch browser event for pop-up + redirect
        $this->dispatch('procurement-submitted', [
            'message' => 'Pengadaan berhasil dibuat! Status produk diperbarui menjadi "On Order".',
            'redirectUrl' => route('warehouse.detail', $this->warehouseProduct->warehouse_id),
        ]);
    }

    public function render()
    {
        return view('livewire.procurement-form')
            ->layout('layouts.app');
    }
}
