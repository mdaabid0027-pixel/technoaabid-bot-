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
            $table->decimal('reseller_price_1d', 10, 2)->default(0.00)->after('cost_1d');
            $table->decimal('reseller_price_3d', 10, 2)->default(0.00)->after('cost_3d');
            $table->decimal('reseller_price_3d_queue', 10, 2)->default(0.00)->after('cost_3d_queue');
            $table->decimal('reseller_price_7d', 10, 2)->default(0.00)->after('cost_7d');
            $table->decimal('reseller_price_7d_queue', 10, 2)->default(0.00)->after('cost_7d_queue');
            $table->decimal('reseller_price_10d', 10, 2)->default(0.00)->after('cost_10d');
            $table->decimal('reseller_price_15d', 10, 2)->default(0.00)->after('cost_15d');
            $table->decimal('reseller_price_30d', 10, 2)->default(0.00)->after('cost_30d');
            $table->decimal('reseller_price_30d_queue', 10, 2)->default(0.00)->after('cost_30d_queue');
            $table->decimal('reseller_price_90d', 10, 2)->default(0.00)->after('cost_90d');
            $table->decimal('reseller_price_90d_queue', 10, 2)->default(0.00)->after('cost_90d_queue');
            $table->decimal('reseller_price_lifetime', 10, 2)->default(0.00)->after('cost_lifetime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'reseller_price_1d',
                'reseller_price_3d',
                'reseller_price_3d_queue',
                'reseller_price_7d',
                'reseller_price_7d_queue',
                'reseller_price_10d',
                'reseller_price_15d',
                'reseller_price_30d',
                'reseller_price_30d_queue',
                'reseller_price_90d',
                'reseller_price_90d_queue',
                'reseller_price_lifetime',
            ]);
        });
    }
};
