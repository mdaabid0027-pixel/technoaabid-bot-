<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TelegramUser;
use App\Models\Transaction;
use App\Models\Spin;
use Illuminate\Support\Facades\DB;

class AdminRewardsAuditController extends Controller
{
    /**
     * Display paginated Rewards & Claims Audit page.
     */
    public function index(Request $request)
    {
        $search = trim($request->query('search', ''));

        $query = TelegramUser::query()
            ->withSum(['transactions as spin_claims_total' => function($q) {
                $q->where('type', 'credit')->where(function($sub) {
                    $sub->where('description', 'LIKE', '%Spin%')->orWhere('description', 'LIKE', '%Wheel%');
                });
            }], 'amount')
            ->withSum(['transactions as referral_claims_total' => function($q) {
                $q->where('type', 'credit')->where('description', 'LIKE', '%Referral%');
            }], 'amount')
            ->withMax(['transactions as last_claim_date' => function($q) {
                $q->where('type', 'credit')->where(function($sub) {
                    $sub->where('description', 'LIKE', '%Spin%')
                        ->orWhere('description', 'LIKE', '%Wheel%')
                        ->orWhere('description', 'LIKE', '%Referral%');
                });
            }], 'created_at');

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('username', 'LIKE', "%{$search}%")
                  ->orWhere('telegram_id', 'LIKE', "%{$search}%");
            });
        }

        $claimers = $query->having('spin_claims_total', '>', 0)
            ->orHaving('referral_claims_total', '>', 0)
            ->orderByDesc(DB::raw('COALESCE(spin_claims_total, 0) + COALESCE(referral_claims_total, 0)'))
            ->paginate(15)
            ->withQueryString();

        $lifetimeClaimsDisbursed = Transaction::where('type', 'credit')
            ->where(function($q) {
                $q->where('description', 'LIKE', '%Spin%')
                  ->orWhere('description', 'LIKE', '%Wheel%')
                  ->orWhere('description', 'LIKE', '%Referral%');
            })->sum('amount');

        return view('admin.rewards.index', compact('claimers', 'lifetimeClaimsDisbursed', 'search'));
    }

    /**
     * Clear reward audit logs for a single user (without deducting current wallet balance).
     */
    public function destroy($id)
    {
        $user = TelegramUser::findOrFail($id);

        // Delete reward transactions and spin logs for this user to clear audit record
        Transaction::where('telegram_user_id', $user->id)
            ->where('type', 'credit')
            ->where(function($q) {
                $q->where('description', 'LIKE', '%Spin%')
                  ->orWhere('description', 'LIKE', '%Wheel%')
                  ->orWhere('description', 'LIKE', '%Referral%');
            })->delete();

        Spin::where('telegram_user_id', $user->id)->delete();

        $user->total_spin_won = 0;
        $user->total_spins = 0;
        $user->referral_earnings = 0;
        $user->save();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Audit record for {$user->first_name} cleared successfully!"
            ]);
        }

        return redirect()->back()->with('success', "Audit record for {$user->first_name} cleared successfully!");
    }

    /**
     * Bulk clear reward audit logs for selected users.
     */
    public function bulkClear(Request $request)
    {
        $userIds = $request->input('user_ids', []);

        if (empty($userIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No audit records selected.'
            ], 422);
        }

        Transaction::whereIn('telegram_user_id', $userIds)
            ->where('type', 'credit')
            ->where(function($q) {
                $q->where('description', 'LIKE', '%Spin%')
                  ->orWhere('description', 'LIKE', '%Wheel%')
                  ->orWhere('description', 'LIKE', '%Referral%');
            })->delete();

        Spin::whereIn('telegram_user_id', $userIds)->delete();

        TelegramUser::whereIn('id', $userIds)->update([
            'total_spin_won' => 0,
            'total_spins' => 0,
            'referral_earnings' => 0
        ]);

        return response()->json([
            'success' => true,
            'message' => count($userIds) . " audit record(s) cleared successfully!"
        ]);
    }
}
