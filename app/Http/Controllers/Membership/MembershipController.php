<?php

namespace App\Http\Controllers\Membership;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use Illuminate\Http\Request;

class MembershipController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'membership_name' => 'required|string|unique:memberships,membership_name|max:255',
            'price' => 'required|numeric|min:0',
            'facilities' => 'nullable|array',
            'status' => 'required|in:Active,Inactive',
        ]);

        $membership = Membership::create($validatedData);

        return response()->json([
            'message' => 'Membership created successfully.',
            'membership' => $membership,
        ], 201);
    }
    public function index(){
        $membership=Membership::select('membership_id','membership_name','price','facilities','status')->get()->map(function($membership){
            $membership->status="Active";
            return $membership;
        });
        return response()->json($membership);
    }
}
