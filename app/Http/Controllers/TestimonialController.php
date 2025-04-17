<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'image' => 'required|string',
            'comment' => 'required|string',
            'rating' => 'required|integer|between:1,5',
        ]);

        $relativeImagePath = str_replace(url('storage') . '/', '', $request->image);

        $testimonial = Testimonial::create([
            'name' => $request->name,
            'image' => $relativeImagePath,
            'comment' => $request->comment,
            'rating' => $request->rating,
        ]);

        $testimonial->image_url = url('storage/' . $testimonial->image);

        return response()->json($testimonial, 201);
    }

    public function getTestimonials()
    {
        $testimonials = Testimonial::orderBy('created_at', 'desc')->get()->map(function ($testimonial) {

            $testimonial->image_url = $testimonial->image ? url('storage/' . $testimonial->image) : null;
            return $testimonial;
        });

        return response()->json($testimonials);
    }
    public function publish(Request $request, $id)
    {
        $testimonial = Testimonial::findOrFail($id);
        $testimonial->is_published = true;
        $testimonial->save();
    
        return response()->json(['message' => 'Testimonial published successfully.']);
    } 
    public function getPublishTestimonial()
{
    $testimonials = Testimonial::where('is_published', 1)
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($testimonial) {
            $testimonial->image_url = $testimonial->image ? url('storage/' . $testimonial->image) : null;
            return $testimonial;
        });

    return response()->json($testimonials);
}
   

}
