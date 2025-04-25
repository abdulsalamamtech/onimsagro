<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Resources\AssetResource;
use App\Models\Asset;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function index(){

        // Fetch all assets from your database
        $assets = Asset::latest()->paginate();

        // return ApiResponse::success($assets);
        // check if data is empty
        if ($assets->isEmpty()) {
            return ApiResponse::error([], "No assets found", 404);
        }
        // transform data
        $response = AssetResource::collection($assets);
        // return response
        return ApiResponse::success($response, 'successful', 200); 
    }
}
