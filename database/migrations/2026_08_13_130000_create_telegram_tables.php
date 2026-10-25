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
        // 1. Telegram Users
        Schema::create('telegram_users', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('telegram_id')->unique();
            $table->string('username')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->decimal('balance', 10, 2)->default(0.00);
            $table->boolean('is_blocked')->default(false);
            $table->timestamps();
        });

        // 2. Products
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->nullable(); // KOS, Snake, etc.
            $table->decimal('price_1d', 10, 2)->default(0.00);
            $table->decimal('price_3d', 10, 2)->default(0.00);
            $table->decimal('price_7d', 10, 2)->default(0.00);
            $table->decimal('price_10d', 10, 2)->default(0.00);
            $table->decimal('price_15d', 10, 2)->default(0.00);
            $table->decimal('price_30d', 10, 2)->default(0.00);
            $table->decimal('price_90d', 10, 2)->default(0.00);
            $table->decimal('price_lifetime', 10, 2)->default(0.00);
            $table->timestamps();
        });

        // 3. License Keys
        Schema::create('keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('duration'); // 1d, 7d, 15d, 30d
            $table->text('license_key');
            $table->boolean('is_sold')->default(false);
            $table->foreignId('telegram_user_id')->nullable()->constrained('telegram_users')->onDelete('set null');
            $table->timestamp('sold_at')->nullable();
            $table->timestamps();
        });

        // 4. Ledger Transactions
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('telegram_user_id')->constrained('telegram_users')->onDelete('cascade');
            $table->string('type'); // credit, debit
            $table->decimal('amount', 10, 2);
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('keys');
        Schema::dropIfExists('products');
        Schema::dropIfExists('telegram_users');
    }
};
