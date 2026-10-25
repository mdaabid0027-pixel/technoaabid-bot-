<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class OrderAlertService
{
    /**
     * Send short, clean email alert to Admin upon product purchase / renewal.
     */
    public static function sendSaleAlert(array $data): void
    {
        try {
            $adminEmail = config('mail.admin_alert_email') ?: env('ADMIN_ALERT_EMAIL', 'admin@example.com');

            if (empty($adminEmail)) {
                return;
            }

            $type = $data['type'] ?? 'NEW SALE';
            $productName = $data['product_name'] ?? 'Gaming Key';
            $duration = $data['duration'] ?? '';
            $licenseKey = $data['license_key'] ?? '';
            $price = (float)($data['price'] ?? 0);
            $cost = (float)($data['cost'] ?? 0);
            
            $profitStr = '';
            if ($price > 0 && $cost > 0) {
                $profit = $price - $cost;
                $profitStr = ($profit >= 0) ? " (Profit: +₹" . round($profit) . ")" : " (Loss: -₹" . round(abs($profit)) . ")";
            }

            $cleanPrice = ($price == (int)$price) ? (int)$price : number_format($price, 2, '.', '');

            $userName = $data['user_name'] ?? 'Customer';
            $userHandle = !empty($data['user_username']) ? ' (@' . ltrim($data['user_username'], '@') . ')' : '';
            $customerStr = $userName . $userHandle;

            // Short, clean subject
            $durShort = $duration ? " ({$duration})" : "";
            $subject = "Khelo Store: {$productName}{$durShort} - ₹{$cleanPrice} ({$userName})";

            $html = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='utf-8'>
                <style>
                    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 16px; color: #0f172a; }
                    .card { max-width: 440px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.04); }
                    .title { font-size: 13px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px; }
                    .row { font-size: 13px; margin-bottom: 8px; line-height: 1.4; }
                    .label { color: #64748b; font-weight: 600; }
                    .val { font-weight: 700; color: #0f172a; }
                    .key-box { font-family: monospace; font-size: 14px; font-weight: 700; background: #f1f5f9; color: #2563eb; padding: 4px 8px; border-radius: 6px; border: 1px solid #e2e8f0; display: inline-block; word-break: break-all; }
                </style>
            </head>
            <body>
                <div class='card'>
                    <div class='title'>{$type}</div>
                    
                    <div class='row'>
                        <span class='label'>Product:</span> 
                        <span class='val'>{$productName}" . ($duration ? " ({$duration})" : "") . "</span>
                    </div>

                    " . ($licenseKey ? "
                    <div class='row'>
                        <span class='label'>Key:</span> 
                        <span class='key-box'>{$licenseKey}</span>
                    </div>
                    " : "") . "

                    <div class='row'>
                        <span class='label'>Price:</span> 
                        <span class='val' style='color: #059669;'>₹{$cleanPrice}</span>
                        <span style='color: #64748b; font-size: 12px;'>{$profitStr}</span>
                    </div>

                    <div class='row' style='margin-bottom: 0;'>
                        <span class='label'>Customer:</span> 
                        <span class='val'>{$customerStr}</span>
                    </div>
                </div>
            </body>
            </html>
            ";

            Mail::html($html, function ($message) use ($adminEmail, $subject) {
                $message->to($adminEmail)
                    ->subject($subject);
            });

        } catch (\Throwable $e) {
            Log::error("Failed to send sale alert email: " . $e->getMessage());
        }
    }

    /**
     * Send instant Out of Stock Alert email to Admin when a customer tries to buy an unavailable key.
     */
    public static function sendOutOfStockAlert(array $data): void
    {
        try {
            $adminEmail = config('mail.admin_alert_email') ?: env('ADMIN_ALERT_EMAIL', 'admin@example.com');

            if (empty($adminEmail)) {
                return;
            }

            $productName = $data['product_name'] ?? 'Gaming Key';
            $duration = $data['duration'] ?? '';
            $price = (float)($data['price'] ?? 0);
            $cleanPrice = ($price == (int)$price) ? (int)$price : number_format($price, 2, '.', '');

            $userName = $data['user_name'] ?? 'Customer';
            $userHandle = !empty($data['user_username']) ? ' (@' . ltrim($data['user_username'], '@') . ')' : '';
            $customerStr = $userName . $userHandle;
            $userBalance = isset($data['user_balance']) ? '₹' . number_format((float)$data['user_balance'], 2) : 'N/A';

            $durShort = $duration ? " ({$duration})" : "";
            $subject = "⚠️ OUT OF STOCK: {$productName}{$durShort} - {$userName} tried to purchase";

            $adminUrl = url('/admin/keys');

            $html = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='utf-8'>
                <style>
                    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #fef2f2; margin: 0; padding: 16px; color: #0f172a; }
                    .card { max-width: 460px; margin: 0 auto; background: #ffffff; border: 1px solid #fecaca; border-radius: 12px; padding: 20px 22px; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.08); }
                    .badge { display: inline-block; background: #dc2626; color: #ffffff; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.8px; padding: 4px 10px; border-radius: 20px; margin-bottom: 12px; }
                    .title { font-size: 17px; font-weight: 800; color: #991b1b; margin-bottom: 14px; line-height: 1.3; }
                    .info-box { background: #fff5f5; border: 1px solid #fee2e2; border-radius: 8px; padding: 12px 14px; margin-bottom: 16px; }
                    .row { font-size: 13.5px; margin-bottom: 8px; line-height: 1.4; }
                    .row:last-child { margin-bottom: 0; }
                    .label { color: #64748b; font-weight: 600; min-width: 90px; display: inline-block; }
                    .val { font-weight: 700; color: #0f172a; }
                    .price-val { color: #dc2626; font-weight: 800; font-size: 15px; }
                    .action-box { background: #f8fafc; border-left: 4px solid #dc2626; padding: 10px 14px; border-radius: 0 6px 6px 0; font-size: 12.5px; color: #475569; margin-bottom: 16px; }
                    .btn { display: block; text-align: center; background: #dc2626; color: #ffffff !important; text-decoration: none; font-weight: 700; font-size: 14px; padding: 11px 16px; border-radius: 8px; box-shadow: 0 2px 4px rgba(220, 38, 38, 0.2); margin-top: 14px; }
                </style>
            </head>
            <body>
                <div class='card'>
                    <div class='badge'>⚠️ Out Of Stock Alert</div>
                    <div class='title'>Customer Attempted to Buy Out-of-Stock Key</div>
                    
                    <div class='info-box'>
                        <div class='row'>
                            <span class='label'>Game/Product:</span> 
                            <span class='val'>{$productName}" . ($duration ? " ({$duration})" : "") . "</span>
                        </div>
                        <div class='row'>
                            <span class='label'>Plan Price:</span> 
                            <span class='price-val'>₹{$cleanPrice}</span>
                        </div>
                        <div class='row'>
                            <span class='label'>Customer:</span> 
                            <span class='val'>{$customerStr}</span>
                        </div>
                        <div class='row'>
                            <span class='label'>User Balance:</span> 
                            <span class='val' style='color: #0284c7;'>{$userBalance}</span>
                        </div>
                    </div>

                    <a href='{$adminUrl}' class='btn' target='_blank'>📦 Refill Keys in Admin Panel</a>
                </div>
            </body>
            </html>
            ";

            Mail::html($html, function ($message) use ($adminEmail, $subject) {
                $message->to($adminEmail)
                    ->subject($subject);
            });

        } catch (\Throwable $e) {
            Log::error("Failed to send out-of-stock alert email: " . $e->getMessage());
        }
    }
}

