<div>
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Kelola Gudang') }}</h2>
        </div>
    </header>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari gudang..." class="pl-9 pr-4 py-2.5 text-sm bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all w-64"/>
                </div>
                <button wire:click="openCreate" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500/50 focus:ring-offset-2 transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Gudang
                </button>
            </div>

            @if ($successMessage)
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg">
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
                                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Nama Gudang</th>
                                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Lokasi</th>
                                <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Produk</th>
                                <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">User</th>
                                <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4 w-32">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($warehouses as $index => $warehouse)
                                <tr class="hover:bg-gray-50/50 transition-colors" wire:key="wh-{{ $warehouse->id }}">
                                    <td class="px-6 py-4 text-sm text-gray-400 font-mono">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $warehouse->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $warehouse->location }}</td>
                                    <td class="px-6 py-4 text-center"><span class="inline-flex items-center px-2.5 py-1 bg-indigo-50 text-indigo-700 text-xs font-bold rounded-lg">{{ $warehouse->warehouse_products_count }}</span></td>
                                    <td class="px-6 py-4 text-center"><span class="inline-flex items-center px-2.5 py-1 bg-gray-100 text-gray-600 text-xs font-bold rounded-lg">{{ $warehouse->users_count }}</span></td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="inline-flex items-center gap-1">
                                            <button wire:click="openEdit({{ $warehouse->id }})" class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                            <button wire:click="confirmDelete({{ $warehouse->id }})" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-6 py-16 text-center text-sm text-gray-500">Tidak ada gudang ditemukan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="bg-gray-50 border-t border-gray-100 px-6 py-3">
                    <p class="text-xs text-gray-400">Total <span class="font-semibold text-gray-600">{{ count($warehouses) }}</span> gudang</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Create/Edit Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500/75 transition-opacity" wire:click="$set('showModal', false)"></div>
                <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-8 z-10">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">{{ $editingId ? 'Edit Gudang' : 'Tambah Gudang Baru' }}</h3>
                    <form wire:submit="save" class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Gudang <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="name" class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500" placeholder="Contoh: Gudang UP3 Medan"/>
                            @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Lokasi <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="location" class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500" placeholder="Contoh: Medan, Sumatera Utara"/>
                            @error('location') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
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
                <div class="fixed inset-0 bg-gray-500/75 transition-opacity" wire:click="$set('showDeleteModal', false)"></div>
                <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-8 z-10 text-center">
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Hapus Gudang?</h3>
                    <p class="text-sm text-gray-500 mb-6">Gudang yang memiliki produk terdaftar tidak bisa dihapus.</p>
                    <div class="flex justify-center gap-3">
                        <button wire:click="$set('showDeleteModal', false)" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Batal</button>
                        <button wire:click="delete" class="px-5 py-2.5 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors shadow-sm">Ya, Hapus</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
