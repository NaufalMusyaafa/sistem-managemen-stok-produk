<div>
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Pemesanan Barang') }}</h2>
        </div>
    </header>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Filter Bar --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari produk, gudang, vendor..." class="pl-9 pr-4 py-2.5 text-sm bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition-all w-64"/>
                    </div>
                    <select wire:model.live="filterStatus" class="py-2.5 px-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500" style="min-width:160px; padding-right:2rem;">
                        <option value="">Semua Status</option>
                        <option value="ordered">Sedang Dipesan</option>
                        <option value="received">Diterima</option>
                        <option value="canceled">Dibatalkan</option>
                    </select>
                </div>
            </div>

            {{-- Success Message (outside modal) --}}
            @if ($successMessage && !$selectedOrder)
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg">
                    <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm font-medium">{{ $successMessage }}</p>
                </div>
            @endif

            {{-- Table --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4 w-12">#</th>
                                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Produk</th>
                                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Gudang</th>
                                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Vendor</th>
                                <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Tgl Pesan</th>
                                <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">ETA</th>
                                <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($orders as $order)
                                @php
                                    $isOverdue = $order->eta_date
                                        && $order->eta_date->isPast()
                                        && $order->status === 'ordered';
                                @endphp
                                <tr
                                    wire:click="openDetail({{ $order->id }})"
                                    wire:key="order-{{ $order->id }}"
                                    class="cursor-pointer transition-colors {{ $isOverdue ? 'bg-red-50 hover:bg-red-100' : 'hover:bg-teal-50/40' }}"
                                >
                                    <td class="px-6 py-4 text-sm text-gray-400 font-mono">{{ ($orders->currentPage() - 1) * $orders->perPage() + $loop->iteration }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            @if ($isOverdue)
                                                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-red-100 text-red-600 flex-shrink-0" title="Jatuh tempo">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                                </span>
                                            @endif
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900">{{ $order->warehouseProduct->product->name ?? '-' }}</p>
                                                <p class="text-xs text-gray-400 font-mono">{{ $order->warehouseProduct->product->sku ?? '-' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $order->warehouseProduct->warehouse->name ?? '-' }}</td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-medium text-gray-900">{{ $order->vendor_name }}</p>
                                        @if ($order->vendor_contact)
                                            <p class="text-xs text-gray-400">{{ $order->vendor_contact }}</p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm text-gray-600">{{ $order->order_date->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 text-center">
                                        @if ($order->eta_date)
                                            <span class="text-sm {{ $isOverdue ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                                                {{ $order->eta_date->format('d/m/Y') }}
                                            </span>
                                            @if ($isOverdue)
                                                <p class="text-xs text-red-500 font-semibold">Jatuh Tempo</p>
                                            @endif
                                        @else
                                            <span class="text-sm text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if ($order->status === 'ordered')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-teal-50 text-teal-700 text-xs font-semibold rounded-lg">
                                                <span class="w-1.5 h-1.5 bg-teal-500 rounded-full"></span>Sedang Dipesan
                                            </span>
                                        @elseif ($order->status === 'received')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-lg">
                                                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>Diterima
                                            </span>
                                        @elseif ($order->status === 'canceled')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-gray-100 text-gray-500 text-xs font-semibold rounded-lg">
                                                <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>Dibatalkan
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-6 py-16 text-center text-sm text-gray-500">Belum ada pesanan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="bg-gray-50 border-t border-gray-100 px-6 py-3">
                    <p class="text-xs text-gray-400">Total <span class="font-semibold text-gray-600">{{ $orders->total() }}</span> pesanan · Klik baris untuk melihat detail</p>
                </div>
                @if ($orders->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100">
                        {{ $orders->links('vendor.pagination.livewire-light') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Detail Modal Pop-up --}}
    @if ($selectedOrder)
        {{-- Backdrop --}}
        <div
            wire:click="closeDetail"
            class="fixed inset-0 bg-black/40 z-40"
            style="backdrop-filter: blur(3px);"
        ></div>

        {{-- Centered Modal --}}
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="pointer-events:none;">
            <div
                class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden"
                style="pointer-events:auto; animation: fadeScaleIn 0.2s ease-out;"
            >
                {{-- Modal Header --}}
                <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Detail Pesanan</h3>
                        <p class="text-sm text-gray-400 mt-0.5">#{{ $selectedOrder->id }}</p>
                    </div>
                    <button wire:click="closeDetail" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="flex-1 overflow-y-auto p-6 space-y-5">

                    @php
                        $panelOverdue = $selectedOrder->eta_date
                            && $selectedOrder->eta_date->isPast()
                            && $selectedOrder->status === 'ordered';
                    @endphp

                    {{-- Overdue Alert --}}
                    @if ($panelOverdue)
                        <div class="flex items-start gap-3 p-4 bg-red-50 border border-red-200 rounded-xl">
                            <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            <div>
                                <p class="text-sm font-semibold text-red-800">Pesanan Jatuh Tempo!</p>
                                <p class="text-xs text-red-600 mt-0.5">ETA sudah lewat. Segera tandai sebagai diterima atau perbarui tanggal ETA jika barang belum tiba.</p>
                            </div>
                        </div>
                    @endif

                    {{-- Success message inside modal --}}
                    @if ($successMessage)
                        <div class="flex items-center gap-3 p-3 bg-emerald-50 border border-emerald-200 rounded-lg">
                            <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-sm font-medium text-emerald-800">{{ $successMessage }}</p>
                        </div>
                    @endif

                    {{-- Product Summary (mirip bagian atas form pengadaan) --}}
                    <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl border border-gray-100">
                        <div class="flex flex-wrap gap-6">
                            <div>
                                <p class="text-xs text-gray-400 uppercase tracking-wide font-medium">Produk</p>
                                <p class="text-sm font-bold text-gray-900">{{ $selectedOrder->warehouseProduct->product->name ?? '-' }}</p>
                                <p class="text-xs text-gray-400 font-mono">{{ $selectedOrder->warehouseProduct->product->sku ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 uppercase tracking-wide font-medium">Gudang</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $selectedOrder->warehouseProduct->warehouse->name ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 uppercase tracking-wide font-medium">Stok Saat Ini</p>
                                @php $wpStatus = $selectedOrder->warehouseProduct->status ?? '-'; @endphp
                                <p class="text-sm font-bold {{ $wpStatus === 'low_stock' ? 'text-red-600' : 'text-gray-900' }}">
                                    {{ number_format($selectedOrder->warehouseProduct->current_stock ?? 0) }} buah
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 uppercase tracking-wide font-medium">Status</p>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-semibold
                                    {{ $wpStatus === 'normal' ? 'bg-emerald-50 text-emerald-700' : ($wpStatus === 'on_order' ? 'bg-teal-50 text-teal-700' : 'bg-red-50 text-red-700') }}">
                                    {{ $wpStatus === 'normal' ? 'Normal' : ($wpStatus === 'on_order' ? 'On Order' : 'Stok Rendah') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Detail Pesanan --}}
                    <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
                        <h4 class="flex items-center gap-2 text-base font-bold text-gray-800">
                            <svg class="w-5 h-5 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Detail Pesanan
                        </h4>

                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-xs text-gray-500 font-medium mb-1">Nama Vendor</p>
                                <p class="font-semibold text-gray-900">{{ $selectedOrder->vendor_name }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-medium mb-1">Nomor Kontrak Pengadaan</p>
                                <p class="text-gray-700">{{ $selectedOrder->vendor_contact ?: '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-medium mb-1">Tanggal Pesan</p>
                                <p class="font-medium text-gray-900">{{ $selectedOrder->order_date->format('d/m/Y') }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-medium mb-1">Estimasi Tiba (ETA)</p>
                                @if ($selectedOrder->eta_date)
                                    <p class="font-medium {{ $panelOverdue ? 'text-red-600' : 'text-gray-900' }}">
                                        {{ $selectedOrder->eta_date->format('d/m/Y') }}
                                        @if ($panelOverdue)
                                            <span class="text-xs font-semibold text-red-500 ml-1">(Terlambat)</span>
                                        @endif
                                    </p>
                                @else
                                    <p class="text-gray-400">-</p>
                                @endif
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-medium mb-1">Status Pesanan</p>
                                @if ($selectedOrder->status === 'ordered')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-teal-50 text-teal-700 text-xs font-semibold rounded-lg">
                                        <span class="w-1.5 h-1.5 bg-teal-500 rounded-full"></span>Sedang Dipesan
                                    </span>
                                @elseif ($selectedOrder->status === 'received')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-lg">
                                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>Diterima
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-gray-100 text-gray-500 text-xs font-semibold rounded-lg">
                                        <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>Dibatalkan
                                    </span>
                                @endif
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-medium mb-1">Jumlah Dipesan</p>
                                <p class="font-bold text-indigo-700">{{ number_format($selectedOrder->ordered_quantity) }} {{ $selectedOrder->warehouseProduct->product->unit ?? 'unit' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-medium mb-1">Dipesan oleh</p>
                                <p class="text-gray-700">{{ $selectedOrder->user->name ?? '-' }}</p>
                            </div>
                        </div>

                        @if ($selectedOrder->notes)
                            <div>
                                <p class="text-xs text-gray-500 font-medium mb-1">Catatan</p>
                                <p class="text-sm text-gray-700 bg-gray-50 rounded-lg px-4 py-3 border border-gray-100">{{ $selectedOrder->notes }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Update ETA --}}
                    @if ($selectedOrder->status === 'ordered' && $canManage)
                        <div class="bg-white rounded-xl border border-gray-200 p-5">
                            <h4 class="text-sm font-bold text-gray-800 mb-3">Perbarui Tanggal ETA</h4>
                            <div class="flex items-end gap-3">
                                <div class="flex-1">
                                    <input
                                        type="date"
                                        wire:model="newEtaDate"
                                        class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition-all"
                                    />
                                    @error('newEtaDate') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                </div>
                                <button
                                    wire:click="updateEta"
                                    class="px-5 py-2.5 text-sm font-semibold text-white bg-teal-600 rounded-lg hover:bg-teal-700 transition-colors shadow-sm whitespace-nowrap"
                                >
                                    Simpan ETA
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Modal Footer --}}
                @if ($selectedOrder->status === 'ordered' && $canManage)
                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex items-center justify-end gap-3">
                        <button
                            wire:click="cancelOrder({{ $selectedOrder->id }})"
                            wire:confirm="Yakin ingin membatalkan pesanan ini?"
                            class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-red-600 bg-white border border-red-200 rounded-lg hover:bg-red-50 transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Batalkan Pesanan
                        </button>
                        <button
                            wire:click="confirmReceive({{ $selectedOrder->id }})"
                            class="flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors shadow-sm"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span wire:loading.remove wire:target="confirmReceive({{ $selectedOrder->id }})">Tandai Diterima</span>
                            <span wire:loading wire:target="confirmReceive({{ $selectedOrder->id }})">Memproses...</span>
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Stock Confirmation Modal --}}
    @if ($showStockConfirm)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 space-y-4 z-10">
                {{-- Back button --}}
                <button
                    wire:click="$set('showStockConfirm', false)"
                    class="absolute top-4 left-4 p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                    title="Kembali"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </button>
                <div class="flex items-center justify-center w-12 h-12 bg-amber-100 rounded-full mx-auto">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="text-center">
                    <h3 class="text-base font-bold text-gray-900">Stok Sudah di Atas ROP</h3>
                    <p class="text-sm text-gray-500 mt-1.5">
                        Stok item ini sudah di atas ROP. Apakah Anda tetap ingin menambahkan
                        <strong class="text-indigo-700">{{ $selectedOrder?->ordered_quantity ?? 0 }} {{ $selectedOrder?->warehouseProduct?->product?->unit ?? 'unit' }}</strong>
                        ke stok gudang?
                    </p>
                </div>
                <div class="flex gap-3 pt-1">
                    <button
                        wire:click="receiveWithStockChoice(false)"
                        class="flex-1 px-4 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors"
                    >
                        Tidak, Terima Saja
                    </button>
                    <button
                        wire:click="receiveWithStockChoice(true)"
                        class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition-colors shadow-sm"
                    >
                        Ya, Tambah Stok
                    </button>
                </div>
            </div>
        </div>
    @endif

    <style>
        @keyframes fadeScaleIn {
            from { opacity: 0; transform: scale(0.95); }
            to   { opacity: 1; transform: scale(1); }
        }
    </style>
</div>
