<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Waktu Pengecekan Stok Harian
    |--------------------------------------------------------------------------
    |
    | Waktu (format HH:MM, zona WIB) ketika sistem mengecek seluruh
    | warehouse_products dan mengirim email rangkuman stok rendah
    | ke semua user dengan role "manager".
    |
    | Ubah nilai STOCK_CHECK_TIME di file .env untuk mengatur waktu.
    | Contoh: "17:00", "08:00", "21:30"
    |
    */

    'check_time' => env('STOCK_CHECK_TIME', '17:00'),

];
