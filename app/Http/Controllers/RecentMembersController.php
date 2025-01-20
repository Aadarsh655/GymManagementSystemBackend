<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
class RecentMembersController extends Controller
{
    public function getRecentMembers(){
        try{
        $recentMembers = User::where('created_at', ">=",Carbon::now()->subDays(7))->count();

        return response()->json([
            'success'=> true,
            'recent_members'=>$recentMembers
        ],200);

    }
    catch(\Exception $e){
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch recent members.',
            'error' => $e->getMessage()
        ],500);
    }
}
}
