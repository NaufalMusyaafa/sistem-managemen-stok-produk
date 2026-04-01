<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Monitoring') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- Welcome Banner --}}
            <div class="bg-gradient-to-r from-teal-600 to-cyan-600 rounded-2xl p-8 text-white shadow-xl shadow-teal-500/20">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold">Selamat Datang, {{ Auth::user()->name }}!</h3>
                        <p class="text-teal-100 mt-1">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-white/20 backdrop-blur-sm mt-2">
                                MANAGER
                            </span>
                        </p>
                    </div>
                    <div class="hidden sm:flex items-center gap-2 text-teal-100 text-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        {{ now()->translatedFormat('l, d F Y') }}
                    </div>
                </div>
            </div>

            @php
                $warehouses = \App\Models\Warehouse::withCount('warehouseProducts')->get();
                $allWarehouseProducts = \App\Models\WarehouseProduct::withoutGlobalScopes()->get();
                $normalCount = $allWarehouseProducts->where('status', 'normal')->count();
                $lowStockCount = $allWarehouseProducts->where('status', 'low_stock')->count();
                $onOrderCount = $allWarehouseProducts->where('status', 'on_order')->count();
                $totalLowCount = $lowStockCount + $onOrderCount;
                $totalPivots = $allWarehouseProducts->count();
            @endphp

            {{-- Summary Stats — Clickable Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                {{-- Stok Normal --}}
                <a href="{{ route('status.detail', 'normal') }}" class="bg-white rounded-2xl border border-gray-200/60 shadow-sm p-6 hover:shadow-lg hover:border-emerald-200 transition-all group cursor-pointer">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Stok Normal</p>
                            <p class="text-3xl font-bold text-emerald-600 mt-1">{{ $normalCount }}</p>
                            <p class="text-xs text-gray-400 mt-1">dari {{ $totalPivots }} item</p>
                        </div>
                        <div class="w-12 h-12 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </a>

                {{-- Stok Rendah (belum dipesan) --}}
                <a href="{{ route('status.detail', 'low_stock') }}" class="bg-white rounded-2xl border border-gray-200/60 shadow-sm p-6 hover:shadow-lg hover:border-red-200 transition-all group cursor-pointer">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Stok Rendah</p>
                            <p class="text-3xl font-bold text-red-600 mt-1">{{ $lowStockCount }}</p>
                            <p class="text-xs text-gray-400 mt-1">belum dipesan</p>
                        </div>
                        <div class="w-12 h-12 bg-gradient-to-br from-red-100 to-red-200 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                    </div>
                </a>

                {{-- Dalam Pesanan --}}
                <a href="{{ route('status.detail', 'on_order') }}" class="bg-white rounded-2xl border border-gray-200/60 shadow-sm p-6 hover:shadow-lg hover:border-blue-200 transition-all group cursor-pointer">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Dalam Pesanan</p>
                            <p class="text-3xl font-bold text-blue-600 mt-1">{{ $onOrderCount }}</p>
                            <p class="text-xs text-gray-400 mt-1">menunggu pengiriman</p>
                        </div>
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-blue-200 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </a>

                {{-- Total Stok Rendah (low_stock + on_order) --}}
                <a href="{{ route('status.detail', 'total_low') }}" class="bg-white rounded-2xl border border-gray-200/60 shadow-sm p-6 hover:shadow-lg hover:border-orange-200 transition-all group cursor-pointer">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Stok Rendah</p>
                            <p class="text-3xl font-bold text-orange-600 mt-1">{{ $totalLowCount }}</p>
                            <p class="text-xs text-gray-400 mt-1">rendah + dalam pesanan</p>
                        </div>
                        <div class="w-12 h-12 bg-gradient-to-br from-orange-100 to-orange-200 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.618 5.984A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Warehouse Table --}}
            <div class="bg-white rounded-2xl border border-gray-200/60 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Status Per Gudang
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50/80">
                                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">#</th>
                                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Gudang</th>
                                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Lokasi</th>
                                <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Total Produk</th>
                                <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">
                                    <span class="inline-flex items-center gap-1"><span class="w-2 h-2 bg-emerald-500 rounded-full"></span> Normal</span>
                                </th>
                                <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">
                                    <span class="inline-flex items-center gap-1"><span class="w-2 h-2 bg-red-500 rounded-full"></span> Low Stock</span>
                                </th>
                                <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">
                                    <span class="inline-flex items-center gap-1"><span class="w-2 h-2 bg-blue-500 rounded-full"></span> On Order</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($warehouses as $index => $warehouse)
                                @php
                                    $items = $allWarehouseProducts->where('warehouse_id', $warehouse->id);
                                    $wNormal = $items->where('status', 'normal')->count();
                                    $wLow = $items->where('status', 'low_stock')->count();
                                    $wOrder = $items->where('status', 'on_order')->count();
                                @endphp
                                <tr class="hover:bg-indigo-50/50 transition-colors cursor-pointer group" onclick="window.location='{{ route('warehouse.detail', $warehouse->id) }}'">
                                    <td class="px-6 py-4 text-sm text-gray-400 font-mono">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors">{{ $warehouse->name }}</span>
                                            <svg class="w-4 h-4 text-gray-300 group-hover:text-indigo-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $warehouse->location }}</td>
                                    <td class="px-6 py-4 text-center text-sm font-semibold text-gray-700">{{ $items->count() }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-lg">{{ $wNormal }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 {{ $wLow > 0 ? 'bg-red-50 text-red-700' : 'bg-gray-50 text-gray-400' }} text-xs font-bold rounded-lg">{{ $wLow }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 {{ $wOrder > 0 ? 'bg-blue-50 text-blue-700' : 'bg-gray-50 text-gray-400' }} text-xs font-bold rounded-lg">{{ $wOrder }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- No Low Stock Alert Table for Manager role --}}

        </div>
    </div>
</x-app-layout>
