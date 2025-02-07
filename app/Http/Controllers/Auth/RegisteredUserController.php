<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;


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
    
    // public function update(Request $request, $id)
    // {
    //     $user = User::findOrFail($id);

    //     $request->validate([
    //         'name' => ['sometimes', 'string', 'max:255'],
    //         'email' => ['sometimes', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
    //         'role' => ['sometimes', 'string', 'in:Member,Admin'],
    //         'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:9048'],
    //         'age' => ['sometimes', 'integer', 'min:0', 'max:120'],
    //         'gender' => ['sometimes', 'string', 'in:Male,Female'],
    //         'blood_group' => ['sometimes', 'string', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
    //     ]);

        
    //     if ($request->hasFile('image')) {
    //         if ($user->image) {
    //             Storage::disk('public')->delete($user->image);
    //         }
    //         $photoPath = $request->file('image')->store('images', 'public');
    //         $user->image = $photoPath; 
    //     }
    //     $user->fill($request->except('image'));
    //     $user->name = $request->name;
    //     $user->age = $request->age;
    //     $user->gender = $request->gender;
    //     $user->image = $request->image;
    //     $user->update();

    // return response()->json([
    //     'message' => 'User updated successfully!',
    //     'user' => array_merge($user->toArray(), [
    //         'id'=>$user->id,
    //         'image_url' => $user->image ? asset('storage/' . $user->image) : null,
    //     ]),
    // ], 200);
    // }    
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
   
        $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role' => ['sometimes', 'string', 'in:Member,Admin'],
            'image' => ['sometimes','nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:9048'],
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
        $user = User::select('id','name','email','role','image','age','gender','blood_group')
        ->get()->map(function($user){
           $user->image_url = $user->image ? url('storage/'  . $user->image) : null;
           $user->status = 'Active';
           return $user; 
        });
        return response()->json($user);
        }
}