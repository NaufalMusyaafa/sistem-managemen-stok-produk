<div>
    {{-- Page Heading --}}
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Form Pengadaan') }}
            </h2>
        </div>
    </header>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Product Info Card --}}
            @if ($warehouseProduct)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <div class="flex-1 grid grid-cols-2 sm:grid-cols-4 gap-4">
                            <div>
                                <p class="text-xs text-gray-400 font-medium uppercase tracking-wider">Produk</p>
                                <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $warehouseProduct->product->name }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-medium uppercase tracking-wider">Gudang</p>
                                <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $warehouseProduct->warehouse->name }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-medium uppercase tracking-wider">Stok Saat Ini</p>
                                <p class="text-sm font-semibold mt-0.5 {{ $warehouseProduct->current_stock <= $warehouseProduct->reorder_point ? 'text-red-600' : 'text-emerald-600' }}">
                                    {{ number_format($warehouseProduct->current_stock) }} {{ $warehouseProduct->product->unit }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-medium uppercase tracking-wider">Status</p>
                                <span class="inline-flex items-center mt-0.5 px-2.5 py-0.5 rounded-full text-xs font-semibold
                                    {{ $warehouseProduct->status === 'normal' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                    {{ $warehouseProduct->status === 'low_stock' ? 'bg-red-100 text-red-700' : '' }}
                                    {{ $warehouseProduct->status === 'on_order' ? 'bg-blue-100 text-blue-700' : '' }}
                                ">
                                    {{ $warehouseProduct->status === 'normal' ? 'Normal' : '' }}
                                    {{ $warehouseProduct->status === 'low_stock' ? 'Stok Rendah' : '' }}
                                    {{ $warehouseProduct->status === 'on_order' ? 'Dalam Pesanan' : '' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Success Alert --}}
            @if ($successMessage)
                <div
                    x-data="{ show: true }"
                    x-init="setTimeout(() => show = false, 5000)"
                    x-show="show"
                    x-transition:enter="transform transition ease-out duration-300"
                    x-transition:enter-start="translate-y-2 opacity-0"
                    x-transition:enter-end="translate-y-0 opacity-100"
                    x-transition:leave="transform transition ease-in duration-200"
                    x-transition:leave-start="translate-y-0 opacity-100"
                    x-transition:leave-end="-translate-y-2 opacity-0"
                    class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg"
                >
                    <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm font-medium">{{ $successMessage }}</p>
                </div>
            @endif

            {{-- Error Alert --}}
            @if ($errorMessage)
                <div class="flex items-center gap-3 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
                    <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm font-medium">{{ $errorMessage }}</p>
                </div>
            @endif

            {{-- Form Card --}}
            <form wire:submit="submit" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8 space-y-6">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Detail Pengadaan
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    {{-- Vendor Name --}}
                    <div class="sm:col-span-2">
                        <label for="vendor_name" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Nama Vendor <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="vendor_name"
                            wire:model="vendor_name"
                            placeholder="Masukkan nama vendor..."
                            class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder:text-gray-400"
                        />
                        @error('vendor_name')
                            <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Vendor Contact --}}
                    <div class="sm:col-span-2">
                        <label for="vendor_contact" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Kontak Vendor
                        </label>
                        <input
                            type="text"
                            id="vendor_contact"
                            wire:model="vendor_contact"
                            placeholder="No. telepon atau email vendor..."
                            class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder:text-gray-400"
                        />
                        @error('vendor_contact')
                            <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Order Date --}}
                    <div>
                        <label for="order_date" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Tanggal Order <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="date"
                            id="order_date"
                            wire:model="order_date"
                            class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                        />
                        @error('order_date')
                            <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- ETA Date --}}
                    <div>
                        <label for="eta_date" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Estimasi Tiba (ETA)
                        </label>
                        <input
                            type="date"
                            id="eta_date"
                            wire:model="eta_date"
                            class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                        />
                        @error('eta_date')
                            <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Notes --}}
                    <div class="sm:col-span-2">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Catatan
                        </label>
                        <textarea
                            id="notes"
                            wire:model="notes"
                            rows="3"
                            placeholder="Catatan tambahan (opsional)..."
                            class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all resize-none placeholder:text-gray-400"
                        ></textarea>
                        @error('notes')
                            <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="flex justify-end pt-4 border-t border-gray-100">
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500/50 focus:ring-offset-2 transition-all shadow-sm disabled:opacity-60 disabled:cursor-not-allowed"
                    >
                        <svg wire:loading.remove wire:target="submit" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        <svg wire:loading wire:target="submit" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="submit">Submit Pengadaan</span>
                        <span wire:loading wire:target="submit">Memproses...</span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
