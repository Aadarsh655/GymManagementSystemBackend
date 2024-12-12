<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): Response
    {
        // Get credentials from the request
        $credentials = $request->only('email', 'password');
    
        // Attempt authentication
        if (!Auth::attempt($credentials)) {
            // Return a 401 Unauthorized response for invalid credentials
            return response()->json([
                'message' => 'Unauthorized',
                'error' => 'Invalid credentials or user not found',
                'credentials' => $credentials, // Include for debugging (remove in production)
            ], 401);
        }
        // Regenerate session to prevent session fixation attacks
        $request->session()->regenerate();
    
        // Return no content for successful login
        return response()->noContent();
    }
    

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
