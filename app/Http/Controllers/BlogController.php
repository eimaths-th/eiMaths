<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BlogController extends Controller
{
    private function generate_thai_slug(string $text): string
    {
        $slug = preg_replace('/[^\p{Thai}a-zA-Z0-9\s\-:]/u', '', trim($text));
        $slug = str_replace(' ', '-', $slug);
        $slug = str_replace(':', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = Str::lower($slug);
        return $slug;
    }

    public function __construct()
    {
        // $this->middleware('permission:blog-list', ['only' => ['index','show']]); // Comment out for public API access
        // For API read-only access, we don't need permission middleware for index and show_by_slug

        // $this->middleware('permission:blog-create', ['only' => ['create','store']]);
        // $this->middleware('permission:blog-edit', ['only' => ['edit','update']]);
        // $this->middleware('permission:blog-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (auth()->user()->hasRole('Admin')|| auth()->user()->hasRole('SystemAdmin') || auth()->user()->hasRole('BranchAdmin')) {
            $blogs = Blog::with('user')->get();
        } else {
            $blogs = Blog::where('user_id', auth()->id())->with('user')->get();
        }
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
            'pen_name' => 'nullable|string|max:255',
        ]);

        $slug = $request->slug ? $this->generate_thai_slug($request->slug) : $this->generate_thai_slug($request->title);

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

        if ($request->meta_keywords) {
            $keywords = $request->meta_keywords;
            $keywords = strtolower($keywords); // แปลงเป็นตัวพิมพ์เล็กทั้งหมด
            $keywords = str_replace('#', '', $keywords); // ลบเครื่องหมาย #
            $keywords = preg_replace('/\s*\/\s*/', ',', $keywords); // แทนที่ / ด้วย ,
            $keywords = preg_replace('/[!?:;."\(\)\[\]\{\}&+%$@\-_]+/', '', $keywords); // ลบเครื่องหมายวรรคตอนและอักขระพิเศษ
            $keywords = preg_replace('/\s+/', ',', $keywords); // แทนที่ช่องว่างด้วย ,
            $keywords = preg_replace('/,+/', ',', $keywords); // แทนที่เครื่องหมาย , ที่ซ้ำกัน
            $keywords = trim($keywords, ','); // ตัดเครื่องหมาย , ที่อยู่ต้นและท้าย
            $keyword_array = explode(',', $keywords); // แปลงเป็น array เพื่อกรองและลบค่าซ้ำ
            $keyword_array = array_filter($keyword_array); // ลบค่าว่าง
            $keyword_array = array_unique($keyword_array); // ลบค่าซ้ำ
            $request->meta_keywords = implode(',', $keyword_array); // แปลงกลับเป็นสตริงคั่นด้วยเครื่องหมาย ,
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
            'user_id' => auth()->id(), // Set the author of the blog
            'pen_name' => $request->pen_name,
        ]);

        return redirect()->route('blogs.index')->with('success', 'Blog created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Blog $blog)
    {
        // Load blog with user information
        $blog->load('user');
        return view('blogs.show', compact('blog'));
    }

    /**
     * Display the specified resource for API.
     */
    public function api_show()
    {
        $blogs = Blog::with('user')->where('is_published', true)->get();
        foreach ($blogs as $blog) {
            $blog->makeHidden(['id','user_id']);
            if ($blog->thumbnail) {
                $blog->thumbnail = url('storage/' . $blog->thumbnail);
            } else {
                $blog->thumbnail = null;
            }
            $blog->content = \Illuminate\Support\Str::markdown($blog->content);
            if ($blog->meta_keywords) {
                $keywords = $blog->meta_keywords;
                $keywords = strtolower($keywords); // แปลงเป็นตัวพิมพ์เล็กทั้งหมด
                $keywords = str_replace('#', '', $keywords); // ลบเครื่องหมาย #
                $keywords = preg_replace('/\s*\/\s*/', ',', $keywords); // แทนที่ / ด้วย ,
                $keywords = preg_replace('/[!?:;."\(\)\[\]\{\}&+%$@\-_]+/', '', $keywords); // ลบเครื่องหมายวรรคตอนและอักขระพิเศษ
                $keywords = preg_replace('/\s+/', ',', $keywords); // แทนที่ช่องว่างด้วย ,
                $keywords = preg_replace('/,+/', ',', $keywords); // แทนที่เครื่องหมาย , ที่ซ้ำกัน
                $keywords = trim($keywords, ','); // ตัดเครื่องหมาย , ที่อยู่ต้นและท้าย
                $keyword_array = explode(',', $keywords); // แปลงเป็น array เพื่อกรองและลบค่าซ้ำ
                $keyword_array = array_filter($keyword_array); // ลบค่าว่าง
                $keyword_array = array_unique($keyword_array); // ลบค่าซ้ำ
                $blog->meta_keywords = implode(',', $keyword_array); // แปลงกลับเป็นสตริงคั่นด้วยเครื่องหมาย ,
            } else {
                $blog->meta_keywords = null;
            }
            $blog->pen_name = $blog->pen_name ?: ($blog->user ? $blog->user->name : 'ไม่ระบุ');
            if ($blog->user) {
                $blog->user->makeHidden([
                    'id',
                    'affiliate_request',
                    'language_code',
                    'email',
                    'phone',
                    'referral',
                    'email_verified_at',
                    'disable',
                    'created_at',
                    'updated_at',
                    'department_id',
                    'position_id'
                ]);
            }
        }
        return response()->json($blogs);
    }
    
    /**
     * Display the specified blog by slug for API.
     */
    public function api_show_by_slug(string $slug_param)
    {
        $blog = Blog::with('user')->where('slug', $slug_param)->where('is_published', true)->first();
        if (!$blog) {
            return response()->json(['message' => 'Blog not found'], 404);
        }
        $blog->makeHidden(['id','user_id']);
        if ($blog->thumbnail) {
            $blog->thumbnail = url('storage/' . $blog->thumbnail);
        } else {
            $blog->thumbnail = null;
        }
        $blog->content = \Illuminate\Support\Str::markdown($blog->content);
        if ($blog->meta_keywords) {
            $keywords = $blog->meta_keywords;
            $keywords = strtolower($keywords); // แปลงเป็นตัวพิมพ์เล็กทั้งหมด
            $keywords = str_replace('#', '', $keywords); // ลบเครื่องหมาย #
            $keywords = preg_replace('/\s*\/\s*/', ',', $keywords); // แทนที่ / ด้วย ,
            $keywords = preg_replace('/[!?:;."\(\)\[\]\{\}&+%$@\-_]+/', '', $keywords); // ลบเครื่องหมายวรรคตอนและอักขระพิเศษ
            $keywords = preg_replace('/\s+/', ',', $keywords); // แทนที่ช่องว่างด้วย ,
            $keywords = preg_replace('/,+/', ',', $keywords); // แทนที่เครื่องหมาย , ที่ซ้ำกัน
            $keywords = trim($keywords, ','); // ตัดเครื่องหมาย , ที่อยู่ต้นและท้าย
            $keyword_array = explode(',', $keywords); // แปลงเป็น array เพื่อกรองและลบค่าซ้ำ
            $keyword_array = array_filter($keyword_array); // ลบค่าว่าง
            $keyword_array = array_unique($keyword_array); // ลบค่าซ้ำ
            $blog->meta_keywords = implode(',', $keyword_array); // แปลงกลับเป็นสตริงคั่นด้วยเครื่องหมาย ,
        } else {
            $blog->meta_keywords = null;
        }
        $blog->pen_name = $blog->pen_name ?: ($blog->user ? $blog->user->name : 'ไม่ระบุ');
        if ($blog->user) {
            $blog->user->makeHidden([
                'id',
                'affiliate_request',
                'language_code',
                'email',
                'phone',
                'referral',
                'email_verified_at',
                'disable',
                'created_at',
                'updated_at',
                'department_id',
                'position_id'
            ]);
        }
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
            'thumbnail' => 'nullable|image|mimes:png,jpg,jpeg|max:5120',
            'is_published' => 'boolean',
            'pen_name' => 'nullable|string|max:255',
        ]);

        $slug = $request->slug ? $this->generate_thai_slug($request->slug) : $this->generate_thai_slug($request->title);

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

        if ($request->meta_keywords) {
            $keywords = $request->meta_keywords;
            $keywords = strtolower($keywords); // แปลงเป็นตัวพิมพ์เล็กทั้งหมด
            $keywords = str_replace('#', '', $keywords); // ลบเครื่องหมาย #
            $keywords = preg_replace('/\s*\/\s*/', ',', $keywords); // แทนที่ / ด้วย ,
            $keywords = preg_replace('/[!?:;."\(\)\[\]\{\}&+%$@\-_]+/', '', $keywords); // ลบเครื่องหมายวรรคตอนและอักขระพิเศษ
            $keywords = preg_replace('/\s+/', ',', $keywords); // แทนที่ช่องว่างด้วย ,
            $keywords = preg_replace('/,+/', ',', $keywords); // แทนที่เครื่องหมาย , ที่ซ้ำกัน
            $keywords = trim($keywords, ','); // ตัดเครื่องหมาย , ที่อยู่ต้นและท้าย
            $keyword_array = explode(',', $keywords); // แปลงเป็น array เพื่อกรองและลบค่าซ้ำ
            $keyword_array = array_filter($keyword_array); // ลบค่าว่าง
            $keyword_array = array_unique($keyword_array); // ลบค่าซ้ำ
            $request->meta_keywords = implode(',', $keyword_array); // แปลงกลับเป็นสตริงคั่นด้วยเครื่องหมาย ,
        }

        // เก็บ content เก่าก่อนอัปเดต
        $old_content = $blog->content;

        $blog->update([
            'title' => $request->title,
            'slug' => $slug,
            'content' => $request->content,
            'thumbnail' => $thumbnail_path,
            'meta_description' => $request->meta_description,
            'meta_keywords' => $request->meta_keywords,
            'is_published' => $request->is_published ?? false,
            'published_at' => $request->is_published ? now() : null,
            'pen_name' => $request->pen_name,
        ]);

        // Auto delete รูปภาพที่ไม่ได้ใช้งาน
        $this->AutoDeleteUnusedImages($old_content, $request->content);

        return redirect()->route('blogs.index')->with('success', 'Blog updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Blog $blog)
    {
        // Auto delete รูปภาพที่ไม่ได้ใช้งานใน blog ก่อนลบ
        $this->AutoDeleteAllImages($blog->content);

        $blog->delete();

        if ($blog->thumbnail) {
            Storage::disk('public')->delete($blog->thumbnail);
        }

        return redirect()->route('blogs.index')->with('success', 'Blog deleted successfully.');
    }

    /**
     * Auto delete unused images from blog content
     */
    private function AutoDeleteUnusedImages($old_content, $new_content)
    {
        try {
            // ดึง URL ของรูปภาพจาก content เก่า
            $old_image_urls = $this->ExtractImageUrls($old_content);
            // ดึง URL ของรูปภาพจาก content ใหม่
            $new_image_urls = $this->ExtractImageUrls($new_content);
            // หาภาพที่อยู่ใน content เก่าแต่ไม่อยู่ใน content ใหม่
            $unused_image_urls = array_diff($old_image_urls, $new_image_urls);
            // ลบไฟล์ที่ไม่ได้ใช้งาน
            foreach ($unused_image_urls as $image_url) {
                $this->DeleteImageFile($image_url);
            }
        } catch (\Exception $error) {
            return false;
        }
    }

    /**
     * Auto delete all images from blog content
     */
    private function AutoDeleteAllImages($content)
    {
        try {
            // ดึง URL ของรูปภาพจาก content
            $image_urls = $this->ExtractImageUrls($content);
            // ลบไฟล์ทั้งหมด
            foreach ($image_urls as $image_url) {
                $this->DeleteImageFile($image_url);
            }
        } catch (\Exception $error) {
            return false;
        }
    }

    /**
     * ดึง URL ของรูปภาพจาก markdown content
     */
    private function ExtractImageUrls($content)
    {
        $image_urls = [];
        $pattern = '/!\[.*?\]\((.*?)\)/';
        preg_match_all($pattern, $content, $matches);
        if (isset($matches[1])) {
            foreach ($matches[1] as $url) {
                // ลบ query parameters ถ้ามี
                $url_parts = explode('?', $url);
                $image_urls[] = $url_parts[0];
            }
        }
        return $image_urls;
    }

    /**
     * ลบไฟล์รูปภาพ
     */
    private function DeleteImageFile($image_url)
    {
        try {
            // ดึง path ของไฟล์จากรูปแบบ URL
            $parsed_url = parse_url($image_url);
            $relative_path = ltrim(urldecode($parsed_url['path']), '/');
            // สร้าง path ที่ถูกต้องสำหรับระบบไฟล์
            $path = public_path($relative_path);
            // ตรวจสอบว่าไฟล์อยู่ในไดเรกทอรี uploads/images หรือไม่
            $upload_path = public_path('uploads/images');
            // ตรวจสอบว่า path อยู่ภายใต้ไดเรกทอรี uploads/images และไฟล์มีอยู่จริง
            if (strpos(realpath($path), realpath($upload_path)) === 0 && \Illuminate\Support\Facades\File::exists($path)) {
                // ลบไฟล์
                \Illuminate\Support\Facades\File::delete($path);
                return true;
            }
            return false;
        } catch (\Exception $error) {
            return false;
        }
    }
}
