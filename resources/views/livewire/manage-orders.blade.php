<div>
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Pemesanan Barang') }}</h2>
        </div>
    </header>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari produk, gudang, vendor..." class="pl-9 pr-4 py-2.5 text-sm bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition-all w-64"/>
                    </div>
                    <select wire:model.live="filterStatus" class="min-w-[160px] py-2.5 px-3 pe-8 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                        <option value="">Semua Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="ordered">Ordered</option>
                        <option value="received">Diterima</option>
                        <option value="cancelled">Dibatalkan</option>
                        <option value="expired">Expired</option>
                    </select>
                </div>
            </div>

            @if ($successMessage)
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg">
                    <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm font-medium">{{ $successMessage }}</p>
                </div>
            @endif

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
                                @if ($canManage)
                                <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4 w-40">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($orders as $order)
                                <tr class="hover:bg-gray-50/50 transition-colors" wire:key="order-{{ $order->id }}">
                                    <td class="px-6 py-4 text-sm text-gray-400 font-mono">{{ ($orders->currentPage() - 1) * $orders->perPage() + $loop->iteration }}</td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-semibold text-gray-900">{{ $order->warehouseProduct->product->name ?? '-' }}</p>
                                        <p class="text-xs text-gray-400 font-mono">{{ $order->warehouseProduct->product->sku ?? '-' }}</p>
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
                                            <span class="text-sm {{ $order->eta_date->isPast() && in_array($order->status, ['pending', 'approved', 'ordered']) ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                                                {{ $order->eta_date->format('d/m/Y') }}
                                            </span>
                                            @if ($order->eta_date->isPast() && in_array($order->status, ['pending', 'approved', 'ordered']))
                                                <p class="text-xs text-red-500 font-medium">Terlambat</p>
                                            @endif
                                        @else
                                            <span class="text-sm text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @switch($order->status)
                                            @case('pending')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-amber-50 text-amber-700 text-xs font-semibold rounded-lg"><span class="w-1.5 h-1.5 bg-amber-500 rounded-full"></span>Pending</span>
                                                @break
                                            @case('approved')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-teal-50 text-teal-700 text-xs font-semibold rounded-lg"><span class="w-1.5 h-1.5 bg-teal-500 rounded-full"></span>Approved</span>
                                                @break
                                            @case('ordered')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-blue-50 text-blue-700 text-xs font-semibold rounded-lg"><span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>Ordered</span>
                                                @break
                                            @case('received')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-lg"><span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>Diterima</span>
                                                @break
                                            @case('cancelled')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-gray-100 text-gray-500 text-xs font-semibold rounded-lg"><span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>Dibatalkan</span>
                                                @break
                                            @case('expired')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-red-50 text-red-700 text-xs font-semibold rounded-lg"><span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>Expired</span>
                                                @break
                                        @endswitch
                                    </td>
                                    @if ($canManage)
                                    <td class="px-6 py-4 text-center">
                                        @if (in_array($order->status, ['pending', 'approved', 'ordered']))
                                            <div class="inline-flex items-center gap-1">
                                                <button wire:click="markReceived({{ $order->id }})" wire:confirm="Tandai pesanan ini sebagai diterima?" class="px-2.5 py-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition-colors" title="Tandai Diterima">
                                                    ✓ Diterima
                                                </button>
                                                <button wire:click="cancelOrder({{ $order->id }})" wire:confirm="Batalkan pesanan ini?" class="px-2.5 py-1.5 text-xs font-medium text-red-700 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors" title="Batalkan">
                                                    ✕
                                                </button>
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400">—</span>
                                        @endif
                                    </td>
                                    @endif
                                </tr>
                            @empty
                                <tr><td colspan="{{ $canManage ? 8 : 7 }}" class="px-6 py-16 text-center text-sm text-gray-500">Belum ada pesanan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="bg-gray-50 border-t border-gray-100 px-6 py-3">
                    <p class="text-xs text-gray-400">Total <span class="font-semibold text-gray-600">{{ $orders->total() }}</span> pesanan</p>
                </div>
                @if ($orders->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100">
                        {{ $orders->links('vendor.pagination.livewire-light') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
