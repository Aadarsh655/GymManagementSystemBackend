<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
class CountUserController extends Controller
{
    public function countUsers(){
        $count = User::count();
        return response()->json([
            'success'=>true,
            'user_count'=>$count
        ]);
    }
}
