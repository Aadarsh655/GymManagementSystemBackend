<?php

namespace App\Http\Controllers\Notice;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    public function index(){
    return response()->json(Notice::latest()->get());
    }
    public function store(Request $request){
        $request->validate([
            'message'=>'required|string',
        ]);
        $notice = Notice::create([
            'message'=>$request->message,
            'posted_by'=>'Admin',
            'date'=>now(),
        ]);
        return response()->json($notice, 201);
    }
}
