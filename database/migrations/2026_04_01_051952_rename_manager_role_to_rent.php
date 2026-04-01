<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'rent' and 'manager' to enum first
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin_up3', 'admin_uid', 'rent', 'manager') NOT NULL");

        // Rename all existing 'manager' users to 'rent'
        DB::table('users')->where('role', 'manager')->update(['role' => 'rent']);
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'rent')->update(['role' => 'manager']);
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin_up3', 'admin_uid', 'manager') NOT NULL");
    }
};
