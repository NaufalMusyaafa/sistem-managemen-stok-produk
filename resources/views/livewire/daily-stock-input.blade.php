<div>
    {{-- Page Heading --}}
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Input Stok Harian') }}
                </h2>
                <span class="inline-flex items-center gap-1.5 text-sm text-gray-500">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    {{ Auth::user()->warehouse->name ?? 'Warehouse' }}
                </span>
            </div>
        </div>
    </header>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Action Bar --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                {{-- Search --}}
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

                {{-- Save All Button --}}
                <button
                    wire:click="saveAll"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500/50 focus:ring-offset-2 transition-all shadow-sm disabled:opacity-60 disabled:cursor-not-allowed"
                >
                    <svg wire:loading.remove wire:target="saveAll" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <svg wire:loading wire:target="saveAll" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="saveAll">Simpan Semua</span>
                    <span wire:loading wire:target="saveAll">Menyimpan...</span>
                </button>
            </div>

            {{-- Success Alert --}}
            @if ($successMessage)
                <div
                    x-data="{ show: true }"
                    x-init="setTimeout(() => show = false, 4000)"
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

            {{-- Data Table --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4 w-12">#</th>
                                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Produk</th>
                                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">SKU</th>
                                <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Stok Terakhir</th>
                                <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">ROP</th>
                                <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4 w-44">Stok Sekarang</th>
                                <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Selisih</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @php $rowNum = 0; @endphp
                            @forelse ($filteredItems as $id => $item)
                                @php
                                    $rowNum++;
                                    $newStock = (int) ($item['new_stock'] ?? 0);
                                    $rop = (int) ($item['rop'] ?? 0);
                                    $diff = (int) ($item['difference'] ?? 0);
                                    $isBelowRop = $newStock < $rop && $newStock !== $item['current_stock'];
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

                                    {{-- Last Stock --}}
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-sm font-semibold text-gray-700">{{ number_format($item['current_stock']) }}</span>
                                    </td>

                                    {{-- ROP --}}
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 bg-amber-50 text-amber-700 text-xs font-semibold rounded-lg border border-amber-200/50">
                                            {{ number_format($rop) }}
                                        </span>
                                    </td>

                                    {{-- Current Stock Input --}}
                                    <td class="px-6 py-4 text-center">
                                        <input
                                            type="number"
                                            wire:model.live.debounce.500ms="stockInputs.{{ $id }}.new_stock"
                                            min="0"
                                            class="w-32 mx-auto text-center text-sm font-semibold rounded-lg border-2 px-3 py-2.5 transition-all focus:ring-2 focus:ring-offset-1
                                                {{ $isBelowRop
                                                    ? 'border-red-400 bg-red-50 text-red-700 focus:ring-red-500/30 focus:border-red-500'
                                                    : 'border-gray-200 bg-white text-gray-900 focus:ring-indigo-500/30 focus:border-indigo-500'
                                                }}"
                                        />
                                        @if ($isBelowRop)
                                            <p class="text-xs text-red-500 font-medium mt-1.5 flex items-center justify-center gap-1">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                                Di bawah ROP
                                            </p>
                                        @endif
                                    </td>

                                    {{-- Difference --}}
                                    <td class="px-6 py-4 text-center">
                                        @if ($diff !== 0)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold rounded-lg
                                                {{ $diff > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/50' : 'bg-red-50 text-red-700 border border-red-200/50' }}
                                            ">
                                                @if ($diff > 0)
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7" /></svg>
                                                    +{{ number_format($diff) }}
                                                @else
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" /></svg>
                                                    {{ number_format($diff) }}
                                                @endif
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-300">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-16 text-center">
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
                        Menampilkan <span class="font-semibold text-gray-600">{{ count($filteredItems) }}</span> produk
                        · Input yang <span class="text-red-500 font-semibold">merah</span> menandakan stok di bawah Reorder Point (ROP)
                    </p>
                </div>
            </div>

        </div>
    </div>
</div>
