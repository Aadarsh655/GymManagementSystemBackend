<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function dashboardCounts(){
        try{
            $userCount = User::count();
            $pendingAmount = Payment::sum('due_amount');
            $recentMembers = User::where('created_at','>=', Carbon::now()->subDays(7))->count();
            $monthlyGrowth = User::selectRaw('MONTH(created_at) as month, COUNT(*) as count')->whereYear('created_at',Carbon::now()->year)->groupBy('month')->orderBy('month')->get();
            $membershipBreakdown = DB::table('payments')
                    ->join('memberships', 'payments.membership_id', '=', 'memberships.membership_id')
                    ->select('memberships.membership_name as membership_name', DB::raw('COUNT(payments.membership_id) as count'))
                    ->groupBy('memberships.membership_name')
                    ->get();
                $expiredUsers = User::join('payments', 'users.id', '=', 'payments.user_id')
    ->whereDate('payments.expire_date', '<', Carbon::now())
    ->select('users.id', 'users.name', 'users.image', 'payments.expire_date', 'payments.amount')
    ->get();
    $expiringUsers = User::join('payments', 'users.id', '=', 'payments.user_id')
    ->where('payments.expire_date', '<', Carbon::now()->addDays(7)) // Expiring in less than 7 days
    ->where('payments.expire_date', '>=', Carbon::now()) // Still active (not expired yet)
    ->select('users.id', 'users.name', 'users.image', 'payments.expire_date', 'payments.amount')
    ->get();


            return response()->json([
                'success'=> true,
                'data' => [
                    'user_count'=> $userCount,
                    'pending_amount'=> $pendingAmount,
                    'recent_members'=> $recentMembers,
                    'monthly_growth' =>$monthlyGrowth,
                    'membership_breakdown' => $membershipBreakdown,
                    'expired_members' => $expiredUsers,
                    'expiring_members'=>$expiringUsers,
                ],
            ],200);
        }
        catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
