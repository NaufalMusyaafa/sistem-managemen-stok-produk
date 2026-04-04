<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Delete all old procurement records (old statuses will be cleaned)
        DB::table('procurements')->delete();

        // 2. Modify the enum column to the new simplified set
        // MySQL requires re-creating the column definition
        DB::statement("ALTER TABLE procurements MODIFY COLUMN status ENUM('ordered','received','canceled') NOT NULL DEFAULT 'ordered'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE procurements MODIFY COLUMN status ENUM('pending','approved','ordered','received','cancelled','expired') NOT NULL DEFAULT 'pending'");
    }
};
