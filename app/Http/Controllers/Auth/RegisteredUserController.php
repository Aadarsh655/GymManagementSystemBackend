<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserWelcomeMail;

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
            'image' => ['nullable'],
            'age' => ['required', 'integer', 'min:0', 'max:120'],
            'gender' => ['required', 'string', 'in:Male,Female'], 
            'blood_group' => ['required', 'string', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-']
        ]);
        $randomPassword = Str::random(16);
        $photoPath = null;
        if ($request->hasFile('image')) {
            $photoPath = $request->file('image')->store('image', 'public');
            
        }
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($randomPassword), 
            'image' => $photoPath,
            'role' => $request->role, 
            'age' => $request->age,
            'gender' => $request->gender,
            'blood_group' => $request->blood_group,
        ]);
     
        event(new Registered($user));
        Mail::to($user->email)->send(new UserWelcomeMail($user, $randomPassword));
        return response()->json(['message' => 'User registered successfully!', 'user' => $user], 201);
    }
        
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
   
        $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role' => ['sometimes', 'string', 'in:Member,Admin'],
            'image' => ['nullable'],
            'age' => ['sometimes', 'integer', 'min:0', 'max:120'],
            'gender' => ['sometimes', 'string', 'in:Male,Female'],
            'blood_group' => ['sometimes', 'string', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
        ]);
    
        if ($request->hasFile('image')) {
            if ($user->image) {
                Storage::disk('public')->delete($user->image);
            }
            $photoPath = $request->file('image')->store('images', 'public');
            $user->image = $photoPath; 
        }
        $user->fill($request->except('image'));  
        $user->save(); 
        return response()->json([
            'message' => 'User updated successfully!',
            'user' => array_merge($user->toArray(), [
                'id' => $user->id,
                'image_url' => $user->image ? asset('storage/' . $user->image) : null,
            ]),
        ], 200);
    }
    
    public function index(){
        $user = User::select('id','name','email','role','image','age','gender','blood_group','status')
        ->get()->map(function($user){
           $user->image_url = $user->image ? url('storage/'  . $user->image) : null;
           return $user; 
        });
        return response()->json($user);
        }

        public function getLoggedInUserDetails(Request $request)
    {
        $user = Auth::user(); 

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'image_url' => $user->image ? url('storage/' . $user->image) : null,
            'age' => $user->age,
            'gender' => $user->gender,
            'blood_group' => $user->blood_group,
        ]);}

    public function archive($id)
    {
        $user = User::findOrFail($id);
        $user->status = 'Inactive';
        $user->save();

        return response()->json([
            'message' => 'User archived successfully!',
            'user' => $user
        ]);
    }
}