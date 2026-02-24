<div>
    {{-- Page Heading --}}
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-indigo-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali
                    </a>
                    <div class="h-6 border-l border-gray-200"></div>
                    <div>
                        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                            {{ $warehouse->name }}
                        </h2>
                        <p class="text-sm text-gray-500 mt-0.5">{{ $warehouse->location }}</p>
                    </div>
                </div>
                @php
                    $totalItems = count($stockItems);
                    $normalCount = collect($stockItems)->where('status', 'normal')->count();
                    $lowCount = collect($stockItems)->where('status', 'low_stock')->count();
                    $orderCount = collect($stockItems)->where('status', 'on_order')->count();
                @endphp
                <div class="hidden sm:flex items-center gap-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-lg">
                        <span class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                        {{ $normalCount }} Normal
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50 text-red-700 text-xs font-semibold rounded-lg">
                        <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                        {{ $lowCount }} Low Stock
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-700 text-xs font-semibold rounded-lg">
                        <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                        {{ $orderCount }} On Order
                    </span>
                </div>
            </div>
        </div>
    </header>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Search Bar --}}
            <div class="flex items-center justify-between">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Cari produk..."
                        class="pl-9 pr-4 py-2.5 text-sm bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all w-64"
                    />
                </div>
                <p class="text-sm text-gray-500">
                    Menampilkan <span class="font-semibold text-gray-700">{{ count($filteredItems) }}</span> dari {{ $totalItems }} produk
                </p>
            </div>

            {{-- Data Table --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4 w-12">#</th>
                                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Produk</th>
                                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">SKU</th>
                                <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Stok Saat Ini</th>
                                <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">ROP</th>
                                <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @php $rowNum = 0; @endphp
                            @forelse ($filteredItems as $id => $item)
                                @php
                                    $rowNum++;
                                    $stock = (int) $item['current_stock'];
                                    $rop = (int) $item['rop'];
                                    $isBelowRop = $stock < $rop;
                                @endphp
                                <tr class="hover:bg-gray-50/50 transition-colors" wire:key="row-{{ $id }}">
                                    {{-- Row Number --}}
                                    <td class="px-6 py-4 text-sm text-gray-400 font-mono">{{ $rowNum }}</td>

                                    {{-- Product Name --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900">{{ $item['product_name'] }}</p>
                                                <p class="text-xs text-gray-400">{{ $item['product_unit'] }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- SKU --}}
                                    <td class="px-6 py-4">
                                        <span class="inline-flex px-2.5 py-1 bg-gray-100 text-gray-600 text-xs font-mono rounded-lg">{{ $item['product_sku'] }}</span>
                                    </td>

                                    {{-- Current Stock --}}
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-sm font-semibold {{ $isBelowRop ? 'text-red-600' : 'text-gray-700' }}">
                                            {{ number_format($stock) }}
                                        </span>
                                    </td>

                                    {{-- ROP --}}
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 bg-amber-50 text-amber-700 text-xs font-semibold rounded-lg border border-amber-200/50">
                                            {{ number_format($rop) }}
                                        </span>
                                    </td>

                                    {{-- Status --}}
                                    <td class="px-6 py-4 text-center">
                                        @if ($item['status'] === 'normal')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-lg border border-emerald-200/50">
                                                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                                                Normal
                                            </span>
                                        @elseif ($item['status'] === 'low_stock')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-red-50 text-red-700 text-xs font-semibold rounded-lg border border-red-200/50">
                                                <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                                                Low Stock
                                            </span>
                                        @elseif ($item['status'] === 'on_order')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 text-blue-700 text-xs font-semibold rounded-lg border border-blue-200/50">
                                                <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
                                                On Order
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <div class="w-12 h-12 bg-gray-100 rounded-2xl flex items-center justify-center">
                                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                                </svg>
                                            </div>
                                            <p class="text-sm text-gray-500 font-medium">Tidak ada produk ditemukan.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Table Footer --}}
                <div class="bg-gray-50 border-t border-gray-100 px-6 py-3">
                    <p class="text-xs text-gray-400">
                        Stok yang <span class="text-red-500 font-semibold">merah</span> menandakan stok di bawah Reorder Point (ROP)
                        · Halaman ini bersifat <span class="font-semibold text-gray-600">read-only</span>
                    </p>
                </div>
            </div>

        </div>
    </div>
</div>
