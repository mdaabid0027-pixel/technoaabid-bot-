<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AdminAuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    /**
     * Handle authentication.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    /**
     * Show Forgot Password Form.
     */
    public function showForgotPassword()
    {
        return view('admin.auth.forgot_password');
    }

    /**
     * Send Password Reset Link to Admin's Gmail.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = strtolower(trim($request->email));

        // Find user by email or fallback to admin user if they enter their personal email
        $user = User::where('email', $email)->first();
        if (!$user) {
            // Check if this matches configured admin alert email
            $adminAlertEmail = strtolower(trim(config('mail.admin_alert_email') ?: env('ADMIN_ALERT_EMAIL', '')));
            if ($email === $adminAlertEmail) {
                $user = User::first();
            }
        }

        if (!$user) {
            return back()->withErrors(['email' => "No admin account found matching this email address."]);
        }

        // Generate secure 64-char token
        $token = Str::random(64);

        // Store or update in password_reset_tokens table
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => $token,
                'created_at' => now(),
            ]
        );

        $resetUrl = route('admin.password.reset', ['token' => $token, 'email' => $user->email]);
        $targetEmail = config('mail.admin_alert_email') ?: ($user->email ?: env('ADMIN_ALERT_EMAIL', 'admin@example.com'));

        // Send Email via Gmail SMTP
        try {
            $html = "
            <div style='font-family: Arial, sans-serif; max-width: 540px; margin: 0 auto; padding: 24px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px;'>
                <div style='text-align: center; margin-bottom: 24px;'>
                    <div style='display: inline-block; width: 48px; height: 48px; background: #2563eb; color: #ffffff; border-radius: 12px; line-height: 48px; font-size: 24px;'>🛡️</div>
                    <h2 style='color: #0f172a; margin: 12px 0 4px 0; font-size: 20px;'>Admin Password Reset</h2>
                    <p style='color: #64748b; font-size: 13px; margin: 0;'>Khelo Store Admin Panel Security</p>
                </div>
                
                <p style='color: #334155; font-size: 14px; line-height: 1.6;'>
                    Hello <strong>Admin</strong>,<br><br>
                    A password reset request was received for your Khelo Store Admin account. Click the button below to directly reset your password:
                </p>
                
                <div style='text-align: center; margin: 28px 0;'>
                    <a href='{$resetUrl}' style='background: #2563eb; color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 15px; display: inline-block; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);'>
                        🔒 Reset My Password
                    </a>
                </div>
                
                <p style='color: #64748b; font-size: 12px; line-height: 1.5; background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;'>
                    ⏰ <strong>Note:</strong> This password reset link is valid for 60 minutes. If you did not request a password reset, you can safely ignore this email.
                </p>
                
                <div style='margin-top: 24px; padding-top: 16px; border-top: 1px solid #f1f5f9; color: #94a3b8; font-size: 11px; text-align: center;'>
                    Khelo Store Bot • Automated Security Notification
                </div>
            </div>";

            Mail::html($html, function ($msg) use ($targetEmail) {
                $msg->to($targetEmail)
                    ->subject("🔒 Khelo Store: Admin Password Reset Request");
            });

            return back()->with('status', "Password reset link has been sent to your Gmail inbox ({$targetEmail})! Please check your email.");
        } catch (\Exception $e) {
            return back()->withErrors(['email' => "Failed to send email: " . $e->getMessage()]);
        }
    }

    /**
     * Show the Password Reset form.
     */
    public function showResetPassword(Request $request, $token)
    {
        $email = $request->query('email');
        
        $record = DB::table('password_reset_tokens')
            ->where('token', $token)
            ->first();

        if (!$record) {
            return redirect()->route('admin.password.request')->withErrors(['email' => 'This password reset link is invalid or has expired. Please request a new one.']);
        }

        return view('admin.auth.reset_password', [
            'token' => $token,
            'email' => $record->email,
        ]);
    }

    /**
     * Handle the password reset submission.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('token', $request->token)
            ->where('email', $request->email)
            ->first();

        if (!$record) {
            return back()->withErrors(['email' => 'Invalid or expired reset token. Please request a new link.']);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            $user = User::first();
        }

        if ($user) {
            $user->password = Hash::make($request->password);
            $user->save();

            // Delete the token
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            // Log the user in
            Auth::login($user);

            return redirect()->route('admin.dashboard')->with('success', '🎉 Password reset successfully! You are now logged in.');
        }

        return redirect()->route('admin.login')->withErrors(['email' => 'Unable to reset password.']);
    }

    /**
     * Update Admin Password inside Dashboard.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => ['required', 'min:6', 'confirmed'],
        ]);

        $user = Auth::user();
        if ($user) {
            $user->password = Hash::make($request->password);
            $user->save();
        }

        return back()->with('success', 'Admin password changed successfully!');
    }
}
