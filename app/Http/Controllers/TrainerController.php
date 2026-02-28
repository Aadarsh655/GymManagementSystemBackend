<?php

namespace App\Http\Controllers;

use App\Models\Trainer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class TrainerController extends Controller
{
    public function index()
    {
        $trainers = Trainer::all()->map(function ($trainer) {
            $trainer->image_url = $trainer->image ? url('storage/' . $trainer->image) : null;
            return $trainer;
        });

        return response()->json($trainers);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'facebook' => 'nullable|string|max:255',
            'twitter' => 'nullable|string|max:255',
            'instagram' => 'nullable|string|max:255',
            'category' => 'required|string|in:Gym,MMA',
        ]);

        if ($request->hasFile('image')) {
            $validatedData['image'] = $request->file('image')->store('trainers', 'public');
        }

        $trainer = Trainer::create($validatedData);

        return response()->json([
            'message' => 'Trainer created successfully.',
            'trainer' => $trainer,
        ], 201);
    }

    public function show(Trainer $trainer)
    {
        $trainer->image_url = $trainer->image ? url('storage/' . $trainer->image) : null;
        return response()->json($trainer);
    }

    public function update(Request $request, $id)
    {
        $trainer = Trainer::findOrFail($id);

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'facebook' => 'nullable|string|max:255',
            'twitter' => 'nullable|string|max:255',
            'instagram' => 'nullable|string|max:255',
            'category' => 'required|string|in:Gym,MMA',
        ]);

        if ($request->hasFile('image')) {
            if ($trainer->image) {
                Storage::disk('public')->delete($trainer->image);
            }
            $validatedData['image'] = $request->file('image')->store('trainers', 'public');
        }

        $trainer->update($validatedData);

        return response()->json([
            'message' => 'Trainer updated successfully.',
            'trainer' => $trainer,
        ], 200);
    }

    public function destroy(Request $request)
    {
        $ids = $request->input('ids');

        if (!is_array($ids) || empty($ids)) {
            return response([
                'message' => 'No trainer IDs provided.'
            ], 400);
        }

        $trainers = Trainer::whereIn('id', $ids)->get();
        foreach ($trainers as $trainer) {
            if ($trainer->image) {
                Storage::disk('public')->delete($trainer->image);
            }
            $trainer->delete();
        }

        return response([
            'message' => 'Trainer(s) deleted successfully.'
        ], 200);
    }
}
