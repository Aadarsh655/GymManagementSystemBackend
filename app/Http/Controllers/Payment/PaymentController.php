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

        $existingPayment = Payment::where('user_id', $validatedData['user_id'])
        ->where('membership_id', $validatedData['membership_id'])
        ->first();

    if ($existingPayment) {
        return response()->json([
            'message' => 'Payment for this user and membership already exists.',
        ], 422); 
    }
        // Retrieve the membership and its price (amount)
        $membership = \App\Models\Membership::findOrFail($validatedData['membership_id']);
    
        // Retrieve the user details
        $user = \App\Models\User::findOrFail($validatedData['user_id']);
    
        // Create the payment record
        $payment = \App\Models\Payment::create([
            //'user_id' => $user->name,
            'user_id' => $validatedData['user_id'],
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
                'membership_id'=>$membership->membership_id,
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
    public function update(Request $request, $payment_id)
    {
        $validatedData = $request->validate([
            'user_id' => 'sometimes|exists:users,id', // Ensure user exists
            'membership_id' => 'sometimes|exists:memberships,membership_id', // Ensure membership exists
            'discount' => 'nullable|integer|min:0',
            'paid_amount' => 'nullable|integer|min:0',
            'paid_date' => 'nullable|date',
            'expire_date' => 'nullable|date',
        ]);
        $payment = Payment::findOrFail($payment_id);
        if (isset($validatedData['membership_id'])) {
            $membership = \App\Models\Membership::find($validatedData['membership_id']);
            if ($membership) {
                $validatedData['amount'] = $membership->price;
            } else {
                return response()->json(['message' => 'Invalid membership selected.'], 422);
            }
        } else {
            $membership = $payment->membership; 
        }
        $validatedData['due_amount'] = ($validatedData['amount'] ?? $payment->amount) 
            - ($validatedData['discount'] ?? $payment->discount) 
            - ($validatedData['paid_amount'] ?? $payment->paid_amount);
        $payment->update($validatedData);
        $user = $payment->user;
        return response()->json([
            'message' => 'Payment updated successfully.',
            'payment' => [
                'payment_id' => $payment->payment_id,
                'user_id' => $payment->user_id,
                'user_name' => $user ? $user->name : 'Unknown User',
                'membership_id' => $membership->membership_id,
                'membership_name' => $membership ? $membership->membership_name : 'Unknown Membership',
                'amount' => $payment->amount,
                'discount' => $payment->discount,
                'paid_amount' => $payment->paid_amount,
                'due_amount' => $payment->due_amount,
                'status' => $payment->status,
                'paid_date' => $payment->paid_date,
                'expire_date' => $payment->expire_date,
            ],
        ]);
    }
    public function destroy($payment_id)
    {
        $payment = Payment::findOrFail($payment_id);
        $payment->delete();

        return response()->json([
            'message' => 'Payment deleted successfully.',
            'payment_id' => $payment_id,
        ]);
    }

    public function index(){
        $payments = Payment::select('payment_id', 'user_id', 'membership_id', 'amount', 'discount', 'paid_amount', 'due_amount', 'status', 'paid_date', 'expire_date')->get();
        // Map through payments to include user and membership details
        $payments = $payments->map(function ($payment) {
            $user = \App\Models\User::find($payment->user_id); // Fetch user
            $membership = \App\Models\Membership::find($payment->membership_id); // Fetch membership
            return [
                'payment_id' => $payment->payment_id,
                'user_id' => $payment->user_id,
                'user_name' => $user ? $user->name : 'Unknown User',
                'membership_id'=>$membership->membership_id, // Handle null users
                'membership_name' => $membership ? $membership->membership_name : 'Unknown Membership', // Handle null memberships
                'amount' => $payment->amount,
                'discount' => $payment->discount,
                'paid_amount' => $payment->paid_amount,
                'due_amount' => $payment->due_amount,
                'status' => $payment->status,
                'paid_date' => $payment->paid_date,
                'expire_date' => $payment->expire_date,
            ];
        });
    
        // Return payments as a JSON response
        return response()->json([
            'message' => 'Payments fetched successfully.',
            'payments' => $payments,
        ]);
    }
    
}
                                                                         