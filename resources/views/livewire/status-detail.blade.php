<div>
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
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4 text-sm text-gray-400 font-mono">{{ $items->firstItem() + $index }}</td>
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">{{ $item->product->name ?? '-' }}</p>
                                            <p class="text-xs text-teal-500 mt-0.5">{{ $item->product->unit ?? '' }}</p>
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
                                        @php
                                            $rop = $item->rop_mode === 'manual'
                                                ? $item->rop_manual
                                                : app(\App\Services\InventoryService::class)->calculateROP(
                                                    (float) $item->avg_daily_usage,
                                                    (int) $item->lead_time,
                                                    (int) $item->safety_stock
                                                );
                                        @endphp
                                        {{ number_format($rop, 0, ',', '.') }}
                                    </td>
                                    @if ($isTotalLow)
                                        <td class="px-6 py-4 text-center">
                                            @if ($item->status === 'on_order')
                                                <span class="inline-flex items-center px-2.5 py-1 bg-blue-50 text-blue-700 text-xs font-semibold rounded-lg">
                                                    Sudah Order
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 bg-red-50 text-red-700 text-xs font-semibold rounded-lg">
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
</div>
