<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('warehouse_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('current_stock')->default(0);
            $table->enum('status', ['normal', 'low_stock', 'on_order'])->default('normal');
            $table->decimal('avg_daily_usage', 10, 2)->default(0);
            $table->integer('lead_time')->default(0);
            $table->integer('safety_stock')->default(0);
            $table->integer('reorder_point')->default(0);
            $table->timestamps();

            $table->unique(['warehouse_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_products');
    }
};
