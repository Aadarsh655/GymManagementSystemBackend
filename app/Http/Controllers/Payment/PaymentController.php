<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id', 
            'membership_id' => 'required|exists:memberships,membership_id',
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
 
        $membership = \App\Models\Membership::findOrFail($validatedData['membership_id']);
    
        $user = \App\Models\User::findOrFail($validatedData['user_id']);
    
        $payment = \App\Models\Payment::create([
            //'user_id' => $user->name,
            'user_id' => $validatedData['user_id'],
            'membership_id' => $membership->membership_id,
            'amount' => $membership->price, 
            'discount' => $validatedData['discount'] ?? 0,
            'paid_amount' => $validatedData['paid_amount'] ?? 0,
            'paid_date' => $validatedData['paid_date'] ?? now(),
            'expire_date' => $validatedData['expire_date'] ?? null,
        ]);

        $payment = \App\Models\Payment::select('*')->find($payment->payment_id);
    
        return response()->json([
            'message' => 'Payment created successfully.',
            'payment' => [
                'payment_id' => $payment->payment_id,
                'user_id' => $payment->user_id,
                'user_name' => $user->name,
                'membership_id'=>$membership->membership_id,
                'membership_name' => $membership->membership_name, 
                'amount' => $payment->amount,
                'discount' => $payment->discount,
                'paid_amount' => $payment->paid_amount,
                'due_amount' => $payment->due_amount, 
                'status' => $payment->status,        
                'paid_date' => $payment->paid_date,
                'expire_date' => $payment->expire_date,
            ],
        ], 201);
    }
    public function update(Request $request, $payment_id)
    {
        $validatedData = $request->validate([
            'user_id' => 'sometimes|exists:users,id', 
            'membership_id' => 'sometimes|exists:memberships,membership_id', 
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
    public function getUserPayments(Request $request)
    {
        $user = Auth::user();
    
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }
    
        $payments = Payment::with('membership')
            ->where('user_id', $user->id)
            ->get();
    
        $formattedPayments = $payments->map(function ($payment) use ($user) {
            return [
                'payment_id' => $payment->payment_id,
                'user_id' => $payment->user_id,
                'user_name' => $user->name,
                'membership_id' => $payment->membership->membership_id ?? null,
                'membership_name' => $payment->membership->membership_name ?? 'Unknown Membership',
                'amount' => $payment->amount,
                'discount' => $payment->discount,
                'paid_amount' => $payment->paid_amount,
                'due_amount' => $payment->due_amount,
                'status' => $payment->status,
                'paid_date' => $payment->paid_date,
                'expire_date' => $payment->expire_date,
            ];
        });
    
        return response()->json([
            'message' => 'Payments fetched successfully.',
            'payments' => $formattedPayments,
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
        $payments = $payments->map(function ($payment) {
            $user = \App\Models\User::find($payment->user_id); 
            $membership = \App\Models\Membership::find($payment->membership_id); 
            return [
                'payment_id' => $payment->payment_id,
                'user_id' => $payment->user_id,
                'user_name' => $user ? $user->name : 'Unknown User',
                'membership_id'=>$membership->membership_id, 
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
                                                                         