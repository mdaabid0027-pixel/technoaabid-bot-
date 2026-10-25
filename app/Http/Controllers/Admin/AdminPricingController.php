<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\TelegramUser;
use App\Models\UserProductPrice;
use Illuminate\Http\Request;

class AdminPricingController extends Controller
{
    /**
     * Display the Pricing Manager page.
     */
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'customer'); // 'customer' or 'reseller'
        
        $products = Product::with('category')->orderBy('name')->get();
        $categories = Category::with('products')->orderBy('name')->get();
        $resellers = TelegramUser::where('is_reseller', true)
            ->orWhereHas('customPrices')
            ->orderBy('first_name')
            ->get();
        $allUsers = TelegramUser::orderBy('first_name')->get();
        $allCustomPrices = UserProductPrice::with(['telegramUser', 'product'])->latest()->get();

        // Selected reseller for reseller tab
        $selectedUserId = $request->input('reseller_id', $resellers->first()?->id ?? ($allUsers->first()?->id ?? null));
        $selectedUser = $selectedUserId ? TelegramUser::find($selectedUserId) : null;

        $resellerPricesMap = [];
        if ($selectedUser) {
            $existing = UserProductPrice::where('telegram_user_id', $selectedUser->id)->get();
            foreach ($existing as $ep) {
                $resellerPricesMap[$ep->product_id][$ep->duration] = (float) $ep->price;
            }
        }

        $allUsersJson = $allUsers->map(function ($u) {
            return [
                'id'          => $u->id,
                'name'        => $u->first_name ? ($u->first_name . ' ' . $u->last_name) : ($u->username ?: 'User #' . $u->id),
                'username'    => $u->username ? '@' . $u->username : '',
                'telegram_id' => (string) $u->telegram_id,
                'is_reseller' => (bool) $u->is_reseller,
            ];
        })->values()->toJson();

        return view('admin.pricing.index', compact(
            'tab',
            'products',
            'categories',
            'resellers',
            'allUsers',
            'allUsersJson',
            'allCustomPrices',
            'selectedUser',
            'resellerPricesMap'
        ));
    }

    /**
     * Update customer standard prices for a specific product.
     */
    public function updateCustomerPrices(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'price_1d'         => 'nullable|numeric|min:0',
            'cost_1d'          => 'nullable|numeric|min:0',
            'price_3d'         => 'nullable|numeric|min:0',
            'cost_3d'          => 'nullable|numeric|min:0',
            'price_3d_queue'   => 'nullable|numeric|min:0',
            'cost_3d_queue'    => 'nullable|numeric|min:0',
            'price_7d'         => 'nullable|numeric|min:0',
            'cost_7d'          => 'nullable|numeric|min:0',
            'price_7d_queue'   => 'nullable|numeric|min:0',
            'cost_7d_queue'    => 'nullable|numeric|min:0',
            'price_10d'        => 'nullable|numeric|min:0',
            'cost_10d'         => 'nullable|numeric|min:0',
            'price_15d'        => 'nullable|numeric|min:0',
            'cost_15d'         => 'nullable|numeric|min:0',
            'price_30d'        => 'nullable|numeric|min:0',
            'cost_30d'         => 'nullable|numeric|min:0',
            'price_30d_queue'  => 'nullable|numeric|min:0',
            'cost_30d_queue'   => 'nullable|numeric|min:0',
            'price_90d'        => 'nullable|numeric|min:0',
            'cost_90d'         => 'nullable|numeric|min:0',
            'price_90d_queue'  => 'nullable|numeric|min:0',
            'cost_90d_queue'   => 'nullable|numeric|min:0',
            'price_lifetime'   => 'nullable|numeric|min:0',
            'cost_lifetime'    => 'nullable|numeric|min:0',
        ]);

        $product->update([
            'price_1d'         => $request->input('price_1d') !== null ? (float) $request->input('price_1d') : $product->price_1d,
            'cost_1d'          => $request->input('cost_1d') !== null ? (float) $request->input('cost_1d') : $product->cost_1d,
            'price_3d'         => $request->input('price_3d') !== null ? (float) $request->input('price_3d') : $product->price_3d,
            'cost_3d'          => $request->input('cost_3d') !== null ? (float) $request->input('cost_3d') : $product->cost_3d,
            'price_3d_queue'   => $request->input('price_3d_queue') !== null ? (float) $request->input('price_3d_queue') : $product->price_3d_queue,
            'cost_3d_queue'    => $request->input('cost_3d_queue') !== null ? (float) $request->input('cost_3d_queue') : $product->cost_3d_queue,
            'price_7d'         => $request->input('price_7d') !== null ? (float) $request->input('price_7d') : $product->price_7d,
            'cost_7d'          => $request->input('cost_7d') !== null ? (float) $request->input('cost_7d') : $product->cost_7d,
            'price_7d_queue'   => $request->input('price_7d_queue') !== null ? (float) $request->input('price_7d_queue') : $product->price_7d_queue,
            'cost_7d_queue'    => $request->input('cost_7d_queue') !== null ? (float) $request->input('cost_7d_queue') : $product->cost_7d_queue,
            'price_10d'        => $request->input('price_10d') !== null ? (float) $request->input('price_10d') : $product->price_10d,
            'cost_10d'         => $request->input('cost_10d') !== null ? (float) $request->input('cost_10d') : $product->cost_10d,
            'price_15d'        => $request->input('price_15d') !== null ? (float) $request->input('price_15d') : $product->price_15d,
            'cost_15d'         => $request->input('cost_15d') !== null ? (float) $request->input('cost_15d') : $product->cost_15d,
            'price_30d'        => $request->input('price_30d') !== null ? (float) $request->input('price_30d') : $product->price_30d,
            'cost_30d'         => $request->input('cost_30d') !== null ? (float) $request->input('cost_30d') : $product->cost_30d,
            'price_30d_queue'  => $request->input('price_30d_queue') !== null ? (float) $request->input('price_30d_queue') : $product->price_30d_queue,
            'cost_30d_queue'   => $request->input('cost_30d_queue') !== null ? (float) $request->input('cost_30d_queue') : $product->cost_30d_queue,
            'price_90d'        => $request->input('price_90d') !== null ? (float) $request->input('price_90d') : $product->price_90d,
            'cost_90d'         => $request->input('cost_90d') !== null ? (float) $request->input('cost_90d') : $product->cost_90d,
            'price_90d_queue'  => $request->input('price_90d_queue') !== null ? (float) $request->input('price_90d_queue') : $product->price_90d_queue,
            'cost_90d_queue'   => $request->input('cost_90d_queue') !== null ? (float) $request->input('cost_90d_queue') : $product->cost_90d_queue,
            'price_lifetime'   => $request->input('price_lifetime') !== null ? (float) $request->input('price_lifetime') : $product->price_lifetime,
            'cost_lifetime'    => $request->input('cost_lifetime') !== null ? (float) $request->input('cost_lifetime') : $product->cost_lifetime,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Prices updated successfully for {$product->name}!",
                'product' => $product,
            ]);
        }

        return back()->with('success', "Customer prices for '{$product->name}' updated successfully.");
    }

    /**
     * Update global standard reseller prices for a specific product.
     */
    public function updateStandardResellerPrices(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'reseller_price_1d'         => 'nullable|numeric|min:0',
            'reseller_price_3d'         => 'nullable|numeric|min:0',
            'reseller_price_3d_queue'   => 'nullable|numeric|min:0',
            'reseller_price_7d'         => 'nullable|numeric|min:0',
            'reseller_price_7d_queue'   => 'nullable|numeric|min:0',
            'reseller_price_10d'        => 'nullable|numeric|min:0',
            'reseller_price_15d'        => 'nullable|numeric|min:0',
            'reseller_price_30d'        => 'nullable|numeric|min:0',
            'reseller_price_30d_queue'  => 'nullable|numeric|min:0',
            'reseller_price_90d'        => 'nullable|numeric|min:0',
            'reseller_price_90d_queue'  => 'nullable|numeric|min:0',
            'reseller_price_lifetime'   => 'nullable|numeric|min:0',
        ]);

        $product->update([
            'reseller_price_1d'         => $request->input('reseller_price_1d') !== null ? (float) $request->input('reseller_price_1d') : $product->reseller_price_1d,
            'reseller_price_3d'         => $request->input('reseller_price_3d') !== null ? (float) $request->input('reseller_price_3d') : $product->reseller_price_3d,
            'reseller_price_3d_queue'   => $request->input('reseller_price_3d_queue') !== null ? (float) $request->input('reseller_price_3d_queue') : $product->reseller_price_3d_queue,
            'reseller_price_7d'         => $request->input('reseller_price_7d') !== null ? (float) $request->input('reseller_price_7d') : $product->reseller_price_7d,
            'reseller_price_7d_queue'   => $request->input('reseller_price_7d_queue') !== null ? (float) $request->input('reseller_price_7d_queue') : $product->reseller_price_7d_queue,
            'reseller_price_10d'        => $request->input('reseller_price_10d') !== null ? (float) $request->input('reseller_price_10d') : $product->reseller_price_10d,
            'reseller_price_15d'        => $request->input('reseller_price_15d') !== null ? (float) $request->input('reseller_price_15d') : $product->reseller_price_15d,
            'reseller_price_30d'        => $request->input('reseller_price_30d') !== null ? (float) $request->input('reseller_price_30d') : $product->reseller_price_30d,
            'reseller_price_30d_queue'  => $request->input('reseller_price_30d_queue') !== null ? (float) $request->input('reseller_price_30d_queue') : $product->reseller_price_30d_queue,
            'reseller_price_90d'        => $request->input('reseller_price_90d') !== null ? (float) $request->input('reseller_price_90d') : $product->reseller_price_90d,
            'reseller_price_90d_queue'  => $request->input('reseller_price_90d_queue') !== null ? (float) $request->input('reseller_price_90d_queue') : $product->reseller_price_90d_queue,
            'reseller_price_lifetime'   => $request->input('reseller_price_lifetime') !== null ? (float) $request->input('reseller_price_lifetime') : $product->reseller_price_lifetime,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Standard Reseller prices updated successfully for {$product->name}!",
                'product' => $product,
            ]);
        }

        return redirect()->route('admin.pricing.index', ['tab' => 'standard_reseller'])->with('success', "Standard Reseller prices for '{$product->name}' updated successfully.");
    }

    /**
     * Batch update standard reseller prices for all products.
     */
    public function updateStandardResellerBatch(Request $request)
    {
        $productsData = $request->input('products', []);

        foreach ($productsData as $prodId => $prices) {
            $product = Product::find($prodId);
            if (!$product) continue;

            $updateData = [];
            foreach (['1d', '3d', '3d_queue', '7d', '7d_queue', '10d', '15d', '30d', '30d_queue', '90d', '90d_queue', 'lifetime'] as $dur) {
                $key = "reseller_price_{$dur}";
                if (isset($prices[$key]) && $prices[$key] !== '') {
                    $updateData[$key] = (float)$prices[$key];
                }
            }
            if (!empty($updateData)) {
                $product->update($updateData);
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'All product standard reseller rates updated successfully!',
            ]);
        }

        return redirect()->route('admin.pricing.index', ['tab' => 'standard_reseller'])->with('success', 'All product standard reseller rates updated successfully!');
    }

    /**
     * Set or update custom price for a reseller.
     */
    public function updateResellerPrice(Request $request)
    {
        $request->validate([
            'telegram_user_id' => 'required|exists:telegram_users,id',
            'product_id'       => 'required|exists:products,id',
            'duration'         => 'required|string',
            'price'            => 'required|numeric|min:0',
        ]);

        $user = TelegramUser::findOrFail($request->telegram_user_id);
        $product = Product::findOrFail($request->product_id);
        $duration = strtolower(trim($request->duration));

        $customPrice = UserProductPrice::updateOrCreate(
            [
                'telegram_user_id' => $user->id,
                'product_id'       => $product->id,
                'duration'         => $duration,
            ],
            [
                'price'            => (float) $request->price,
            ]
        );

        // Ensure user is marked as reseller
        if (!$user->is_reseller) {
            $user->is_reseller = true;
            $user->save();
        }

        $userName = $user->first_name ?: ($user->username ?: 'User #' . $user->id);
        $msg = "Set special price ₹" . number_format($request->price, 2) . " for {$userName} on [{$product->name} - " . strtoupper($duration) . "].";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'id'      => $customPrice->id,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Batch update custom prices for a product for a specific reseller.
     */
    public function updateBatchResellerPrices(Request $request, $productId)
    {
        $request->validate([
            'telegram_user_id' => 'required|exists:telegram_users,id',
        ]);

        $user = TelegramUser::findOrFail($request->telegram_user_id);
        $product = Product::findOrFail($productId);

        $fields = [
            'price_1d'         => '1d',
            'price_3d'         => '3d',
            'price_3d_queue'   => '3d_queue',
            'price_7d'         => '7d',
            'price_7d_queue'   => '7d_queue',
            'price_10d'        => '10d',
            'price_15d'        => '15d',
            'price_30d'        => '30d',
            'price_30d_queue'  => '30d_queue',
            'price_90d'        => '90d',
            'price_90d_queue'  => '90d_queue',
            'price_lifetime'   => 'lifetime',
        ];

        foreach ($fields as $inputKey => $dur) {
            if ($request->has($inputKey) && $request->input($inputKey) !== null && $request->input($inputKey) !== '') {
                $val = (float) $request->input($inputKey);
                if ($val > 0) {
                    UserProductPrice::updateOrCreate(
                        [
                            'telegram_user_id' => $user->id,
                            'product_id'       => $product->id,
                            'duration'         => $dur,
                        ],
                        [
                            'price'            => $val,
                        ]
                    );
                } else {
                    UserProductPrice::where('telegram_user_id', $user->id)
                        ->where('product_id', $product->id)
                        ->where('duration', $dur)
                        ->delete();
                }
            }
        }

        if (!$user->is_reseller) {
            $user->is_reseller = true;
            $user->save();
        }

        $userName = $user->first_name ?: ($user->username ?: 'User #' . $user->id);
        $msg = "Reseller special rates for {$userName} updated on {$product->name}!";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Delete a custom price.
     */
    public function deleteResellerPrice($id)
    {
        $cp = UserProductPrice::findOrFail($id);
        $cp->delete();

        return back()->with('success', "Reseller custom rate removed successfully.");
    }
}
