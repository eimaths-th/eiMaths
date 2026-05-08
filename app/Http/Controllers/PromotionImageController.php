<?php

namespace App\Http\Controllers;

use App\Models\PromotionImage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PromotionImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $promotion_images = PromotionImage::all();
        return view('promotion_img.index', compact('promotion_images'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate input
        $request->validate([
            'image' => 'required|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi,wmv|max:51200', // Accept images and videos up to 50MB
            'title' => 'required|string|max:255',
            'type' => 'required|in:image,video',
            'description' => 'nullable|string',
            'is_for_affiliate' => 'nullable|boolean',
        ]);

        // Handle file upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $random = Str::random(10);
            $originalName = $file->getClientOriginalName();
            $fileName = time() . '-' . $random . '_' . $originalName; 
            
            // Determine directory based on type
            $directory = $request->type == 'video' ? 'video/promotion' : 'img/promotion';
            
            // Move the file
            $file->move(public_path($directory), $fileName);

            $filePath = $directory . '/' . $fileName;
        } else {
            return back()->with('error', 'No file uploaded.');
        }

        // Store details in database
        $promotion = new PromotionImage();
        $promotion->title = $request->title;
        $promotion->url = $request->url;
        $promotion->img = $filePath; 
        $promotion->type = $request->type;
        $promotion->description = $request->description;
        $promotion->is_for_affiliate = $request->has('is_for_affiliate') ? 1 : 0;
        $promotion->save();

        return back()->with('success', 'Marketing content uploaded successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**6
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $image = PromotionImage::findOrFail($id);
        $image->delete();

        return redirect()->route('promotion-img.index')->with('success', 'Image deleted successfully.');
    }
    public function updateStatus(Request $request)
    {
        // dd($request->all());
        $imageId = $request->input('id');
        $status = $request->input('status');
        // First, disable all images
        // PromotionImage::where('status', 'enabled')->update(['status' => 'disabled']);

        // Then, enable the selected image
        $image = PromotionImage::find($imageId);
        $image->status = $status;
        $image->save();

        return response()->json(['message' => 'Image status updated successfully!']);
    }
    public function updateStatus2(Request $request)
    {
        $imageId = $request->input('id');

        // Update the status of the image
        $image = PromotionImage::find($imageId);

        // If the image is being enabled, set its status to 'enabled'
        // If the image is being disabled, set its status to 'disabled'
        $image->status = $image->status == 'enabled' ? 'disabled' : 'enabled';
        $image->save();

        return response()->json(['message' => 'Image status updated successfully!']);
    }

    public function disableAll(Request $request)
    {
        // Disable all images before enabling the selected one
        PromotionImage::where('status', 'enabled')->update(['status' => 'disabled']);

        return response()->json(['message' => 'All images have been disabled.']);
    }
}
