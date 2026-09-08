<?php

namespace App\Http\Controllers;

use App\Models\FileUpload;
use App\Services\Images\ImageSourceContext;
use App\Services\Images\ImageUploadStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HistoryStudentImgController extends Controller
{
    private ImageUploadStorageService $image_upload_storage_service;

    public function __construct(ImageUploadStorageService $image_upload_storage_service)
    {
        $this->image_upload_storage_service = $image_upload_storage_service;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        //

        $validator =  Validator::make($request->all(), [
            'file.*' => 'required|image',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $filecount = count($request->file('file'));
        //return response()->json(['success'=>$x]);
        //print_r($request->file('file'));
        //exit();
        $newname = "";
        for ($i = 0; $i <= ($filecount - 1); $i++) {
            $image = $request->file('file')[$i];
            //print_r($image);
            //exit();
            $imageName = $image->getClientOriginalName();
            $oldfile = FileUpload::where('oldname', $imageName)->get();


            if (!is_array($oldfile)) {

                $stored_image = $this->image_upload_storage_service->StoreUploadedImage(
                    $image,
                    'file_upload',
                    ImageSourceContext::API_STUDENT_HISTORY_IMAGE,
                );

                try {
                    $newname = $stored_image->filename;
                    $imageUpload = new FileUpload();
                    $imageUpload->filename = $newname;
                    $imageUpload->oldname = $imageName;
                    $imageUpload->save();
                } catch (\Throwable $exception) {
                    $this->image_upload_storage_service->DeleteStoredImage($stored_image->absolute_path);
                    throw $exception;
                }
            }

            //return response()->json(['success' => $newname]);

            //$newnamea[$i] = $newname;
        }
        return response()->json(['success' => $newname]);
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

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        //
        $filename =  $request->get('name');
        $oldfile = FileUpload::where('oldname', $filename)->get();
        abort_if($oldfile->isEmpty(), 404);
        $path = public_path() . '/file_upload/' . $oldfile[0]->filename;
        if (file_exists($path)) {
            unlink($path);
            FileUpload::where('oldname', $filename)->delete();
        }
        return $filename;
    }
}
