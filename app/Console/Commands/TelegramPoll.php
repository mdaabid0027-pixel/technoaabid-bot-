<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Http\Controllers\TelegramController;

class TelegramPoll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:poll';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Long poll Telegram updates continuously and process via TelegramController';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $token = config('services.telegram.token');

        if (!$token) {
            $this->error("TELEGRAM_BOT_TOKEN is not configured in .env!");
            return 1;
        }

        // Single instance lock to prevent duplicate polling processes
        $lockFile = storage_path('app/telegram_poll.lock');
        $fp = fopen($lockFile, 'c+');
        if (!flock($fp, LOCK_EX | LOCK_NB)) {
            $this->error("Another instance of Telegram Poller is already running. Exiting to prevent duplicate replies.");
            fclose($fp);
            return 0;
        }

        // 1. Delete Webhook so getUpdates works properly
        try {
            $res = Http::get("https://api.telegram.org/bot{$token}/deleteWebhook");
            $this->info("Webhook removed: " . json_encode($res->json()));
        } catch (\Exception $e) {
            $this->warn("Failed to delete webhook: " . $e->getMessage());
        }

        $this->info("Bot @" . (env('TELEGRAM_BOT_USERNAME') ?: 'bot') . " is active and listening for messages (Single Instance Lock Acquired)!");

        $offset = 0;
        $controller = app(TelegramController::class);
        $lastExpiryCheck = 0;

        while (true) {
            try {
                // Periodically check expiring keys & purge 48h old sold keys every 5 minutes
                if (time() - $lastExpiryCheck > 300) {
                    $lastExpiryCheck = time();
                    try {
                        \Illuminate\Support\Facades\Artisan::call('keys:check-expiry');
                        
                        // Auto-purge sold keys whose validity expired over 48 hours ago
                        $purged = \App\Models\Key::where('is_sold', true)
                            ->whereNotNull('expires_at')
                            ->where('expires_at', '<', now()->subHours(48))
                            ->delete();
                        if ($purged > 0) {
                            $this->info("Auto-purged {$purged} expired keys (expired > 48h ago).");
                        }
                    } catch (\Throwable $ex) {
                        $this->warn("Expiry check warning: " . $ex->getMessage());
                    }
                }

                $response = Http::timeout(45)->connectTimeout(15)->get("https://api.telegram.org/bot{$token}/getUpdates", [
                    'offset' => $offset,
                    'timeout' => 20,
                ]);

                if ($response->successful() && isset($response->json()['result'])) {
                    $updates = $response->json()['result'];

                    foreach ($updates as $update) {
                        $offset = $update['update_id'] + 1;
                        $updateId = $update['update_id'];

                        $from = $update['message']['from']['first_name'] ?? $update['callback_query']['from']['first_name'] ?? 'User';
                        $text = $update['message']['text'] ?? (isset($update['message']['document']) ? '[Uploaded Document: ' . ($update['message']['document']['file_name'] ?? 'file') . ']' : (isset($update['message']['photo']) ? '[Uploaded Photo]' : ($update['callback_query']['data'] ?? '[Event]')));

                        $this->line("<comment>[" . date('H:i:s') . "]</comment> Update #{$updateId} from {$from}: {$text}");

                        // Create proper Laravel Request instance with JSON content
                        $req = Request::create('/telegram/webhook', 'POST', [], [], [], [
                            'HTTP_CONTENT_TYPE' => 'application/json',
                            'CONTENT_TYPE' => 'application/json'
                        ], json_encode($update));

                        app()->instance('request', $req);
                        $controller->handle($req);
                    }
                }
            } catch (\Exception $e) {
                $this->error("Polling error: " . $e->getMessage());
                sleep(2);
            }
        }
    }
}

