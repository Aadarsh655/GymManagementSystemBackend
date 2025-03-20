<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
// use App\Http\Controllers\Response;
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
                'Authorization: key fa8a723551f44a73840d7ac3a364c422',
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

    if ($validatedData['status'] !== 'Completed') {
        return response()->json([
            'message' => 'Transaction failed.'
        ], 400);  // Return failure response
    }

    $amountInRupees = $validatedData['amount'] / 100;

    // Check if the user already has an active membership
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

    // Create the payment record if the payment is completed
    $payment = Payment::create([
        'user_id' => $validatedData['user'],
        'membership_id' => $validatedData['purchase_order_id'],
        'amount' => $amountInRupees,
        'discount' => 0,
        'paid_amount' => $amountInRupees,
        'status' => 'Paid',
        'paid_date' => now(),
        'expire_date' => null,
    ]);

    return response()->json([
        'message' => 'Transaction completed successfully.',
        'data' => $payment
    ]);
}

}