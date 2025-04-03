<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;

class CloudinaryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $assets = Asset::all();
        // $data = $assets->map(function ($asset) {
        //     return [
        //         'id' => $asset->id,
        //         'name' => $asset->name,
        //         'url' => $asset->url,
        //         'price' => $asset->price,
        //         'description' => $asset->description,
        //     ];
        // });
        // return ApiResponse::success($data, 'All products retrieved successfully');

        return ApiResponse::success($assets, 'All assets retrieved successfully');

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validateRequest = $request->validate([
            // 'name' => ['required', 'max:255'],
            'image' => ['required', 'image', 'max:2048'],
            // 'price' => ['required', 'numeric'],
            // 'description' => 'required',
        ]);
        
        $cloudinaryImage = $request->file('image')->storeOnCloudinary('assets');
        $url = $cloudinaryImage->getSecurePath();
        $public_id = $cloudinaryImage->getPublicId();

        // dd($cloudinaryImage);
        // return [$cloudinaryImage];

        $asset = Asset::create([
            'name' =>  $cloudinaryImage->getOriginalFileName(),
            'description' => 'test cloudinary file upload',
            'url' => $url,
            'file_id' => $public_id,
            'type' => $cloudinaryImage->getFileType(),
            'size' => $cloudinaryImage->getSize(),
        ]);
        $message = "Asset created successfully";

        $data = [
            'url' => $url,
            'file_id' => $public_id,
            'response' => $cloudinaryImage->getResponse()
        ];

        return ApiResponse::success($data, $message);
    }

    /**
     * Display the specified resource.
     */
    public function show(Asset $asset)
    {
        return ApiResponse::success($asset, 'successful');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Asset $asset)
    {
        $validateRequest = $request->validate([
            'image' => ['sometimes','required', 'image', 'max:2048']
        ]);

        if($request->hasFile('image')){
            Cloudinary::destroy($asset->file_id);
            $cloudinaryImage = $request->file('image')->storeOnCloudinary('assets');
            $url = $cloudinaryImage->getSecurePath();
            $public_id = $cloudinaryImage->getPublicId();

            $asset->update([
                'url' => $url,
                'file_id' => $public_id,
            ]);

        }

        $asset->update([
            'description' => "updated cloudinary image",
        ]);

        ApiResponse::success([], "Asset successfully updated");

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Asset $asset)
    {
        Cloudinary::destroy($asset->file_id);
        $asset->delete();

        ApiResponse::success([], "Asset successfully deleted");
    }

}
