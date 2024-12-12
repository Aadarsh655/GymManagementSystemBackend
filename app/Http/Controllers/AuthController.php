<?php

// namespace App\Http\Controllers;

// use App\Models\User;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Hash;

// class AuthController extends Controller
// {
//     public function register(Request $request)
//     {
//         $fields = $request->validate([
//             'name' => 'required|max:255',
//             'email' => 'required|email|unique:users',
//             'password' => 'required|confirmed'
//         ]);

//         $user = User::create($fields);

//         $token = $user -> createToken($request->name);

//         return[
//             'user' => $user,
//             'token' => $token->plainTextToken
//         ] ;
//     }


//     public function login(Request $request)
//     {
//         $fields = $request->validate([
//             'email' => 'required|email',
//             'password' => 'required'
//         ]);
    
//         $user = User::where('email', $request->email)->first();
    
//         if (!$user) {
//             return response()->json([
//                 'message' => 'Login failed. Please check your credentials.'
//             ], 404); // 404 Not Found
//         }
    
//         if (!Hash::check($request->password, $user->password)) {
//             return response()->json([
//                 'message' => 'Login failed. Please check your credentials.'
//             ], 401); // 401 Unauthorized
//         }
    
//         $token = $user->createToken($user->name)->plainTextToken;
    
//         return response()->json([
//             'user' => $user,
//             'token' => $token
//         ], 200);
//     }
    

//     public function logout(Request $request)
//     {
//         $request->user()->tokens()->delete();

//         return[
//             'message' => 'You are logged out.'
//         ];
//     }
// }
