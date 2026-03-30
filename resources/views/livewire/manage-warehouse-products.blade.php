<div>
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Kelola Stok Gudang') }}</h2>
        </div>
    </header>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari produk..." class="pl-9 pr-4 py-2.5 text-sm bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all w-56"/>
                    </div>
                    @unless ($isAdminUp3)
                    <select wire:model.live="filterWarehouse" class="py-2.5 px-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                        <option value="">Semua Gudang</option>
                        @foreach ($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                        @endforeach
                    </select>
                    @endunless
                </div>
                <button wire:click="openCreate" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500/50 focus:ring-offset-2 transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Item
                </button>
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
                                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Gudang</th>
                                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Produk</th>
                                <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Stok</th>
                                <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">ROP</th>
                                <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Status</th>
                                <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4 w-32">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($items as $index => $item)
                                <tr class="hover:bg-gray-50/50 transition-colors" wire:key="wp-{{ $item->id }}">
                                    <td class="px-6 py-4 text-sm text-gray-400 font-mono">{{ ($items->currentPage() - 1) * $items->perPage() + $loop->iteration }}</td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $item->warehouse->name }}</td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-semibold text-gray-900">{{ $item->product->name }}</p>
                                        <p class="text-xs text-gray-400 font-mono">{{ $item->product->sku }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-sm font-semibold {{ $item->current_stock <= $item->reorder_point ? 'text-red-600' : 'text-gray-700' }}">{{ number_format($item->current_stock) }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 bg-amber-50 text-amber-700 text-xs font-semibold rounded-lg border border-amber-200/50">{{ number_format($item->reorder_point) }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if ($item->status === 'normal')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-lg"><span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>Normal</span>
                                        @elseif ($item->status === 'low_stock')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-red-50 text-red-700 text-xs font-semibold rounded-lg"><span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>Low Stock</span>
                                        @elseif ($item->status === 'on_order')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-blue-50 text-blue-700 text-xs font-semibold rounded-lg"><span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>On Order</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="inline-flex items-center gap-1">
                                            <button wire:click="openEdit({{ $item->id }})" class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                            <button wire:click="confirmDelete({{ $item->id }})" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-6 py-16 text-center text-sm text-gray-500">Tidak ada item ditemukan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="bg-gray-50 border-t border-gray-100 px-6 py-3">
                    <p class="text-xs text-gray-400">Total <span class="font-semibold text-gray-600">{{ $items->total() }}</span> item · ROP dihitung otomatis: (Rata-rata Penggunaan × Lead Time) + Safety Stock</p>
                </div>
                @if ($items->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100">
                        {{ $items->links('vendor.pagination.livewire-light') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Create/Edit Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500/75" wire:click="$set('showModal', false)"></div>
                <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-8 z-10">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">{{ $editingId ? 'Edit Stok Gudang' : 'Tambah Produk ke Gudang' }}</h3>
                    <form wire:submit="save" class="space-y-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Gudang <span class="text-red-500">*</span></label>
                                @if ($isAdminUp3)
                                    <input type="text" value="{{ Auth::user()->warehouse->name ?? 'Gudang Anda' }}" class="w-full px-4 py-3 text-sm border border-gray-200 bg-gray-50 rounded-lg text-gray-500" disabled/>
                                @else
                                    <select wire:model="warehouse_id" class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500" {{ $editingId ? 'disabled' : '' }}>
                                        <option value="">Pilih Gudang</option>
                                        @foreach ($warehouses as $wh)
                                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                        @endforeach
                                    </select>
                                @endif
                                @error('warehouse_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Produk <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="product_name" class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500" placeholder="Ketik nama produk..." {{ $editingId ? 'disabled' : '' }}/>
                                @error('product_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        @unless ($editingId)
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">SKU <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="product_sku" class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-mono" placeholder="Contoh: KBL-NFA2X-070"/>
                                @error('product_sku') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Satuan <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="product_unit" class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500" placeholder="meter, buah, unit"/>
                                @error('product_unit') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <p class="text-xs text-gray-400 bg-blue-50 p-3 rounded-lg">💡 Jika nama produk sudah ada di database, SKU & satuan akan diabaikan dan produk yang sudah ada akan digunakan.</p>
                        @endunless
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Stok Saat Ini <span class="text-red-500">*</span></label>
                            <input type="number" wire:model="current_stock" min="0" class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"/>
                            @error('current_stock') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        {{-- ROP Mode Toggle --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Mode ROP <span class="text-red-500">*</span></label>
                            <div class="flex rounded-lg border border-gray-300 overflow-hidden">
                                <button type="button" wire:click="$set('rop_mode', 'auto')"
                                    class="flex-1 px-4 py-2.5 text-sm font-medium transition-colors {{ $rop_mode === 'auto' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50' }}">
                                    🔄 Otomatis
                                </button>
                                <button type="button" wire:click="$set('rop_mode', 'manual')"
                                    class="flex-1 px-4 py-2.5 text-sm font-medium transition-colors border-l border-gray-300 {{ $rop_mode === 'manual' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50' }}">
                                    ✏️ Manual
                                </button>
                            </div>
                        </div>

                        @if ($rop_mode === 'auto')
                        {{-- Auto ROP Fields --}}
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Rata-rata Harian <span class="text-red-500">*</span></label>
                                <input type="number" wire:model="avg_daily_usage" step="0.01" min="0.01" class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"/>
                                @error('avg_daily_usage') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Lead Time (hari) <span class="text-red-500">*</span></label>
                                <input type="number" wire:model="lead_time" min="1" class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"/>
                                @error('lead_time') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Safety Stock <span class="text-red-500">*</span></label>
                                <input type="number" wire:model="safety_stock" min="0" class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"/>
                                @error('safety_stock') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <p class="text-xs text-gray-400 bg-gray-50 p-3 rounded-lg">💡 ROP dihitung otomatis: (Rata-rata Harian × Lead Time) + Safety Stock.</p>
                        @else
                        {{-- Manual ROP Field --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Nilai ROP <span class="text-red-500">*</span></label>
                            <input type="number" wire:model="manual_rop" min="1" class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500" placeholder="Masukkan nilai ROP tetap"/>
                            @error('manual_rop') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <p class="text-xs text-gray-400 bg-gray-50 p-3 rounded-lg">💡 ROP menggunakan nilai tetap yang Anda masukkan. Stok di bawah ROP akan ditandai sebagai low stock.</p>
                        @endif
                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                            <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Batal</button>
                            <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">{{ $editingId ? 'Perbarui' : 'Simpan' }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500/75" wire:click="$set('showDeleteModal', false)"></div>
                <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-8 z-10 text-center">
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Hapus Item?</h3>
                    <p class="text-sm text-gray-500 mb-6">Produk akan dihapus dari gudang ini. Data stok akan hilang.</p>
                    <div class="flex justify-center gap-3">
                        <button wire:click="$set('showDeleteModal', false)" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Batal</button>
                        <button wire:click="delete" class="px-5 py-2.5 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors shadow-sm">Ya, Hapus</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
