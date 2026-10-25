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
        if (!Schema::hasTable('user_product_prices')) {
            Schema::create('user_product_prices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('telegram_user_id')->nullable()->constrained('telegram_users')->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
                $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
                $table->string('duration')->nullable(); // 1d, 3d, 7d, 10d, 15d, 30d, 90d, lifetime
                $table->decimal('price', 10, 2);
                $table->timestamps();

                $table->index(['telegram_user_id', 'product_id', 'duration']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_product_prices');
    }
};
