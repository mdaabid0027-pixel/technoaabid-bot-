<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class AdminInGameItemController extends Controller
{
    public static function defaultItems(): array
    {
        return [
            'gems' => [
                'name' => 'Carrom Gems',
                'button_text' => '💎 GEMS PRICE',
                'title' => '💎 CARROM POOL GEMS OFFERS',
                'whatsapp_number' => 'XXXXXXXXXX',
                'photo_url' => '/uploads/ingame/gems_1787675902.jpg',
                'photo_file_id' => '',
                'caption' => "💎 CARROM POOL GEMS SPECIAL RATE\n\n• Instant Delivery\n• 100% Safe & Anti-Ban\n• Direct WhatsApp Deals",
            ],
            'coins' => [
                'name' => 'Carrom Coins',
                'button_text' => '🟡 COIN PRICE',
                'title' => '🟡 CARROM POOL COIN PACKS',
                'whatsapp_number' => 'XXXXXXXXXX',
                'photo_url' => '/uploads/ingame/coins_1787675915.jpg',
                'photo_file_id' => '',
                'caption' => "🟡 CARROM POOL COINS BULK STOCK\n\n• Millions & Billions available\n• Fast delivery to your account\n• Contact on WhatsApp now!",
            ],
            'striker' => [
                'name' => 'Carrom Strikers',
                'button_text' => '🧿 STRIKER PRICE',
                'title' => '🧿 EXCLUSIVE STRIKERS UNLOCK',
                'whatsapp_number' => 'XXXXXXXXXX',
                'photo_url' => '/uploads/ingame/striker_1787675895.jpg',
                'photo_file_id' => '',
                'caption' => "🧿 LEGENDARY & SUPREME STRIKERS\n\n• Max power strikers\n• Instant unlock on WhatsApp",
            ],
            'trail' => [
                'name' => 'Carrom Trails',
                'button_text' => '🔥 TRAIL PRICE',
                'title' => '🔥 GLOWING & VIP TRAILS',
                'whatsapp_number' => 'XXXXXXXXXX',
                'photo_url' => '/uploads/ingame/trail_1787675886.jpg',
                'photo_file_id' => '',
                'caption' => "🔥 EXCLUSIVE PUCKS & TRAILS\n\n• Premium glowing effects\n• DM to buy on WhatsApp",
            ],
        ];
    }

    public static function getItems(): array
    {
        $file = storage_path('app/ingame_items.json');
        if (File::exists($file)) {
            $data = json_decode(File::get($file), true);
            if (!empty($data) && is_array($data)) {
                return $data;
            }
        }
        
        $defaults = self::defaultItems();
        try {
            if (!File::exists(dirname($file))) {
                File::makeDirectory(dirname($file), 0755, true);
            }
            File::put($file, json_encode($defaults, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        } catch (\Throwable $e) {}
        
        return $defaults;
    }

    public static function saveItems(array $items): void
    {
        $file = storage_path('app/ingame_items.json');
        if (!File::exists(dirname($file))) {
            File::makeDirectory(dirname($file), 0755, true);
        }
        File::put($file, json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    public function index()
    {
        $items = self::getItems();
        return view('admin.ingame_items.index', compact('items'));
    }

    public function update(Request $request, $id)
    {
        $items = self::getItems();

        if (!isset($items[$id])) {
            return redirect()->back()->with('error', 'Item not found.');
        }

        $items[$id]['title'] = $request->input('title', $items[$id]['title']);
        $items[$id]['whatsapp_number'] = $request->input('whatsapp_number', $items[$id]['whatsapp_number']);
        $items[$id]['caption'] = $request->input('caption', $items[$id]['caption']);

        if ($request->filled('photo_file_id')) {
            $items[$id]['photo_file_id'] = trim($request->input('photo_file_id'));
        }

        // Handle direct image file upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = $id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/ingame'), $filename);
            $items[$id]['photo_url'] = '/uploads/ingame/' . $filename;
        }

        self::saveItems($items);

        return redirect()->route('admin.ingame-items.index')->with('success', "Item '{$items[$id]['name']}' updated successfully!");
    }
}
