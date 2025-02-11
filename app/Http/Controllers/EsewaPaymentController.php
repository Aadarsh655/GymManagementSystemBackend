<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EsewaPaymentController extends Controller
{
    public function verifyPayment(Request $request)
    {

        // Extract the payment details from eSewa's callback
        $paymentData = $request->all();

        // Log the payment verification request for debugging
        Log::info('Payment Verification Request:', $paymentData);

        // You will need to verify the transaction using eSewa's verification API.
        // You can refer to eSewa's API documentation for this part.
        try {
            // Send the data to eSewa's verification API
            $response = Http::asForm()->post('https://www.esewa.com.np/epay/main/v2/verify', $paymentData);

            // Check if the response is successful
            if ($response->successful() && $response->json()['status'] == 'success') {
                // Handle successful payment (e.g., update the database)
                return response()->json(['status' => 'success', 'message' => 'Payment verified successfully.']);
            } else {
                return response()->json(['status' => 'failed', 'message' => 'Payment verification failed.']);
            }
        } catch (\Exception $e) {
            Log::error('Payment verification failed: ', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'failed', 'message' => 'Payment verification failed due to an error.']);
        }
    }
    public function initializePayment(Request $request)
    {
        $transactionId = Str::uuid()->toString();
        $merchantSecretKey = '8gBm/:&EnhH.1/q'; // Replace with your eSewa demo secret key
    
        $paymentData = [
            'amount' => $request->input('amount'),
            'tax_amount' => $request->input('tax_amount', 0),
            'total_amount' => $request->input('total_amount'),
            'transaction_uuid' => $transactionId,
            'product_code' => 'EPAYTEST',
            'product_service_charge' => $request->input('product_service_charge', 0),
            'product_delivery_charge' => $request->input('product_delivery_charge', 0),
            'success_url' => $request->input('success_url'),
            'failure_url' => $request->input('failure_url'),
            'signed_field_names' => 'total_amount,transaction_uuid,product_code',
        ];
    
        // 🔹 Generate Signature
        $signatureString = "";
        foreach (explode(',', $paymentData['signed_field_names']) as $field) {
            $signatureString .= $field . "=" . $paymentData[$field] . ",";
        }
        $signatureString = rtrim($signatureString, ",");
    
        // 🔹 Add Signature to payment data
        $paymentData['signature'] = base64_encode(hash_hmac('sha256', $signatureString, $merchantSecretKey, true));
    
        try {
            // Send payment data to the eSewa demo endpoint
            $response = Http::asForm()->post('https://uat.esewa.com.np/api/epay/main/v2/form', $paymentData);
    
            if ($response->successful()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Payment initialized successfully.',
                    'payment_url' => 'https://uat.esewa.com.np/epay/main',
                ]);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Payment initialization failed.',
                    'error_details' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error initializing eSewa payment:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred.',
                'error_details' => $e->getMessage(),
            ]);
        }
    }
}    