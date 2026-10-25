<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Key;
use App\Models\Product;
use App\Models\TelegramUser;
use App\Models\UserProductPrice;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminKeyController extends Controller
{
    /**
     * Display Key Inventory & Pricing Management dashboard.
     */
    public function index(Request $request)
    {
        // 0. Automatically purge sold keys whose validity expired over 48 hours ago
        Key::where('is_sold', true)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', Carbon::now()->subHours(48))
            ->delete();

        $query = Key::with(['product.category', 'telegramUser']);

        // Filter by Product / Game
        if ($request->filled('game') && $request->game !== 'all') {
            $game = $request->game;
            $query->whereHas('product', function ($q) use ($game) {
                $q->where('name', 'LIKE', "%{$game}%")
                  ->orWhere('game_name', 'LIKE', "%{$game}%");
            });
        }

        // Filter by Duration
        if ($request->filled('duration') && $request->duration !== 'all') {
            $query->where('duration', $request->duration);
        }

        // Search by Key Code or Product Name
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('license_key', 'LIKE', "%{$search}%")
                  ->orWhereHas('product', function ($pq) use ($search) {
                      $pq->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Tab: Available vs History (Sold in last 48 hours)
        $tab = $request->input('tab', 'available');
        if ($tab === 'history') {
            $query->where('is_sold', true)
                ->where(function ($q) {
                    $recentLimit = Carbon::now()->subHours(48);
                    $q->where('sold_at', '>=', $recentLimit)
                      ->orWhere('updated_at', '>=', $recentLimit);
                })
                ->orderBy('sold_at', 'desc')
                ->orderBy('updated_at', 'desc');
        } else {
            $query->where('is_sold', false)->orderBy('created_at', 'desc');
        }

        $keys = $query->paginate(25)->withQueryString();

        // 1. Group counts for top summary cards
        $counts = [
            'aim_carrom'      => Key::whereHas('product', fn($q) => $q->where('name', 'LIKE', '%Carrom%'))->where('is_sold', false)->count(),
            'ks_engine'       => Key::whereHas('product', fn($q) => $q->where('name', 'LIKE', '%KOS%')->orWhere('name', 'LIKE', '%KS%'))->where('is_sold', false)->count(),
            'snake'           => Key::whereHas('product', fn($q) => $q->where('name', 'LIKE', '%Snake%'))->where('is_sold', false)->count(),
            'aim_ai'          => Key::whereHas('product', fn($q) => $q->where('name', 'LIKE', '%Aim AI%')->orWhere('name', 'LIKE', '%Ai%'))->where('is_sold', false)->count(),
            'total_available' => Key::where('is_sold', false)->count(),
            'total_used'      => Key::where('is_sold', true)->count(),
            'today_sold'      => Key::where('is_sold', true)->whereDate('sold_at', Carbon::today())->count(),
            'yesterday_sold'  => Key::where('is_sold', true)->whereDate('sold_at', Carbon::yesterday())->count(),
            'month_sold'      => Key::where('is_sold', true)->whereMonth('sold_at', Carbon::now()->month)->whereYear('sold_at', Carbon::now()->year)->count(),
        ];

        // 2. Profit Calculations
        $todayRevenue = Transaction::where('type', 'debit')
            ->whereDate('created_at', Carbon::today())
            ->sum('amount');
        $counts['today_profit'] = round($todayRevenue > 0 ? $todayRevenue : ($counts['today_sold'] * 150));

        $monthRevenue = Transaction::where('type', 'debit')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('amount');
        $counts['month_profit'] = round($monthRevenue > 0 ? $monthRevenue : ($counts['month_sold'] * 150));

        // 3. Products list for Key Creation and Quick Link Generator (bitAIM is manual activation, no keys)
        $products = Product::with('category')->where('name', 'NOT LIKE', '%bitaim%')->orderBy('name')->get();

        // 3.1 Grouped Stock Structure by Product & Duration Plan (excluding bitAIM)
        $productsStock = Product::withCount(['keys as available_keys_count' => function ($query) {
            $query->where('is_sold', false);
        }])->where('name', 'NOT LIKE', '%bitaim%')->get()->map(function ($p) {
            $pName = strtolower($p->name);
            if (str_contains($pName, 'soccer')) {
                $durList = ['3d' => '3 Days', '10d' => '10 Days', '30d' => '30 Days'];
            } elseif (str_contains($pName, 'bitaim') || str_contains($pName, 'bit')) {
                $durList = ['7d' => '7 Days (1 Week)', '30d' => '30 Days (1 Month)', '90d' => '90 Days (3 Months)', 'lifetime' => 'Lifetime'];
            } elseif (str_contains($pName, 'carrom')) {
                $durList = [
                    '3d' => '3 Days', '7d' => '7 Days', '30d' => '30 Days', '90d' => '90 Days',
                    '3d_queue' => '3D Queue', '7d_queue' => '7D Queue', '30d_queue' => '30D Queue', '90d_queue' => '90D Queue',
                ];
            } elseif (str_contains($pName, 'kos') || str_contains($pName, 'ks')) {
                $durList = ['1d' => '1 Day', '7d' => '7 Days', '15d' => '15 Days', '30d' => '30 Days'];
            } elseif (str_contains($pName, 'snake')) {
                $durList = ['3d' => '3 Days', '10d' => '10 Days', '30d' => '30 Days', '90d' => '90 Days'];
            } elseif (str_contains($pName, 'ai') || str_contains($pName, 'aim ai')) {
                $durList = ['1d' => '1 Day', '3d' => '3 Days', '7d' => '7 Days', '15d' => '15 Days', '30d' => '30 Days', '90d' => '90 Days'];
            } else {
                $durList = ['1d' => '1 Day', '7d' => '7 Days', '30d' => '30 Days'];
            }

            $plans = [];
            foreach ($durList as $durKey => $durLabel) {
                $count = Key::where('product_id', $p->id)
                    ->where('is_sold', false)
                    ->where('duration', $durKey)
                    ->count();
                $plans[$durKey] = [
                    'key' => $durKey,
                    'label' => $durLabel,
                    'count' => $count,
                ];
            }

            return [
                'id' => $p->id,
                'name' => $p->name,
                'game_name' => $p->game_name ?: ($p->category->name ?? 'Game'),
                'total_available' => $p->available_keys_count,
                'plans' => $plans,
            ];
        });

        // 4. Resellers list for Custom Pricing
        $resellers = TelegramUser::orderBy('first_name')->get();

        // 5. Active VIP Custom Prices
        $allCustomPrices = UserProductPrice::with(['telegramUser', 'product'])->latest()->get();

        // 6. Selected Reseller logic if filtered
        $selectedUserId = $request->input('telegram_user_id');
        $targetUser = null;
        $userCustomPrices = [];

        if ($selectedUserId) {
            $targetUser = TelegramUser::find($selectedUserId);
            if ($targetUser) {
                $userCustomPrices = UserProductPrice::where('telegram_user_id', $targetUser->id)
                    ->get()
                    ->keyBy(fn($item) => $item->product_id . '_' . ($item->duration ?? 'default'));
            }
        }

        return view('admin.keys.index', compact(
            'keys',
            'counts',
            'products',
            'productsStock',
            'resellers',
            'allCustomPrices',
            'targetUser',
            'userCustomPrices',
            'tab'
        ));
    }

    /**
     * AJAX endpoint to fetch keys for a specific product & duration batch.
     */
    public function getBatchKeys(Request $request)
    {
        $productId = $request->product_id;
        $duration = $request->duration;

        $product = Product::findOrFail($productId);
        $keys = Key::where('product_id', $productId)
            ->where('duration', $duration)
            ->where('is_sold', false)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'license_key', 'created_at']);

        return response()->json([
            'success' => true,
            'product' => $product->name,
            'duration' => $duration,
            'count' => $keys->count(),
            'keys' => $keys,
        ]);
    }

    /**
     * Store new license key(s) in bulk.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id'   => 'nullable|exists:products,id',
            'game_name'    => 'nullable|string',
            'duration'     => 'nullable|string',
            'key_duration' => 'nullable|string',
            'license_keys' => 'nullable|string',
            'key_code'     => 'nullable|string',
        ]);

        $productId = $request->product_id;
        $duration = $request->duration ?: $request->key_duration;
        $rawKeysString = $request->license_keys ?: $request->key_code;

        // If product_id not directly passed, lookup by game_name
        if (!$productId && $request->filled('game_name')) {
            $product = Product::where('name', 'LIKE', '%' . trim($request->game_name) . '%')->first();
            if ($product) {
                $productId = $product->id;
            } else {
                // Create or find product
                $product = Product::firstOrCreate(
                    ['name' => trim($request->game_name)],
                    ['price_1d' => 10, 'price_7d' => 30, 'price_30d' => 100]
                );
                $productId = $product->id;
            }
        }

        if (!$productId) {
            return back()->with('error', 'Please select a valid Game / Product.');
        }

        // Clean and normalize duration format (supporting normal and Auto Queue variants)
        $durationRaw = trim((string)$duration);
        $durationLower = strtolower($durationRaw);

        if (str_contains($durationLower, 'queue') || str_contains($durationLower, 'auto queue')) {
            if (str_contains($durationLower, '90')) $duration = '90d_queue';
            elseif (str_contains($durationLower, '30')) $duration = '30d_queue';
            elseif (str_contains($durationLower, '7')) $duration = '7d_queue';
            elseif (str_contains($durationLower, '3')) $duration = '3d_queue';
            else $duration = '3d_queue';
        } else {
            if (str_contains($durationLower, 'lifetime')) $duration = 'lifetime';
            elseif (str_contains($durationLower, '90')) $duration = '90d';
            elseif (str_contains($durationLower, '30')) $duration = '30d';
            elseif (str_contains($durationLower, '15')) $duration = '15d';
            elseif (str_contains($durationLower, '10')) $duration = '10d';
            elseif (str_contains($durationLower, '7')) $duration = '7d';
            elseif (str_contains($durationLower, '3')) $duration = '3d';
            elseif (str_contains($durationLower, '1')) $duration = '1d';
            else $duration = $durationLower ?: '1d';
        }

        // Parse keys separated by newline
        $lines = explode("\n", str_replace("\r", "", $rawKeysString));
        $added = 0;
        $duplicates = 0;

        foreach ($lines as $line) {
            $code = trim($line);
            if (empty($code)) continue;

            $exists = Key::where('license_key', $code)->exists();
            if ($exists) {
                $duplicates++;
                continue;
            }

            Key::create([
                'product_id'  => $productId,
                'duration'    => $duration,
                'license_key' => $code,
                'is_sold'     => false,
            ]);
            $added++;
        }

        $prod = Product::find($productId);
        $prodName = $prod ? $prod->name : 'Product';
        $msg = "{$added} license key(s) added successfully for [{$prodName} - {$duration}].";
        if ($duplicates > 0) {
            $msg .= " ({$duplicates} duplicate key(s) skipped)";
        }

        // Notify only the users who requested / attempted to buy this out-of-stock key
        if ($added > 0 && $productId) {
            $notified = \App\Models\StockSubscription::notifySubscribers($productId, $duration);
            if ($notified > 0) {
                $msg .= " 🚀 Sent instant back-in-stock alerts to {$notified} waiting customer(s)!";
            }
        }

        return back()->with('success', $msg);
    }

    /**
     * Delete a single license key.
     */
    public function destroy($id)
    {
        try {
            $key = Key::findOrFail($id);
            $key->delete();
            return back()->with('success', 'License key deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete license key: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete selected license keys.
     */
    public function bulkDelete(Request $request)
    {
        $keyIds = $request->input('key_ids', []);

        if (empty($keyIds)) {
            return back()->with('error', 'No keys selected for deletion.');
        }

        $deletedCount = Key::whereIn('id', $keyIds)->delete();

        return back()->with('success', "Successfully deleted {$deletedCount} selected license keys.");
    }

    /**
     * Clear used keys history older than 48 hours or all used keys.
     */
    public function clearHistory(Request $request)
    {
        $threshold = Carbon::now()->subHours(48);
        $deletedCount = Key::where('is_sold', true)
            ->where(function ($q) use ($threshold) {
                $q->where('updated_at', '<', $threshold)
                  ->orWhere('sold_at', '<', $threshold)
                  ->orWhereNull('sold_at');
            })
            ->delete();

        return back()->with('success', "Success! Deleted {$deletedCount} old keys from history.");
    }

    /**
     * Create or update custom VIP price for a reseller / user.
     */
    public function updateCustomPrice(Request $request)
    {
        $request->validate([
            'product_id'        => 'required|exists:products,id',
            'price'             => 'required|numeric|min:0',
            'duration'          => 'nullable|string',
            'telegram_user_id'  => 'nullable|exists:telegram_users,id',
            'user_query'        => 'nullable|string',
        ]);

        $telegramUserId = $request->telegram_user_id;

        // If username/first_name search query provided
        if (!$telegramUserId && $request->filled('user_query')) {
            $q = trim($request->user_query);
            $tUser = TelegramUser::where('username', 'LIKE', "%{$q}%")
                ->orWhere('first_name', 'LIKE', "%{$q}%")
                ->orWhere('telegram_id', $q)
                ->first();

            if ($tUser) {
                $telegramUserId = $tUser->id;
            }
        }

        if (!$telegramUserId) {
            $msg = 'Please select a valid Telegram Reseller / User.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        $product = Product::findOrFail($request->product_id);
        $duration = $request->filled('duration') ? strtolower(trim($request->duration)) : '1d';

        $customPrice = UserProductPrice::updateOrCreate(
            [
                'telegram_user_id' => $telegramUserId,
                'product_id'       => $product->id,
                'duration'         => $duration,
            ],
            [
                'price'            => $request->price,
            ]
        );

        $reseller = TelegramUser::find($telegramUserId);
        if ($reseller) {
            $reseller->is_reseller = true;
            $reseller->save();
        }
        $resellerName = $reseller ? ($reseller->first_name ?: $reseller->username ?: 'Reseller #' . $reseller->id) : 'Reseller';

        $msg = "Success! Set VIP custom price of ₹" . number_format($request->price, 2) . " for {$resellerName} on [{$product->name} - {$duration}].";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'price'   => $request->price,
                'id'      => $customPrice->id,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Delete custom VIP price.
     */
    public function deleteCustomPrice($id)
    {
        $custom = UserProductPrice::findOrFail($id);
        $resellerName = $custom->telegramUser ? ($custom->telegramUser->first_name ?: $custom->telegramUser->username) : 'User';
        $productName = $custom->product ? $custom->product->name : 'Product';

        $custom->delete();

        return back()->with('success', "VIP Custom Price removed for {$resellerName} on {$productName}. Default price restored.");
    }

    /**
     * Generate dynamic WhatsApp Checkout Link.
     */
    public function generateQuickLink(Request $request)
    {
        $request->validate([
            'product_name'   => 'required|string',
            'custom_price'   => 'required|numeric|min:1',
            'customer_name'  => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
        ]);

        $productName   = $request->input('product_name');
        $price         = floatval($request->input('custom_price'));
        $customerName  = $request->input('customer_name');
        $customerPhone = preg_replace('/[^0-9]/', '', (string)$request->input('customer_phone'));

        $baseUrl = url('/');
        $checkoutUrl = "{$baseUrl}/telegram/webapp?product=" . urlencode($productName) . "&price=" . $price;

        $msg = "👋 Hello" . ($customerName ? " {$customerName}" : "") . "! Here is your instant checkout link for *" . $productName . "* (Amount: ₹" . number_format($price, 2) . "):\n\n" .
               "🔗 " . $checkoutUrl . "\n\n" .
               "Pay & get your license key instantly! 🚀";

        $waSendUrl = $customerPhone
            ? "https://api.whatsapp.com/send?phone={$customerPhone}&text=" . urlencode($msg)
            : "https://api.whatsapp.com/send?text=" . urlencode($msg);

        return response()->json([
            'success'      => true,
            'url'          => $checkoutUrl,
            'whatsapp_url' => $waSendUrl,
            'message'      => 'WhatsApp link generated successfully!',
        ]);
    }
}
