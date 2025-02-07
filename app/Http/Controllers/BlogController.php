<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Models\Blog;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

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

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
    
        $blog = Blog::findOrFail($id);
        $blog->update([
            'title' => $request->title,
            'content' => $request->content,
            'image' => $request->hasFile('image') ? $request->file('image')->store('blog_images', 'public') : $blog->image,
        ]);
    
        return response()->json([
            'message' => 'Blog updated successfully!',
            'data' => $blog,
        ], JsonResponse::HTTP_OK);
    }
    

    public function destroy(Blog $blog): Response{
        $blog->delete();
    
    return response([
        'message'=>'Blog deleted Successfully',
    ], Response::HTTP_OK);
    }

    public function index(){
        $blog = Blog::select('id','title','content','image','slug')
        ->get()->map(function($blog){
           $blog->image_url = $blog->image ? url('storage/'  . $blog->image) : null;
           $blog->status = 'Active';
           return $blog; 
        });
        return response()->json($blog);
        }
}
