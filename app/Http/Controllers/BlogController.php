<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Blog;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    /**
     * Store a new blog post.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request): Response
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Optional image validation
            'slug' => 'sometimes|required|string|max:225'
        ]);
        $validatedData['slug'] = Str::slug($validatedData['title'], '-') . '-' . uniqid();

        // Handle image upload if present
        if ($request->hasFile('image')) {
            $validatedData['image'] = $request->file('image')->store('blog_images', 'public');
        }

        // Create the blog post
        $blog = Blog::create($validatedData);

        // Return a success response
        return response([
            'message' => 'Blog created successfully!',
            'data' => $blog,
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, Blog $blog): Response{
        $validatedData = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'author' => 'sometimes|required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Optional image validation
        ]);
        $validatedData['slug'] = Str::slug($validatedData['title'], '-') . '-' . uniqid();
        if ($request->hasFile('image')) {
            $validatedData['image'] = $request->file('image')->store('blog_images', 'public');
        }

        // Update the blog
        $blog->update($validatedData);

        return response([
            'message' => 'Blog updated successfully!',
            'data' => $blog,
        ], Response::HTTP_OK);
    }

    public function destroy(Blog $blog): Response{
        $blog->delete();
    
    return response([
        'message'=>'Blog deleted Successfully',
    ], Response::HTTP_OK);
    }
}
