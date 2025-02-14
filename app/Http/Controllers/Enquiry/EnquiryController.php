<?php

namespace App\Http\Controllers\Enquiry;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EnquiryController extends Controller
{
    public function store(Request $request){
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'comment' => 'required|string',
        ]);
        $enquiry = Enquiry::create($validated);
        return response() -> json(['message' => 'Enquiry Submitted Successfully']);

    }
    public function reply(Request $request, $id){
        $enquiry = Enquiry::findOrFail($id);
        $validated = $request->validate([
            'reply' => 'required|string',
        ]);
        $enquiry->reply = $validated['reply'];
        $enquiry->save();
        Mail::raw($validated['reply'], function ($message) use ($enquiry) {
            $message->to($enquiry->email)
                    ->subject('Reply to Your Enquiry');
        });

        return response()->json(['message' => 'Reply sent successfully!']);

    }
    public function index()
    {
        $enquiries = Enquiry::all(); // Retrieve all enquiries from the database
        return response()->json($enquiries); // Return them as a JSON response
    }
}


