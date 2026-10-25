<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Admin User
        DB::table('users')->insertOrIgnore([
            'name' => 'Admin User',
            'email' => 'admin@khelostore.com',
            'password' => Hash::make('password123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Seed Categories
        $kosCategoryId = DB::table('categories')->insertGetId([
            'name' => 'KOS',
            'slug' => Str::slug('KOS'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $snakeCategoryId = DB::table('categories')->insertGetId([
            'name' => 'Snake',
            'slug' => Str::slug('Snake'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Seed Products
        $carromId = DB::table('products')->insertGetId([
            'name' => 'Carrom Pool Premium',
            'category_id' => $kosCategoryId,
            'price_1d' => 5.00,
            'price_7d' => 15.00,
            'price_15d' => 25.00,
            'price_30d' => 45.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $eightBallId = DB::table('products')->insertGetId([
            'name' => '8 Ball Pool Premium',
            'category_id' => $kosCategoryId,
            'price_1d' => 4.00,
            'price_7d' => 12.00,
            'price_15d' => 22.00,
            'price_30d' => 40.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $snakeId = DB::table('products')->insertGetId([
            'name' => 'Snake Premium',
            'category_id' => $snakeCategoryId,
            'price_1d' => 6.00,
            'price_7d' => 18.00,
            'price_15d' => 30.00,
            'price_30d' => 55.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Seed Keys matching screenshot stock counts
        // Carrom Pool keys: 17x 1d, 18x 7d, 4x 15d, 5x 30d
        $this->seedKeysForProduct($carromId, '1d', 17, 'CP-1D');
        $this->seedKeysForProduct($carromId, '7d', 18, 'CP-7D');
        $this->seedKeysForProduct($carromId, '15d', 4, 'CP-15D');
        $this->seedKeysForProduct($carromId, '30d', 5, 'CP-30D');

        // Snake keys: 10x 1d, 5x 7d, 2x 15d, 1x 30d
        $this->seedKeysForProduct($snakeId, '1d', 10, 'SN-1D');
        $this->seedKeysForProduct($snakeId, '7d', 5, 'SN-7D');
        $this->seedKeysForProduct($snakeId, '15d', 2, 'SN-15D');
        $this->seedKeysForProduct($snakeId, '30d', 1, 'SN-30D');
    }

    /**
     * Helper to seed keys for a product.
     */
    protected function seedKeysForProduct(int $productId, string $duration, int $count, string $prefix): void
    {
        $keys = [];
        for ($i = 1; $i <= $count; $i++) {
            $keys[] = [
                'product_id' => $productId,
                'duration' => $duration,
                'license_key' => $prefix . '-' . strtoupper(bin2hex(random_bytes(6))),
                'is_sold' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($keys)) {
            DB::table('keys')->insert($keys);
        }
    }
}
