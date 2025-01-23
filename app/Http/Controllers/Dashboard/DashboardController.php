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

            return response()->json([
                'success'=> true,
                'data' => [
                    'user_count'=> $userCount,
                    'pending_amount'=> $pendingAmount,
                    'recent_members'=> $recentMembers,
                    'monthly_growth' =>$monthlyGrowth,
                    'membership_breakdown' => $membershipBreakdown,
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
