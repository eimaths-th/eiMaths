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
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Accept only images up to 2MB
            'title' => 'required|string|max:255',
        ]);

        // Handle file upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            //random str 10 car
            $random = Str::random($length = 10);
            $originalName = $image->getClientOriginalName();

            $imageName = time() . '-' . $random . '_' . $originalName . '.' . $image->getClientOriginalExtension(); // Unique filename
            // Move the file to public/img/promotion
            $image->move(public_path('img/promotion'), $imageName);

            // Save file path to database (if needed)
            $imagePath = 'img/promotion/' . $imageName;
        } else {
            return back()->with('error', 'No image uploaded.');
        }

        // Store image details in database (if necessary)
        $promotion = new PromotionImage();
        $promotion->title = $request->title;
        $promotion->url = $request->url;
        $promotion->img = $imagePath; // Save path in DB
        $promotion->save();

        return back()->with('success', 'Promotion image uploaded successfully!');
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
