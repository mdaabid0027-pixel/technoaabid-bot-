<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Key;
use App\Models\Category;
use Illuminate\Http\Request;

class AdminProductController extends Controller
{
    /**
     * Display a listing of products, stock overview, and granular key inventory with filters.
     */
    public function index(Request $request)
    {
        // 1. Products with Duration Stock Breakdown
        $products = Product::withCount([
            'keys as total_available_count' => function ($q) { $q->where('is_sold', false); },
            'keys as keys_1d_count' => function ($q) { $q->where('duration', '1d')->where('is_sold', false); },
            'keys as keys_3d_count' => function ($q) { $q->where('duration', '3d')->where('is_sold', false); },
            'keys as keys_7d_count' => function ($q) { $q->where('duration', '7d')->where('is_sold', false); },
            'keys as keys_10d_count' => function ($q) { $q->where('duration', '10d')->where('is_sold', false); },
            'keys as keys_15d_count' => function ($q) { $q->where('duration', '15d')->where('is_sold', false); },
            'keys as keys_30d_count' => function ($q) { $q->where('duration', '30d')->where('is_sold', false); },
            'keys as keys_90d_count' => function ($q) { $q->where('duration', '90d')->where('is_sold', false); },
        ])->get();

        // 2. Granular Key Inventory Query with Filters
        $productId = $request->input('product_id');
        $duration = $request->input('duration');
        $status = $request->input('status'); // 'available' or 'sold'
        $searchKey = $request->input('search_key');

        $keysQuery = Key::with(['product', 'telegramUser'])->latest();

        if ($productId) {
            $keysQuery->where('product_id', $productId);
        }

        if ($duration) {
            $keysQuery->where('duration', $duration);
        }

        if ($status === 'available') {
            $keysQuery->where('is_sold', false);
        } elseif ($status === 'sold') {
            $keysQuery->where('is_sold', true);
        }

        if ($searchKey) {
            $keysQuery->where('license_key', 'like', "%{$searchKey}%");
        }

        $keys = $keysQuery->paginate(25)->withQueryString();
        $categories = Category::all();

        return view('admin.products.index', compact(
            'products',
            'keys',
            'categories',
            'productId',
            'duration',
            'status',
            'searchKey'
        ));
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'game_name' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'price_1d' => ['required', 'numeric', 'min:0'],
            'buying_price' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $price = (float) $request->input('price_1d');

        Product::create([
            'name' => $request->input('name'),
            'game_name' => $request->input('game_name', 'Carrom Pool'),
            'category_id' => $request->input('category_id'),
            'price_1d' => $price,
            'price_3d' => $price,
            'price_7d' => $price,
            'price_10d' => $price,
            'price_15d' => $price,
            'price_30d' => $price,
            'price_90d' => $price,
            'price_lifetime' => $price,
            'buying_price' => $request->input('buying_price', 0.00),
            'is_active' => $request->has('is_active') ? (bool)$request->input('is_active') : true,
        ]);

        return back()->with('success', 'Product created successfully.');
    }

    /**
     * Update the product details.
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'game_name' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'price_1d' => ['required', 'numeric', 'min:0'],
            'price_3d' => ['nullable', 'numeric', 'min:0'],
            'price_7d' => ['nullable', 'numeric', 'min:0'],
            'price_10d' => ['nullable', 'numeric', 'min:0'],
            'price_15d' => ['nullable', 'numeric', 'min:0'],
            'price_30d' => ['nullable', 'numeric', 'min:0'],
            'price_90d' => ['nullable', 'numeric', 'min:0'],
            'buying_price' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $price1d = (float) $request->input('price_1d');

        $data = [
            'category_id' => $request->input('category_id'),
            'price_1d' => $price1d,
            'price_3d' => $request->has('price_3d') ? (float)$request->input('price_3d') : $product->price_3d,
            'price_7d' => $request->has('price_7d') ? (float)$request->input('price_7d') : $product->price_7d,
            'price_10d' => $request->has('price_10d') ? (float)$request->input('price_10d') : $product->price_10d,
            'price_15d' => $request->has('price_15d') ? (float)$request->input('price_15d') : $product->price_15d,
            'price_30d' => $request->has('price_30d') ? (float)$request->input('price_30d') : $product->price_30d,
            'price_90d' => $request->has('price_90d') ? (float)$request->input('price_90d') : $product->price_90d,
            'buying_price' => $request->input('buying_price', $product->buying_price),
            'is_active' => $request->has('is_active') ? (bool)$request->input('is_active') : $product->is_active,
        ];

        if ($request->filled('name')) {
            $data['name'] = $request->input('name');
        }

        if ($request->filled('game_name')) {
            $data['game_name'] = $request->input('game_name');
        }

        $product->update($data);

        return back()->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return back()->with('success', 'Product deleted successfully.');
    }

    /**
     * Add keys in bulk to the database for a specific product and duration.
     */
    public function addKeys(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'duration' => ['required', 'string'],
            'keys' => ['required', 'string'],
        ]);

        $duration = strtolower($request->input('duration'));
        $rawKeys = explode("\n", $request->input('keys'));
        
        $keysAdded = 0;

        foreach ($rawKeys as $rawKey) {
            $cleanedKey = trim($rawKey);
            if (!empty($cleanedKey)) {
                Key::create([
                    'product_id' => $product->id,
                    'duration' => $duration,
                    'license_key' => $cleanedKey,
                    'is_sold' => false,
                ]);
                $keysAdded++;
            }
        }

        $notified = 0;
        if ($keysAdded > 0) {
            $notified = \App\Models\StockSubscription::notifySubscribers($product->id, $duration);
        }

        $noticeMsg = "Successfully imported {$keysAdded} keys into {$product->name} ({$duration}) stock.";
        if ($notified > 0) {
            $noticeMsg .= " 🚀 Sent instant stock alerts to {$notified} waiting customer(s)!";
        }

        return back()->with('success', $noticeMsg);
    }
}
