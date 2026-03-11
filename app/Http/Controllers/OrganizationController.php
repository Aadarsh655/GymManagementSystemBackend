<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrganizationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $organization = Organization::first();
        if ($organization) {
            $organization->logo_url = $organization->logo ? url('storage/' . $organization->logo) : null;
        }
        return response()->json($organization);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'facebook' => 'nullable|string|max:255',
            'instagram' => 'nullable|string|max:255',
            'twitter' => 'nullable|string|max:255',
            'linkedin' => 'nullable|string|max:255',
            'tiktok' => 'nullable|string|max:255',
            'other' => 'nullable|array',
        ]);

        if ($request->hasFile('logo')) {
            $validatedData['logo'] = $request->file('logo')->store('organizations', 'public');
        }

        if (!isset($validatedData['other'])) {
            $validatedData['other'] = [];
        }

        $organization = Organization::create($validatedData);

        return response()->json([
            'message' => 'Organization created successfully.',
            'organization' => $organization,
        ], 201);
    }

    public function show(Organization $organization)
    {
        $organization->logo_url = $organization->logo ? url('storage/' . $organization->logo) : null;
        return response()->json($organization);
    }

    public function update(Request $request, $id)
    {
        $organization = Organization::findOrFail($id);

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'facebook' => 'nullable|string|max:255',
            'instagram' => 'nullable|string|max:255',
            'twitter' => 'nullable|string|max:255',
            'linkedin' => 'nullable|string|max:255',
            'tiktok' => 'nullable|string|max:255',
            'other' => 'nullable|array',
        ]);

        if ($request->hasFile('logo')) {
            if ($organization->logo) {
                Storage::disk('public')->delete($organization->logo);
            }
            $validatedData['logo'] = $request->file('logo')->store('organizations', 'public');
        }

        $organization->update($validatedData);

        return response()->json([
            'message' => 'Organization updated successfully.',
            'organization' => $organization,
        ], 200);
    }

    public function destroy($id)
    {
        $organization = Organization::findOrFail($id);
        if ($organization->logo) {
            Storage::disk('public')->delete($organization->logo);
        }
        $organization->delete();

        return response()->json([
            'message' => 'Organization deleted successfully.'
        ], 200);
    }
}
