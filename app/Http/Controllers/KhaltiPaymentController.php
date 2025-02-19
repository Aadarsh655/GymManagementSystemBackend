<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class KhaltiPaymentController extends Controller{
    public function purchase(Request $request)
    {
        $eventData = $request->validate([
            'service_id' => 'required',
            'name' => 'required|string',
            'amount' => 'required|numeric',
            'user'=> 'required|string',
        ]);

        $totalAmount = $eventData['amount'] * 100;

        $fields = array(
            "return_url" => "http://localhost:5173/verify-payment",
            "website_url" => "http://localhost:5173/",
            "amount" => $totalAmount,
            "purchase_order_id" => $eventData['service_id'],
            "purchase_order_name" => $eventData['name'],

            "customer_info" => array(
                "name" => $eventData['user'],
            )
        );

        $postfields = json_encode($fields);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://dev.khalti.com/api/v2/epayment/initiate/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $postfields,
            CURLOPT_HTTPHEADER => array(
                'Authorization: key 1bee9fe34f384f73a9dcc1d98dbf844a',
                'Content-Type: application/json',
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $responseArray = json_decode($response, true);

        if (isset($responseArray['payment_url'])) {
            return response()->json([
                'khalti_url' => $responseArray['payment_url']
            ]);
        } else {
            return response()->json(['error' => 'Unexpected response'], 500);
        }
    }
    // public function verify(Request $request)
    // {
    //     // Validate incoming request
    //     $validatedData = $request->validate([
    //         'payment_id' => 'required|string',
    //         'status' => 'required|string',
    //         'transaction_id' => 'required|string',
    //         'amount' => 'required|numeric',
    //         'purchase_order_id' => 'required|string',
    //         'purchase_order_name' => 'required|string',
    //         'user'=>'required|string'
    //     ]);
    
    //     // Get the validated data from the request
    //     $paymentId = $validatedData['payment_id'];
    //     $status = $validatedData['status'];
    //     $transactionId = $validatedData['transaction_id'];
    //     $amount = $validatedData['amount'];
    //     $purchaseOrderId = $validatedData['purchase_order_id'];
    //     $purchaseOrderName = $validatedData['purchase_order_name'];
    //     $user = $validatedData['user'];
    
    //     // Call Khalti API to verify the payment
    //     $response = Http::withHeaders([
    //         'Authorization' => 'key 1bee9fe34f384f73a9dcc1d98dbf844a',
    //     ])->post('https://dev.khalti.com/api/v2/epayment/verify/', [
    //         'payment_id' => $paymentId,
    //     ]);
    
    //     // Log the response for debugging
      
    
    //     // Check if the Khalti verification response is successful
    //     $responseData = $response->json();
    
    //     if (isset($responseData['status']) && $responseData['status'] === 'SUCCESS') {
    //         // Additional validation to make sure the amounts and details match
    //         if ($amount != $responseData['amount']) {
               
    //             return response()->json(['success' => false, 'error' => 'Amount mismatch']);
    //         }
    
    //         if ($purchaseOrderId != $responseData['purchase_order_id']) {
           
    //             return response()->json(['success' => false, 'error' => 'Purchase Order ID mismatch']);
    //         }
    
    //         if ($purchaseOrderName != $responseData['purchase_order_name']) {
    //             return response()->json(['success' => false, 'error' => 'Purchase Order Name mismatch']);
    //         }
    
    //         // If everything matches, proceed with further business logic
    //         return response()->json(['success' => true]);
    //     } else {
    //         // Log the failed verification response
    //         return response()->json(['success' => false, 'error' => $responseData]);
    //     }
    // }
    
    public function verify(Request $request)
    {
        $validatedData = $request->validate([
            'status' => 'required|string',
            'payment_id' => 'required|string',
            'transaction_id' => 'required|string',
            'amount' => 'required|numeric',
            'purchase_order_id' => 'required|string',
            'purchase_order_name' => 'required|string',
            'user' => 'required|numeric',
        ]);
        if ($validatedData['status'] === 'Completed') {
            $amountInCents = $validatedData['amount'];
    
            $existingPayment = Payment::where('user_id', $validatedData['user'])
            ->where(function ($query) {
                $query->where('expire_date', '>=', now()) 
                      ->orWhereNull('expire_date'); 
            })
            ->where('status', 'Paid') 
            ->first();
    
            if ($existingPayment) {
                return response()->json([
                    'message' => 'User already has an active membership. Cannot make another payment until it expires.',
                    'data' => $existingPayment
                ], 400);
            }
    
        if ($validatedData['status'] === 'Completed') {
            $amountInCents = $validatedData['amount']; 
    
            $payment = Payment::create([
                'user_id' => $validatedData['user'], 
                'membership_id' => $validatedData['purchase_order_id'], 
                'amount' => $amountInCents,
                'discount' => 0, 
                'paid_amount' => $amountInCents,
                'status' => 'Paid', 
                'paid_date' => now(),
                'expire_date' => null, 
            ]);
    
            return response()->json([
                'message' => 'Transaction completed successfully.',
                'data' => $payment 
            ]);
        }
        return response()->json([
            'message' => 'Transaction failed.',
        ], 400);
    }
}
}