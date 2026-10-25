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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('price_3d_queue', 10, 2)->default(0.00)->after('price_3d');
            $table->decimal('price_7d_queue', 10, 2)->default(0.00)->after('price_7d');
            $table->decimal('price_30d_queue', 10, 2)->default(0.00)->after('price_30d');
            $table->decimal('price_90d_queue', 10, 2)->default(0.00)->after('price_90d');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['price_3d_queue', 'price_7d_queue', 'price_30d_queue', 'price_90d_queue']);
        });
    }
};
