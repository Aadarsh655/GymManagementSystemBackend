<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id', // Validate user_id exists in the users table
            'membership_id' => 'required|exists:memberships,membership_id', // Validate membership_id exists in the memberships table
            'discount' => 'nullable|integer|min:0',
            'paid_amount' => 'nullable|integer|min:0',
            'paid_date' => 'nullable|date',
            'expire_date' => 'nullable|date',
        ]);
    
        // Retrieve the membership and its price (amount)
        $membership = \App\Models\Membership::findOrFail($validatedData['membership_id']);
    
        // Retrieve the user details
        $user = \App\Models\User::findOrFail($validatedData['user_id']);
    
        // Create the payment record
        $payment = \App\Models\Payment::create([
            'user_id' => $user->id,
            'membership_id' => $membership->membership_id,
            'amount' => $membership->price, // Automatically set the amount from the membership
            'discount' => $validatedData['discount'] ?? 0,
            'paid_amount' => $validatedData['paid_amount'] ?? 0,
            'paid_date' => $validatedData['paid_date'] ?? now(),
            'expire_date' => $validatedData['expire_date'] ?? null,
        ]);
    
        // Refetch the payment with virtual columns
        $payment = \App\Models\Payment::select('*')->find($payment->payment_id);
    
        return response()->json([
            'message' => 'Payment created successfully.',
            'payment' => [
                'payment_id' => $payment->payment_id,
                'user_id' => $payment->user_id,
                'user_name' => $user->name,
                'membership_name' => $membership->membership_name, // Show only membership name
                'amount' => $payment->amount,
                'discount' => $payment->discount,
                'paid_amount' => $payment->paid_amount,
                'due_amount' => $payment->due_amount, // Virtual column
                'status' => $payment->status,         // Virtual column
                'paid_date' => $payment->paid_date,
                'expire_date' => $payment->expire_date,
            ],
        ], 201);
    }
    

}
