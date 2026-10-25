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
            $table->decimal('cost_1d', 10, 2)->nullable()->after('price_1d');
            $table->decimal('cost_3d', 10, 2)->nullable()->after('price_3d');
            $table->decimal('cost_3d_queue', 10, 2)->nullable()->after('price_3d_queue');
            $table->decimal('cost_7d', 10, 2)->nullable()->after('price_7d');
            $table->decimal('cost_7d_queue', 10, 2)->nullable()->after('price_7d_queue');
            $table->decimal('cost_10d', 10, 2)->nullable()->after('price_10d');
            $table->decimal('cost_15d', 10, 2)->nullable()->after('price_15d');
            $table->decimal('cost_30d', 10, 2)->nullable()->after('price_30d');
            $table->decimal('cost_30d_queue', 10, 2)->nullable()->after('price_30d_queue');
            $table->decimal('cost_90d', 10, 2)->nullable()->after('price_90d');
            $table->decimal('cost_90d_queue', 10, 2)->nullable()->after('price_90d_queue');
            $table->decimal('cost_lifetime', 10, 2)->nullable()->after('price_lifetime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'cost_1d',
                'cost_3d',
                'cost_3d_queue',
                'cost_7d',
                'cost_7d_queue',
                'cost_10d',
                'cost_15d',
                'cost_30d',
                'cost_30d_queue',
                'cost_90d',
                'cost_90d_queue',
                'cost_lifetime',
            ]);
        });
    }
};
