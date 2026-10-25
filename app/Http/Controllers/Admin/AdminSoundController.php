<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\TelegramController;
use App\Models\TelegramUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class AdminSoundController extends Controller
{
    /**
     * Supported Event Definitions.
     */
    public static function getEvents(): array
    {
        return [
            'back_in_stock' => [
                'id' => 'back_in_stock',
                'title' => 'Back in Stock Alert',
                'badge' => 'Stock Alert',
                'icon' => 'fa-solid fa-bell',
                'color' => '#10b981',
                'default' => 'jethalal',
                'description' => 'Jab Admin nayi keys add karega aur waiting customer ko alert message + audio jayega.',
            ],
            'key_purchase' => [
                'id' => 'key_purchase',
                'title' => 'Key Purchase Success',
                'badge' => 'Order Success',
                'icon' => 'fa-solid fa-key',
                'color' => '#6366f1',
                'default' => 'maza',
                'description' => 'Jab user successfully key buy kar leta hai aur screen par key code deliver hota hai.',
            ],
            'out_of_stock' => [
                'id' => 'out_of_stock',
                'title' => 'Out of Stock Alert',
                'badge' => 'Stock 0',
                'icon' => 'fa-solid fa-circle-xmark',
                'color' => '#ef4444',
                'default' => 'khatam',
                'description' => 'Jab user koi aisi key buy karne ki koshish karega jo 0 stock me hai.',
            ],
            'spin_win' => [
                'id' => 'spin_win',
                'title' => 'Lucky Spin Win',
                'badge' => 'Free Rewards',
                'icon' => 'fa-solid fa-gift',
                'color' => '#f59e0b',
                'default' => 'paisa',
                'description' => 'Jab user daily free spin ghumakar free cash prize jeet-ta hai.',
            ],
        ];
    }

    /**
     * Get active sound for an event (returns null if disabled/mute).
     */
    public static function getEventSound(string $eventKey): ?string
    {
        $events = self::getEvents();
        $default = $events[$eventKey]['default'] ?? 'jethalal';
        
        $sound = Cache::get('sound_event_' . $eventKey, $default);
        if ($sound === 'none' || $sound === 'disabled' || empty($sound)) {
            return null;
        }

        return $sound;
    }

    /**
     * Sound definitions with titles, emojis, categories, and file paths.
     */
    public static function getSoundList(): array
    {
        $sounds = [
            [
                'id' => 'jethalal',
                'title' => 'Jethalal (TMKOC)',
                'emoji' => '☕',
                'file' => 'jethalal_wah.mp3',
            ],
            [
                'id' => 'jalwa',
                'title' => 'Jalwa Hai Hamara (Mirzapur)',
                'emoji' => '🕶️',
                'file' => 'jalwa_hai_hamara.mp3',
            ],
            [
                'id' => 'kgf',
                'title' => 'KGF Rocky Bhai Theme',
                'emoji' => '🔥',
                'file' => 'kgf_theme.mp3',
            ],
            [
                'id' => 'paisa',
                'title' => 'Paisa Hi Paisa Hoga',
                'emoji' => '💰',
                'file' => 'paisa_hi_paisa.mp3',
            ],
            [
                'id' => 'waah_bete',
                'title' => 'Waah Bete Mauj Kardi',
                'emoji' => '🚀',
                'file' => 'waah_bete.mp3',
            ],
            [
                'id' => 'maza',
                'title' => 'Aur Ye Jo Maza Hai...',
                'emoji' => '🎉',
                'file' => 'maza_aaya.mp3',
            ],
            [
                'id' => 'dhoom',
                'title' => 'Dhoom Machale Theme',
                'emoji' => '🏎️',
                'file' => 'dhoom_machale.mp3',
            ],
            [
                'id' => 'khatam',
                'title' => 'Khatam Tata Bye Bye',
                'emoji' => '❌',
                'file' => 'khatam_tata_bye_bye.mp3',
            ],
        ];

        // Custom uploaded audio files in public/audio
        $audioDir = public_path('audio');
        if (File::exists($audioDir)) {
            $files = File::files($audioDir);
            $knownFiles = array_column($sounds, 'file');
            
            foreach ($files as $f) {
                $filename = $f->getFilename();
                if ($f->getExtension() === 'mp3' && !in_array($filename, $knownFiles)) {
                    $base = pathinfo($filename, PATHINFO_FILENAME);
                    $sounds[] = [
                        'id' => $base,
                        'title' => ucwords(str_replace('_', ' ', $base)),
                        'emoji' => '🎵',
                        'file' => $filename,
                    ];
                }
            }
        }

        return $sounds;
    }

    /**
     * Display Sound Effects Soundboard & Event Mappings.
     */
    public function index()
    {
        $sounds = self::getSoundList();
        $events = self::getEvents();

        // Get current mapping for each event
        $eventMappings = [];
        foreach ($events as $eKey => $eData) {
            $eventMappings[$eKey] = Cache::get('sound_event_' . $eKey, $eData['default']);
        }

        return view('admin.sounds.index', compact('sounds', 'events', 'eventMappings'));
    }

    /**
     * Save all Event Sound Mappings.
     */
    public function saveEvents(Request $request)
    {
        $events = self::getEvents();

        foreach ($events as $eKey => $eData) {
            if ($request->has('sound_event_' . $eKey)) {
                $selectedSound = $request->input('sound_event_' . $eKey);
                Cache::forever('sound_event_' . $eKey, $selectedSound);
            }
        }

        return back()->with('success', '🎉 All Event Sound Mappings updated successfully!');
    }

    /**
     * Assign a single sound directly to an event.
     */
    public function assignEvent(Request $request)
    {
        $request->validate([
            'event_key' => 'required|string',
            'sound_id' => 'required|string',
        ]);

        $eventKey = $request->input('event_key');
        $soundId = $request->input('sound_id');

        Cache::forever('sound_event_' . $eventKey, $soundId);

        $events = self::getEvents();
        $eventTitle = $events[$eventKey]['title'] ?? $eventKey;

        $sounds = self::getSoundList();
        $matched = collect($sounds)->firstWhere('id', $soundId);
        $soundTitle = $matched['title'] ?? ($soundId === 'none' ? 'Mute (No Sound)' : $soundId);

        return back()->with('success', "✅ '{$soundTitle}' assigned to '{$eventTitle}'!");
    }

    /**
     * Send test sound directly to Admin/User Telegram chat.
     */
    public function testTelegram(Request $request)
    {
        $request->validate([
            'sound_id' => 'required|string',
            'telegram_user_id' => 'nullable',
        ]);

        $soundId = $request->input('sound_id');
        $targetChatId = $request->input('telegram_user_id');

        if (!$targetChatId) {
            $user = TelegramUser::where('telegram_id', '>', 0)->first();
            $targetChatId = $user ? $user->telegram_id : env('ADMIN_TELEGRAM_ID', null);
        }

        $controller = app(TelegramController::class);
        $controller->sendMemeVoiceReaction($targetChatId, $soundId);

        return back()->with('success', "🚀 Test audio sent to Telegram chat ({$targetChatId})!");
    }

    /**
     * Upload a custom MP3 audio file.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'audio_file' => 'required|file|mimes:mp3,wav,ogg,m4a|max:10240',
            'sound_name' => 'nullable|string|max:50',
        ]);

        $file = $request->file('audio_file');
        $rawName = $request->input('sound_name') ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $cleanName = preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower(trim($rawName))) . '.mp3';

        $destDir = public_path('audio');
        if (!File::exists($destDir)) {
            File::makeDirectory($destDir, 0755, true);
        }

        $file->move($destDir, $cleanName);

        return back()->with('success', "🎉 Custom audio file '{$cleanName}' uploaded successfully!");
    }
}
