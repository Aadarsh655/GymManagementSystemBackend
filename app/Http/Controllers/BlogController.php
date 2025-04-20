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
        $validatedData = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'image' => 'nullable',
            'status' => 'required|in:Active,Inactive',
            'slug' => 'sometimes|required|string|max:225'
        ]);
        $validatedData['slug'] = Str::slug($validatedData['title'], '-') . '-' . uniqid();
        if ($request->hasFile('image')) {
            $validatedData['image'] = $request->file('image')->store('blog_images', 'public');
        }
        $blog = Blog::create($validatedData);
        return response([
            'message' => 'Blog created successfully!',
            'blog' => $blog,
        ],201);
    }

    public function update(Request $request, $id)
    {
        $blog = Blog::findorFail($id);
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|in:Active,Inactive',
            'image' => 'nullable',
        ]);
        if ($request->hasFile('image')) {
            if($blog->image){
                Storage::disk('public')->delete($blog->image);
            }
            $photoPath = $request->file('image')->store('images', 'public');
            $blog->image = $photoPath;         
        }
        $blog->fill($request->except('image'));  
        $blog->save(); 
        return response()->json([
            'message' => 'Blog updated successfully!',
            'user' => array_merge($blog->toArray(), [
                'id' => $blog->id,
                'image_url' => $blog->image ? asset('storage/' . $blog->image) : null,
            ]),
        ],200);
    }

    public function index() {
        $blogs = Blog::select('id', 'title', 'content', 'status','image', 'slug', 'created_at')
            ->get()
            ->map(function ($blog) {
                $blog->image_url = $blog->image ? url('storage/' . $blog->image) : null;
                return $blog;
            });

        return response()->json($blogs, 200, ['Content-Type' => 'application/json']);
    }

    public function destroy(Request $request): Response
    {
        $ids = $request->input('ids');
    
        if (!is_array($ids) || empty($ids)) {
            return response([
                'message' => 'No blog IDs provided.'
            ], 400);
        }
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:blog,id',
        ]);

        Blog::whereIn('id', $ids)->delete();
    
        return response([
            'message' => 'Blog(s) deleted successfully.'
        ], 200);
    }

    public function show($slug)
{
    $blog = Blog::where('slug', $slug)->first();

    if (!$blog) {
        return response()->json(['message' => 'Blog not found'], 404);
    }
    $blog->image_url = $blog->image ? url('storage/' . $blog->image) : null;
    return response()->json([
        'title' => $blog->title,
        'content' => $blog->content,
        'image_url' => $blog->image_url, 
        'created_at' => $blog->created_at,
    ], 200, ['Content-Type' => 'application/json']);
}
}
