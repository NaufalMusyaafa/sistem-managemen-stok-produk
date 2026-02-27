<?php

namespace App\Console\Commands;

use App\Mail\LowStockAlert;
use App\Models\User;
use App\Models\WarehouseProduct;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckLowStock extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'stock:check-low';

    /**
     * The console command description.
     */
    protected $description = 'Cek seluruh warehouse_products, kirim email rangkuman stok rendah ke manager jika ada item dengan Stok < ROP dan status bukan on_order';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Mengecek stok rendah di seluruh gudang...');
        $this->newLine();

        // ──────────────────────────────────────────────
        // 1. Query: Stok < ROP AND status != on_order
        // ──────────────────────────────────────────────
        $lowStockItems = WarehouseProduct::withoutGlobalScopes()
            ->with(['product', 'warehouse'])
            ->whereColumn('current_stock', '<', 'reorder_point')
            ->where('status', '!=', 'on_order')
            ->get();

        if ($lowStockItems->isEmpty()) {
            $this->info('✅ Tidak ada item dengan stok rendah. Tidak ada email yang dikirim.');
            return self::SUCCESS;
        }

        // ──────────────────────────────────────────────
        // 2. Format data, kelompokkan per gudang
        // ──────────────────────────────────────────────
        $grouped = $lowStockItems
            ->map(function ($item) {
                return [
                    'warehouse_name' => $item->warehouse->name,
                    'product_name'   => $item->product->name,
                    'product_sku'    => $item->product->sku,
                    'current_stock'  => $item->current_stock,
                    'reorder_point'  => $item->reorder_point,
                    'deficit'        => $item->reorder_point - $item->current_stock,
                ];
            })
            ->groupBy('warehouse_name');

        $totalItems = $lowStockItems->count();

        $this->warn("⚠️  Ditemukan {$totalItems} item stok rendah di {$grouped->count()} gudang.");
        $this->newLine();

        // Show summary table in console
        $this->table(
            ['Gudang', 'Jumlah Item'],
            $grouped->map(fn ($items, $name) => [$name, count($items)])->values()->toArray()
        );

        // ──────────────────────────────────────────────
        // 3. Kirim email ke semua manager
        // ──────────────────────────────────────────────
        $managers = User::where('role', 'manager')->get();

        if ($managers->isEmpty()) {
            $this->error('❌ Tidak ada user dengan role "manager". Email tidak dikirim.');
            return self::FAILURE;
        }

        $this->info("📧 Mengirim email ke {$managers->count()} manager...");

        foreach ($managers as $manager) {
            Mail::to($manager->email)->send(new LowStockAlert($grouped, $totalItems));
            $this->line("   ✉️  → {$manager->email} ({$manager->name})");
        }

        $this->newLine();
        $this->info('✅ Email berhasil dikirim!');

        return self::SUCCESS;
    }
}
