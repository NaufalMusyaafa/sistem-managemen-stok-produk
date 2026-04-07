<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseProduct;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ──────────────────────────────────────────────
        // 1. WAREHOUSES (10) — Sesuai Pola Organisasi PLN UID Sumatera Utara
        // ──────────────────────────────────────────────
        $warehouseNames = [
            ['name' => 'Gudang UP3 Binjai',             'location' => 'Kota Binjai, Sumatera Utara'],
            ['name' => 'Gudang UP3 Lubuk Pakam',        'location' => 'Kab. Deli Serdang, Sumatera Utara'],
            ['name' => 'Gudang UP3 Bukit Barisan',      'location' => 'Kab. Karo, Sumatera Utara'],
            ['name' => 'Gudang UP3 Medan',              'location' => 'Kota Medan, Sumatera Utara'],
            ['name' => 'Gudang UP3 Medan Utara',        'location' => 'Kota Medan, Sumatera Utara'],
            ['name' => 'Gudang UP3 Nias',               'location' => 'Kota Gunung Sitoli, Sumatera Utara'],
            ['name' => 'Gudang UP3 Padangsidimpuan',    'location' => 'Kota Padang Sidempuan, Sumatera Utara'],
            ['name' => 'Gudang UP3 Pematang Siantar',   'location' => 'Kota Pematang Siantar, Sumatera Utara'],
            ['name' => 'Gudang UP3 Rantau Prapat',      'location' => 'Kab. Labuhanbatu, Sumatera Utara'],
            ['name' => 'Gudang UP3 Sibolga',            'location' => 'Kota Sibolga, Sumatera Utara'],
        ];

        $warehouses = collect();
        foreach ($warehouseNames as $data) {
            $warehouses->push(Warehouse::create($data));
        }

        $this->command->info('✔ 10 Warehouses created.');

        // ──────────────────────────────────────────────
        // 2. PRODUCTS (10) — Material utama distribusi PLN
        // ──────────────────────────────────────────────
        $productData = [
            ['sku' => 'KBL-NFA2X-070', 'name' => 'Kabel NFA2X 2x70mm²',       'unit' => 'meter'],
            ['sku' => 'KBL-AAAC-070',  'name' => 'Kabel AAAC 70mm²',           'unit' => 'meter'],
            ['sku' => 'KBL-NYA-25',    'name' => 'Kabel NYA 1x2.5mm²',         'unit' => 'meter'],
            ['sku' => 'TRF-DIST-100',  'name' => 'Trafo Distribusi 100 KVA',   'unit' => 'unit'],
            ['sku' => 'ISO-PIN-20KV',  'name' => 'Isolator Pin 20 KV',         'unit' => 'buah'],
            ['sku' => 'CLP-HOTLINE',   'name' => 'Hot Line Clamp',             'unit' => 'buah'],
            ['sku' => 'LBS-20KV',      'name' => 'Load Break Switch 20 KV',    'unit' => 'unit'],
            ['sku' => 'APD-HELM-STD',  'name' => 'Helm Safety Standar PLN',    'unit' => 'buah'],
            ['sku' => 'FUS-CUT-OUT',   'name' => 'Fuse Cut Out 20 KV',         'unit' => 'buah'],
            ['sku' => 'ACC-BOLT-CARR', 'name' => 'Carriage Bolt 16x250mm',     'unit' => 'buah'],
        ];

        $products = collect();
        foreach ($productData as $data) {
            $products->push(Product::create($data));
        }

        $this->command->info('✔ 10 Products created.');

        // ──────────────────────────────────────────────
        // 3. USERS — 1 admin_up3 per gudang + 3 role lain
        // ──────────────────────────────────────────────
        $defaultPassword = Hash::make('password');

        $adminUp3List = [
            ['name' => 'Admin UP3 Binjai',           'email' => 'admin.binjai@test.com',          'warehouse' => 'Gudang UP3 Binjai'],
            ['name' => 'Admin UP3 Lubuk Pakam',      'email' => 'admin.lubukpakam@test.com',      'warehouse' => 'Gudang UP3 Lubuk Pakam'],
            ['name' => 'Admin UP3 Bukit Barisan',    'email' => 'admin.bukitbarisan@test.com',    'warehouse' => 'Gudang UP3 Bukit Barisan'],
            ['name' => 'Admin UP3 Medan',            'email' => 'admin.medan@test.com',           'warehouse' => 'Gudang UP3 Medan'],
            ['name' => 'Admin UP3 Medan Utara',      'email' => 'admin.medanutara@test.com',      'warehouse' => 'Gudang UP3 Medan Utara'],
            ['name' => 'Admin UP3 Nias',             'email' => 'admin.nias@test.com',            'warehouse' => 'Gudang UP3 Nias'],
            ['name' => 'Admin UP3 Padangsidimpuan',  'email' => 'admin.padangsidimpuan@test.com', 'warehouse' => 'Gudang UP3 Padangsidimpuan'],
            ['name' => 'Admin UP3 Pematang Siantar', 'email' => 'admin.pematangsiantar@test.com', 'warehouse' => 'Gudang UP3 Pematang Siantar'],
            ['name' => 'Admin UP3 Rantau Prapat',    'email' => 'admin.rantauprapat@test.com',    'warehouse' => 'Gudang UP3 Rantau Prapat'],
            ['name' => 'Admin UP3 Sibolga',          'email' => 'admin.sibolga@test.com',         'warehouse' => 'Gudang UP3 Sibolga'],
        ];

        foreach ($adminUp3List as $admin) {
            User::create([
                'name'         => $admin['name'],
                'email'        => $admin['email'],
                'password'     => $defaultPassword,
                'role'         => 'admin_up3',
                'warehouse_id' => $warehouses->firstWhere('name', $admin['warehouse'])->id,
            ]);
        }

        User::create(['name' => 'Admin UID',     'email' => 'admin.uid@test.com',     'password' => $defaultPassword, 'role' => 'admin_uid', 'warehouse_id' => null]);
        User::create(['name' => 'Admin Manager', 'email' => 'admin.manager@test.com', 'password' => $defaultPassword, 'role' => 'manager',   'warehouse_id' => null]);
        User::create(['name' => 'Admin Rent',    'email' => 'admin.rent@test.com',    'password' => $defaultPassword, 'role' => 'rent',      'warehouse_id' => null]);

        $this->command->info('✔ 13 Users created (password: "password").');

        // ──────────────────────────────────────────────
        // 4. WAREHOUSE PRODUCTS — 10 item per gudang
        //    Distribusi: 70% Normal | 20% Low Stock | 10% On Order
        // ──────────────────────────────────────────────
        $totalItems = 0;

        foreach ($warehouses as $warehouse) {
            foreach ($products as $product) {
                $rand = mt_rand(1, 100);
                $desiredStatus = match (true) {
                    $rand <= 70 => 'normal',
                    $rand <= 90 => 'low_stock',
                    default     => 'on_order',
                };

                $avgDailyUsage = round(mt_rand(1, 30) + (mt_rand(0, 99) / 100), 2);
                $leadTime      = mt_rand(3, 30);
                $safetyStock   = mt_rand(10, 100);
                $reorderPoint  = (int) ceil(($avgDailyUsage * $leadTime) + $safetyStock);

                $currentStock = match ($desiredStatus) {
                    'normal'    => mt_rand($reorderPoint + 1, $reorderPoint * 3),
                    'low_stock' => mt_rand(1, max(1, $reorderPoint - 1)),
                    'on_order'  => mt_rand(0, max(1, (int) ($reorderPoint * 0.2))),
                };

                WarehouseProduct::create([
                    'warehouse_id'    => $warehouse->id,
                    'product_id'      => $product->id,
                    'current_stock'   => $currentStock,
                    'status'          => $desiredStatus,
                    'avg_daily_usage' => $avgDailyUsage,
                    'lead_time'       => $leadTime,
                    'safety_stock'    => $safetyStock,
                    'reorder_point'   => $reorderPoint,
                ]);

                $totalItems++;
            }
        }

        $this->command->info("✔ {$totalItems} Warehouse-Product entries created (10 per warehouse).");

        // ──────────────────────────────────────────────
        // Summary
        // ──────────────────────────────────────────────
        $this->command->newLine();
        $this->command->info('══════════════════════════════════════');
        $this->command->info('  SEEDING COMPLETE');
        $this->command->info('══════════════════════════════════════');
        $this->command->table(
            ['Entity', 'Count'],
            [
                ['Warehouses',              $warehouses->count()],
                ['Products',                $products->count()],
                ['Users',                   13],
                ['Warehouse-Product Items', $totalItems],
            ]
        );
    }
}
