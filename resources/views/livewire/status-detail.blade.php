<div
    x-data="{
        showModal: false,
        selectedItem: null,
        openModal(item) {
            this.selectedItem = item;
            this.showModal = true;
        },
        closeModal() {
            this.showModal = false;
            this.selectedItem = null;
        }
    }"
    @keydown.escape.window="closeModal()"
>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $title }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Filters --}}
            <div class="bg-white rounded-2xl border border-gray-200/60 shadow-sm p-6">
                <div class="flex flex-col sm:flex-row gap-4">
                    {{-- Search --}}
                    <div class="relative flex-1">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari produk, SKU, gudang..."
                            class="pl-9 pr-4 py-2.5 text-sm bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition-all w-full"/>
                    </div>

                    {{-- Filter Gudang --}}
                    <select wire:model.live="filterWarehouse" class="min-w-[180px] py-2.5 px-3 pe-8 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                        <option value="">Semua Gudang</option>
                        @foreach ($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Hint for Rent --}}
            @if ($showOrderBtn)
                <p class="text-xs text-gray-400 px-1">
                    <span class="inline-flex items-center gap-1 text-teal-600 font-medium">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Klik baris item yang belum dipesan
                    </span>
                    untuk membuka pilihan tindakan.
                </p>
            @endif

            {{-- Results Table --}}
            <div class="bg-white rounded-2xl border border-gray-200/60 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-700">
                        {{ $items->total() }} item ditemukan
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50/80">
                                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">No</th>
                                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Produk</th>
                                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">SKU</th>
                                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Gudang</th>
                                <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Stok</th>
                                <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">ROP</th>
                                @if ($isTotalLow)
                                    <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Status</th>
                                @endif
                                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Terakhir Update</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($items as $index => $item)
                                @php
                                    $rop = $item->rop_mode === 'manual'
                                        ? $item->rop_manual
                                        : app(\App\Services\InventoryService::class)->calculateROP(
                                            (float) $item->avg_daily_usage,
                                            (int) $item->lead_time,
                                            (int) $item->safety_stock
                                        );
                                    $isClickable = $showOrderBtn && $item->status === 'low_stock';
                                @endphp

                                <tr
                                    class="transition-colors {{ $isClickable ? 'hover:bg-teal-50/60 cursor-pointer group' : 'hover:bg-gray-50/50' }}"
                                    @if ($isClickable)
                                        @click="openModal({
                                            id: {{ $item->id }},
                                            name: @js($item->product->name ?? '-'),
                                            sku: @js($item->product->sku ?? '-'),
                                            unit: @js($item->product->unit ?? ''),
                                            warehouse: @js($item->warehouse->name ?? '-'),
                                            warehouseId: {{ $item->warehouse_id }},
                                            stock: {{ $item->current_stock }},
                                            rop: {{ (int) $rop }},
                                            procurementUrl: @js(route('procurement.create', $item->id)),
                                            warehouseUrl: @js(route('warehouse.detail', $item->warehouse_id))
                                        })"
                                    @endif
                                >
                                    <td class="px-6 py-4 text-sm text-gray-400 font-mono">{{ $items->firstItem() + $index }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            @if ($isClickable)
                                                <div class="w-7 h-7 bg-teal-100 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-teal-200 transition-colors">
                                                    <svg class="w-3.5 h-3.5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                                </div>
                                            @endif
                                            <div>
                                                <p class="text-sm font-semibold {{ $isClickable ? 'text-teal-700 group-hover:text-teal-800' : 'text-gray-900' }}">{{ $item->product->name ?? '-' }}</p>
                                                <p class="text-xs text-teal-500 mt-0.5">{{ $item->product->unit ?? '' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 font-mono">{{ $item->product->sku ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $item->warehouse->name ?? '-' }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-sm font-bold {{ $item->current_stock <= 0 ? 'text-red-600' : 'text-gray-900' }}">
                                            {{ number_format($item->current_stock, 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm text-gray-600">
                                        {{ number_format($rop, 0, ',', '.') }}
                                    </td>
                                    @if ($isTotalLow)
                                        <td class="px-6 py-4 text-center">
                                            @if ($item->status === 'on_order')
                                                <span class="inline-flex items-center px-2.5 py-1 bg-blue-50 text-blue-700 text-xs font-semibold rounded-lg">
                                                    Sudah Order
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-red-50 text-red-700 text-xs font-semibold rounded-lg">
                                                    <span class="w-1.5 h-1.5 bg-red-500 rounded-full animate-pulse"></span>
                                                    Belum Order
                                                </span>
                                            @endif
                                        </td>
                                    @endif
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ $item->updated_at ? $item->updated_at->format('d/m/Y H:i') : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isTotalLow ? 8 : 7 }}" class="px-6 py-12 text-center text-sm text-gray-400">
                                        <div class="flex flex-col items-center gap-2">
                                            <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                            </svg>
                                            Tidak ada item ditemukan.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($items->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100">
                        {{ $items->links('vendor.pagination.livewire-light') }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- Item Action Modal (Rent only, low_stock items)               --}}
    {{-- ============================================================ --}}
    <div
        x-show="showModal"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;"
    >
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="closeModal()"></div>

        {{-- Modal Panel --}}
        <div class="flex min-h-full items-center justify-center p-4">
            <div
                x-show="showModal"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden"
                @click.stop
            >
                {{-- Modal Header --}}
                <div class="flex items-center justify-between px-6 pt-6 pb-4 border-b border-gray-100">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Detail Item</h3>
                        <p class="text-xs text-gray-400 mt-0.5" x-text="selectedItem ? '#' + selectedItem.id : ''"></p>
                    </div>
                    <button @click="closeModal()" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="px-6 py-5 space-y-4">

                    {{-- Product Card --}}
                    <div class="bg-gray-50 rounded-xl p-4 flex items-center gap-4">
                        <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-gray-900 truncate" x-text="selectedItem?.name"></p>
                            <p class="text-xs text-gray-400 font-mono mt-0.5" x-text="selectedItem?.sku"></p>
                            <p class="text-xs text-teal-600 mt-0.5" x-text="selectedItem?.unit"></p>
                        </div>
                    </div>

                    {{-- Info Grid --}}
                    <div class="grid grid-cols-3 gap-3">
                        <div class="bg-white border border-gray-100 rounded-xl p-3 text-center">
                            <p class="text-xs text-gray-400 mb-1">Stok Saat Ini</p>
                            <p class="text-lg font-bold text-red-600" x-text="selectedItem ? selectedItem.stock.toLocaleString('id-ID') : ''"></p>
                        </div>
                        <div class="bg-white border border-gray-100 rounded-xl p-3 text-center">
                            <p class="text-xs text-gray-400 mb-1">ROP</p>
                            <p class="text-lg font-bold text-amber-600" x-text="selectedItem ? selectedItem.rop.toLocaleString('id-ID') : ''"></p>
                        </div>
                        <div class="bg-white border border-gray-100 rounded-xl p-3 text-center">
                            <p class="text-xs text-gray-400 mb-1">Status</p>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-red-50 text-red-700 text-xs font-semibold rounded-lg">
                                <span class="w-1.5 h-1.5 bg-red-500 rounded-full animate-pulse"></span>
                                Rendah
                            </span>
                        </div>
                    </div>

                    {{-- Warehouse Info --}}
                    <div class="flex items-center gap-3 p-3 bg-teal-50 rounded-xl border border-teal-100">
                        <svg class="w-4 h-4 text-teal-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <div>
                            <p class="text-xs text-teal-600 font-medium">Gudang</p>
                            <p class="text-sm font-bold text-teal-800" x-text="selectedItem?.warehouse"></p>
                        </div>
                    </div>

                    {{-- Alert --}}
                    <div class="flex items-start gap-2.5 p-3 bg-amber-50 rounded-xl border border-amber-100">
                        <svg class="w-4 h-4 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        <p class="text-xs text-amber-700">Stok item ini berada di bawah <strong>Reorder Point</strong> dan belum ada pesanan aktif. Segera lakukan pengadaan atau cek kondisi gudang.</p>
                    </div>
                </div>

                {{-- Modal Footer — Action Buttons --}}
                <div class="px-6 pb-6 pt-2 flex gap-3">
                    {{-- Tombol Gudang --}}
                    <a
                        :href="selectedItem?.warehouseUrl"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 bg-gray-100 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-200 transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        Lihat Gudang
                    </a>

                    {{-- Tombol Pesan --}}
                    <a
                        :href="selectedItem?.procurementUrl"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 bg-teal-600 text-white text-sm font-semibold rounded-xl hover:bg-teal-700 transition-colors shadow-sm"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Buat Pesanan
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
