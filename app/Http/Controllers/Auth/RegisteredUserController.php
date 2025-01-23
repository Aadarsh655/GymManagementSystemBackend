<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'role' => ['required', 'string', 'in:Member,Admin'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:9048'],
            'age' => ['required', 'integer', 'min:0', 'max:120'], // Age validation
            'gender' => ['required', 'string', 'in:Male,Female'], // Gender validation
            'blood_group' => ['required', 'string', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-']
            
        ]);
        
        $photoPath = null;
        if ($request->hasFile('image')) {
            $photoPath = $request->file('image')->store('image', 'public');
        }
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make('Test@123'), // Set default password here
            'image' => $photoPath,
            'role' => $request->role, 
            'age' => $request->age,
            'gender' => $request->gender,
            'blood_group' => $request->blood_group,
        ]);
        event(new Registered($user));
        return response()->json(['message' => 'User registered successfully!', 'user' => $user], 201);

    }
    public function index(){
        $user = User::select('id','name','email','role','image','age','gender','blood_group')
        ->get()->map(function($user){
           $user->image_url = $user->image ? url('storage/'  . $user->image) : null;
           $user->status = 'Active';
           return $user; 
        });
        return response()->json($user);
        }
}
