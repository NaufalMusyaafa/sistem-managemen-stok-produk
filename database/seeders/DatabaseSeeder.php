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
        // 2. PRODUCTS (50) — Electrical/Cable supplies
        // ──────────────────────────────────────────────
        $productData = [
            // Kabel
            ['sku' => 'KBL-NFA2X-070', 'name' => 'Kabel NFA2X 2x70mm²',          'unit' => 'meter'],
            ['sku' => 'KBL-NFA2X-050', 'name' => 'Kabel NFA2X 2x50mm²',          'unit' => 'meter'],
            ['sku' => 'KBL-NFA2X-035', 'name' => 'Kabel NFA2X 2x35mm²',          'unit' => 'meter'],
            ['sku' => 'KBL-AAAC-150',  'name' => 'Kabel AAAC 150mm²',             'unit' => 'meter'],
            ['sku' => 'KBL-AAAC-070',  'name' => 'Kabel AAAC 70mm²',              'unit' => 'meter'],
            ['sku' => 'KBL-ACSR-150',  'name' => 'Kabel ACSR 150mm²',             'unit' => 'meter'],
            ['sku' => 'KBL-NYY-4X16',  'name' => 'Kabel NYY 4x16mm²',            'unit' => 'meter'],
            ['sku' => 'KBL-NYY-4X10',  'name' => 'Kabel NYY 4x10mm²',            'unit' => 'meter'],
            ['sku' => 'KBL-NYA-25',    'name' => 'Kabel NYA 1x2.5mm²',           'unit' => 'meter'],
            ['sku' => 'KBL-NYFGBY-4X', 'name' => 'Kabel NYFGBY 4x240mm²',        'unit' => 'meter'],

            // Tiang & Aksesoris
            ['sku' => 'TNG-BTN-9M',    'name' => 'Tiang Beton 9 Meter',            'unit' => 'batang'],
            ['sku' => 'TNG-BTN-11M',   'name' => 'Tiang Beton 11 Meter',           'unit' => 'batang'],
            ['sku' => 'TNG-BTN-12M',   'name' => 'Tiang Beton 12 Meter',           'unit' => 'batang'],
            ['sku' => 'TNG-BSI-7M',    'name' => 'Tiang Besi 7 Meter',             'unit' => 'batang'],
            ['sku' => 'CRH-ARM-1500',  'name' => 'Cross Arm 1500mm',               'unit' => 'buah'],

            // Trafo
            ['sku' => 'TRF-DIST-025',  'name' => 'Trafo Distribusi 25 KVA',        'unit' => 'unit'],
            ['sku' => 'TRF-DIST-050',  'name' => 'Trafo Distribusi 50 KVA',        'unit' => 'unit'],
            ['sku' => 'TRF-DIST-100',  'name' => 'Trafo Distribusi 100 KVA',       'unit' => 'unit'],
            ['sku' => 'TRF-DIST-160',  'name' => 'Trafo Distribusi 160 KVA',       'unit' => 'unit'],
            ['sku' => 'TRF-DIST-200',  'name' => 'Trafo Distribusi 200 KVA',       'unit' => 'unit'],

            // Isolator & Arrester
            ['sku' => 'ISO-PIN-20KV',  'name' => 'Isolator Pin 20 KV',             'unit' => 'buah'],
            ['sku' => 'ISO-SUSP-20KV', 'name' => 'Isolator Suspension 20 KV',      'unit' => 'buah'],
            ['sku' => 'ISO-TUMPU-20K', 'name' => 'Isolator Tumpu 20 KV',           'unit' => 'buah'],
            ['sku' => 'ARR-LA-20KV',   'name' => 'Lightning Arrester 20 KV',       'unit' => 'buah'],
            ['sku' => 'ARR-LA-04KV',   'name' => 'Lightning Arrester 0.4 KV',      'unit' => 'buah'],

            // Connector & Clamp
            ['sku' => 'CON-PIR-CLP',   'name' => 'Piercing Connector',             'unit' => 'buah'],
            ['sku' => 'CON-PRESS-070', 'name' => 'Press Connector 70mm²',          'unit' => 'buah'],
            ['sku' => 'CON-PRESS-150', 'name' => 'Press Connector 150mm²',         'unit' => 'buah'],
            ['sku' => 'CLP-HOTLINE',   'name' => 'Hot Line Clamp',                 'unit' => 'buah'],
            ['sku' => 'CLP-DEAD-END',  'name' => 'Dead End Clamp',                 'unit' => 'buah'],

            // Fuse & Switchgear
            ['sku' => 'FUS-CUT-OUT',   'name' => 'Fuse Cut Out 20 KV',             'unit' => 'buah'],
            ['sku' => 'FUS-LINK-10A',  'name' => 'Fuse Link 10A',                  'unit' => 'buah'],
            ['sku' => 'FUS-LINK-06A',  'name' => 'Fuse Link 6A',                   'unit' => 'buah'],
            ['sku' => 'LBS-20KV',      'name' => 'Load Break Switch 20 KV',        'unit' => 'unit'],
            ['sku' => 'RCLS-20KV',     'name' => 'Recloser 20 KV',                 'unit' => 'unit'],

            // Meter & Proteksi
            ['sku' => 'MTR-KWH-1PH',  'name' => 'KWH Meter 1 Phase',              'unit' => 'buah'],
            ['sku' => 'MTR-KWH-3PH',  'name' => 'KWH Meter 3 Phase',              'unit' => 'buah'],
            ['sku' => 'MCB-1PH-16A',  'name' => 'MCB 1 Phase 16A',                'unit' => 'buah'],
            ['sku' => 'MCB-3PH-32A',  'name' => 'MCB 3 Phase 32A',                'unit' => 'buah'],
            ['sku' => 'CT-RATIO-200', 'name' => 'Current Transformer Ratio 200/5', 'unit' => 'buah'],

            // Perlengkapan Kerja
            ['sku' => 'APD-HELM-STD',  'name' => 'Helm Safety Standar PLN',        'unit' => 'buah'],
            ['sku' => 'APD-GLOVE-20K', 'name' => 'Sarung Tangan Isolasi 20 KV',    'unit' => 'pasang'],
            ['sku' => 'APD-BOOT-20K',  'name' => 'Sepatu Safety Isolasi 20 KV',    'unit' => 'pasang'],
            ['sku' => 'TLS-STIK-20K',  'name' => 'Stick 20 KV',                    'unit' => 'buah'],
            ['sku' => 'TLS-TANG-KBEL', 'name' => 'Tang Kabel Hidrolik',            'unit' => 'unit'],

            // Aksesoris Jaringan
            ['sku' => 'ACC-BAND-STAI', 'name' => 'Stainless Steel Banding',        'unit' => 'roll'],
            ['sku' => 'ACC-GUY-WIRE',  'name' => 'Guy Wire 50mm²',                 'unit' => 'meter'],
            ['sku' => 'ACC-GROUND-RD', 'name' => 'Ground Rod 5/8" x 2.4m',        'unit' => 'batang'],
            ['sku' => 'ACC-CLAMP-GRD', 'name' => 'Clamp Grounding',                'unit' => 'buah'],
            ['sku' => 'ACC-BOLT-CARR', 'name' => 'Carriage Bolt 16x250mm',        'unit' => 'buah'],
        ];

        $products = collect();
        foreach ($productData as $data) {
            $products->push(Product::create($data));
        }

        $this->command->info('✔ 50 Products created.');

        // ──────────────────────────────────────────────
        // 3. USERS (3)
        // ──────────────────────────────────────────────
        $defaultPassword = Hash::make('password');

        User::create([
            'name'         => 'Admin UP3 Medan',
            'email'        => 'admin.medan@test.com',
            'password'     => $defaultPassword,
            'role'         => 'admin_up3',
            'warehouse_id' => $warehouses->firstWhere('name', 'Gudang UP3 Medan')->id,
        ]);

        User::create([
            'name'         => 'Admin UP3 Binjai',
            'email'        => 'admin.binjai@test.com',
            'password'     => $defaultPassword,
            'role'         => 'admin_up3',
            'warehouse_id' => $warehouses->firstWhere('name', 'Gudang UP3 Binjai')->id,
        ]);

        User::create([
            'name'         => 'Admin UP3 Lubuk Pakam',
            'email'        => 'admin.lubukpakam@test.com',
            'password'     => $defaultPassword,
            'role'         => 'admin_up3',
            'warehouse_id' => $warehouses->firstWhere('name', 'Gudang UP3 Lubuk Pakam')->id,
        ]);

        User::create([
            'name'         => 'Admin UP3 Bukit Barisan',
            'email'        => 'admin.bukitbarisan@test.com',
            'password'     => $defaultPassword,
            'role'         => 'admin_up3',
            'warehouse_id' => $warehouses->firstWhere('name', 'Gudang UP3 Bukit Barisan')->id,
        ]);

        User::create([
            'name'         => 'Admin UP3 Medan Utara',
            'email'        => 'admin.medanutara@test.com',
            'password'     => $defaultPassword,
            'role'         => 'admin_up3',
            'warehouse_id' => $warehouses->firstWhere('name', 'Gudang UP3 Medan Utara')->id,
        ]);

        User::create([
            'name'         => 'Admin UP3 Nias',
            'email'        => 'admin.nias@test.com',
            'password'     => $defaultPassword,
            'role'         => 'admin_up3',
            'warehouse_id' => $warehouses->firstWhere('name', 'Gudang UP3 Nias')->id,
        ]);

        User::create([
            'name'         => 'Admin UP3 Padangsidimpuan',
            'email'        => 'admin.padangsidimpuan@test.com',
            'password'     => $defaultPassword,
            'role'         => 'admin_up3',
            'warehouse_id' => $warehouses->firstWhere('name', 'Gudang UP3 Padangsidimpuan')->id,
        ]);

        User::create([
            'name'         => 'Admin UP3 Pematang Siantar',
            'email'        => 'admin.pematangsiantar@test.com',
            'password'     => $defaultPassword,
            'role'         => 'admin_up3',
            'warehouse_id' => $warehouses->firstWhere('name', 'Gudang UP3 Pematang Siantar')->id,
        ]);

        User::create([
            'name'         => 'Admin UP3 Rantau Prapat',
            'email'        => 'admin.rantauprapat@test.com',
            'password'     => $defaultPassword,
            'role'         => 'admin_up3',
            'warehouse_id' => $warehouses->firstWhere('name', 'Gudang UP3 Rantau Prapat')->id,
        ]);

        User::create([
            'name'         => 'Admin UP3 Sibolga',
            'email'        => 'admin.sibolga@test.com',
            'password'     => $defaultPassword,
            'role'         => 'admin_up3',
            'warehouse_id' => $warehouses->firstWhere('name', 'Gudang UP3 Sibolga')->id,
        ]);

        User::create([
            'name'         => 'Admin UID',
            'email'        => 'admin.uid@test.com',
            'password'     => $defaultPassword,
            'role'         => 'admin_uid',
            'warehouse_id' => null,
        ]);

        User::create([
            'name'         => 'Admin Manager',
            'email'        => 'admin.manager@test.com',
            'password'     => $defaultPassword,
            'role'         => 'manager',
            'warehouse_id' => null,
        ]);

        User::create([
            'name'         => 'Admin Rent',
            'email'        => 'admin.rent@test.com',
            'password'     => $defaultPassword,
            'role'         => 'rent',
            'warehouse_id' => null,
        ]);

        $this->command->info('✔ 13 Users created (password: "password").');

        // ──────────────────────────────────────────────
        // 4. PIVOT SEEDING — warehouse_products (10 per warehouse)
        //    70% Normal | 20% Low Stock | 10% On Order
        // ──────────────────────────────────────────────
        $totalPivots = 0;
        $itemsPerWarehouse = 10;

        foreach ($warehouses as $warehouse) {
            // Pick 10 random products for this warehouse
            $selectedProducts = $products->random($itemsPerWarehouse);

            foreach ($selectedProducts as $product) {
                // Determine status based on distribution
                $rand = mt_rand(1, 100);
                if ($rand <= 70) {
                    $desiredStatus = 'normal';
                } elseif ($rand <= 90) {
                    $desiredStatus = 'low_stock';
                } else {
                    $desiredStatus = 'on_order';
                }

                // Realistic ROP parameters
                $avgDailyUsage = round(mt_rand(1, 30) + (mt_rand(0, 99) / 100), 2);
                $leadTime      = mt_rand(3, 30);
                $safetyStock   = mt_rand(10, 100);

                // ROP = (avg_daily_usage * lead_time) + safety_stock
                $reorderPoint = (int) ceil(($avgDailyUsage * $leadTime) + $safetyStock);

                // Set current_stock based on desired status
                switch ($desiredStatus) {
                    case 'normal':
                        $currentStock = mt_rand($reorderPoint + 1, $reorderPoint * 3);
                        break;

                    case 'low_stock':
                        $currentStock = mt_rand(1, max(1, $reorderPoint - 1));
                        break;

                    case 'on_order':
                        $currentStock = mt_rand(0, max(1, (int) ($reorderPoint * 0.2)));
                        break;
                }

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

                $totalPivots++;
            }
        }

        $this->command->info("✔ {$totalPivots} Warehouse-Product entries created.");

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
                ['Warehouses', $warehouses->count()],
                ['Products', $products->count()],
                ['Users', 13],
                ['Warehouse-Product Pivots', $totalPivots],
            ]
        );
    }
}
