<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BlogController extends Controller
{
    public function __construct()
    {
        // $this->middleware('permission:blog-list', ['only' => ['index','show']]); // Comment out for public API access
        // For API read-only access, we don't need permission middleware for index and show_by_slug

        $this->middleware('permission:blog-create', ['only' => ['create','store']]);
        $this->middleware('permission:blog-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:blog-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $blogs = Blog::all();
        return view('blogs.index', compact('blogs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('blogs.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blogs,slug',
            'content' => 'required|string',
            'thumbnail' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'meta_description' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string|max:255',
            'is_published' => 'boolean',
        ]);

        $slug = $request->slug ? Str::slug($request->slug, '-', null) : Str::slug($request->title, '-', null);

        $original_slug = $slug;
        $count = 1;
        while (Blog::where('slug', $slug)->exists()) {
            $slug = $original_slug . '-' . $count++;
        }

        if (Blog::where('slug', $slug)->exists()) {
            return redirect()->back()->with('error', 'Slug already exists. Please choose a different slug.');
        }

        $thumbnail_path = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnail_path = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        $blog = Blog::create([
            'thumbnail' => $thumbnail_path,
            'title' => $request->title,
            'slug' => $slug,
            'content' => $request->content,
            'meta_description' => $request->meta_description,
            'meta_keywords' => $request->meta_keywords,
            'is_published' => $request->is_published ?? false,
            'published_at' => $request->is_published ? now() : null,
        ]);

        return redirect()->route('blogs.index')->with('success', 'Blog created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Blog $blog)
    {
        return view('blogs.show', compact('blog'));
    }

    /**
     * Display the specified resource for API.
     */
    public function api_show(Blog $blog)
    {
        $blog = Blog::all();
        foreach ($blog as $b) {
            $b->makeHidden('id');
            $b->thumbnail = url('storage/' . $b->thumbnail);
        }
        return response()->json($blog);
    }

    /**
     * Display the specified blog by slug for API.
     */
    public function api_show_by_slug(string $slug_param)
    {
        $blog = Blog::where('slug', $slug_param)->first();

        if (!$blog) {
            return response()->json(['message' => 'Blog not found'], 404);
        }

        $blog->makeHidden('id');
        $blog->thumbnail = url('storage/' . $blog->thumbnail);

        return response()->json($blog);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Blog $blog)
    {
        return view('blogs.edit', compact('blog'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Blog $blog)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blogs,slug,' . $blog->id,
            'content' => 'required|string',
            'meta_description' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string|max:255',
            'thumbnail' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'is_published' => 'boolean',
        ]);

        $slug = $request->slug ? Str::slug($request->slug, '-', null) : Str::slug($request->title, '-', null);

        $original_slug = $slug;
        $count = 1;
        while (Blog::where('slug', $slug)->where('id', '!=', $blog->id)->exists()) {
            $slug = $original_slug . '-' . $count++;
        }

        if (Blog::where('slug', $slug)->where('id', '!=', $blog->id)->exists()) {
            return redirect()->back()->with('error', 'Slug already exists. Please choose a different slug.');
        }

        $thumbnail_path = $blog->thumbnail;
        if ($request->hasFile('thumbnail')) {
            if ($blog->thumbnail) {
                Storage::disk('public')->delete($blog->thumbnail);
            }
            $thumbnail_path = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        $blog->update([
            'title' => $request->title,
            'slug' => $slug,
            'content' => $request->content,
            'thumbnail' => $thumbnail_path,
            'meta_description' => $request->meta_description,
            'meta_keywords' => $request->meta_keywords,
            'is_published' => $request->is_published ?? false,
            'published_at' => $request->is_published ? now() : null,
        ]);

        return redirect()->route('blogs.index')->with('success', 'Blog updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Blog $blog)
    {
        $blog->delete();

        if ($blog->thumbnail) {
            Storage::disk('public')->delete($blog->thumbnail);
        }

        return redirect()->route('blogs.index')->with('success', 'Blog deleted successfully.');
    }
}
