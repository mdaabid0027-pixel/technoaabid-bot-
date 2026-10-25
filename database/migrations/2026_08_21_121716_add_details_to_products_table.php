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
            if (!Schema::hasColumn('products', 'buying_price')) {
                $table->decimal('buying_price', 10, 2)->default(0.00)->after('price_lifetime');
            }
            if (!Schema::hasColumn('products', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('buying_price');
            }
            if (!Schema::hasColumn('products', 'game_name')) {
                $table->string('game_name')->nullable()->default('Carrom Pool')->after('name');
            }
            if (!Schema::hasColumn('products', 'image')) {
                $table->string('image')->nullable()->after('game_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['buying_price', 'is_active', 'game_name', 'image']);
        });
    }
};
