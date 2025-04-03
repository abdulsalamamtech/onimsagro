<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Asset;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function index(){

        // Fetch all assets from your database
        $assets = Asset::latest()->paginate();

        return ApiResponse::success($assets);

    }
}
