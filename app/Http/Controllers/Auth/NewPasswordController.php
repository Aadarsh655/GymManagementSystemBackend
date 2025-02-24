<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class NewPasswordController extends Controller
{
 
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            // 'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->string('password')),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status != Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json(['status' => __($status)]);
    }

    public function changePassword(Request $request): JsonResponse
{
    $request->validate([
        'current_password' => ['required'],
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    $user = $request->user(); // Get the authenticated user

    // Check if the current password is correct
    if (!Hash::check($request->input('current_password'), $user->password)) {
        throw ValidationException::withMessages([
            'current_password' => ['The provided password does not match your current password.'],
        ]);
    }

    // Update the password
    $user->forceFill([
        'password' => Hash::make($request->input('password')),
        'remember_token' => Str::random(60), // Optional, for token-based authentication
    ])->save();

    return response()->json(['status' => __('Password changed successfully.')]);
}

}
