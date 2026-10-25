<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Key;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProductAndKeySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear old products and keys
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Key::truncate();
        Product::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $productsData = [
            [
                'name' => 'Aim Carrom King',
                'price_1d' => 25.00,
                'price_3d' => 35.00,
                'price_7d' => 55.00,
                'price_10d' => 75.00,
                'price_15d' => 100.00,
                'price_30d' => 150.00,
                'price_90d' => 350.00,
                'price_lifetime' => 699.00,
            ],
            [
                'name' => 'Snake Engine',
                'price_1d' => 20.00,
                'price_3d' => 30.00,
                'price_7d' => 50.00,
                'price_10d' => 65.00,
                'price_15d' => 90.00,
                'price_30d' => 140.00,
                'price_90d' => 320.00,
                'price_lifetime' => 599.00,
            ],
            [
                'name' => 'KOS',
                'price_1d' => 30.00,
                'price_3d' => 45.00,
                'price_7d' => 65.00,
                'price_10d' => 85.00,
                'price_15d' => 110.00,
                'price_30d' => 160.00,
                'price_90d' => 380.00,
                'price_lifetime' => 799.00,
            ],
            [
                'name' => 'Aim Ai Engine',
                'price_1d' => 35.00,
                'price_3d' => 50.00,
                'price_7d' => 75.00,
                'price_10d' => 95.00,
                'price_15d' => 130.00,
                'price_30d' => 190.00,
                'price_90d' => 450.00,
                'price_lifetime' => 899.00,
            ],
            [
                'name' => 'bitAIM+',
                'price_1d' => 25.00,
                'price_3d' => 35.00,
                'price_7d' => 55.00,
                'price_10d' => 75.00,
                'price_15d' => 100.00,
                'price_30d' => 150.00,
                'price_90d' => 350.00,
                'price_lifetime' => 699.00,
            ]
        ];

        $durations = ['3d', '7d', '10d', '30d', '90d', 'lifetime', '1d', '15d'];

        foreach ($productsData as $data) {
            $product = Product::create($data);

            // Add 10 keys for each duration for this product
            foreach ($durations as $duration) {
                for ($i = 0; $i < 10; $i++) {
                    Key::create([
                        'product_id' => $product->id,
                        'license_key' => strtoupper(Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4)),
                        'duration' => $duration,
                        'is_sold' => false,
                        'telegram_user_id' => null,
                        'sold_at' => null,
                    ]);
                }
            }
        }
    }
}
