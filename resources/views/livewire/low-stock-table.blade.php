<div>
    @if ($lowStockItems->count() > 0)
        <div class="bg-white rounded-2xl border border-red-200/60 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-red-100 bg-red-50/50">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-red-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        ⚠️ Peringatan Stok Rendah
                    </h3>
                    <span class="inline-flex items-center px-3 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full">{{ $lowStockItems->total() }} item</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50/80">
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">#</th>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Produk</th>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Gudang</th>
                            <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Stok</th>
                            <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">ROP</th>
                            <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Defisit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($lowStockItems as $item)
                            @php
                                $deficit = $item->reorder_point - $item->current_stock;
                            @endphp
                            <tr class="hover:bg-red-50/30 transition-colors">
                                <td class="px-6 py-3 text-sm text-gray-400 font-mono">{{ ($lowStockItems->currentPage() - 1) * $lowStockItems->perPage() + $loop->iteration }}</td>
                                <td class="px-6 py-3">
                                    <p class="text-sm font-semibold text-gray-900">{{ $item->product->name ?? '-' }}</p>
                                    <p class="text-xs text-gray-400">{{ $item->product->sku ?? '-' }}</p>
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-600">{{ $item->warehouse->name ?? '-' }}</td>
                                <td class="px-6 py-3 text-center">
                                    <span class="text-sm font-bold text-red-600">{{ number_format($item->current_stock) }}</span>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <span class="text-sm font-semibold text-amber-600">{{ number_format($item->reorder_point) }}</span>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-lg">
                                        -{{ number_format($deficit) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($lowStockItems->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                    {{ $lowStockItems->links('vendor.pagination.livewire-light') }}
                </div>
            @endif
        </div>
    @endif
</div>
