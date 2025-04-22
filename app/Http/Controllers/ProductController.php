<?php

namespace App\Http\Controllers;

use App\Helpers\ActorHelper;
use App\Helpers\ApiResponse;
use App\Helpers\CustomGenerator;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Activity;
use App\Models\Asset;
use App\Models\Product;
use App\Models\ProductCategory;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // get all products
        $products = Product::with(['banner', 'productType', 'productCategory', 'images.asset'])->latest()->paginate(10);
        // check if data is empty
        if ($products->isEmpty()) {
            return ApiResponse::error([], "No products found", 404);
        }
        // transform data
        $response = ProductResource::collection($products);
        // return response
        return ApiResponse::success($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        // validated data
        $data = $request->validated();

        // Check if the request has files
        // if (!$request->hasFile('images')) {
        //     return ApiResponse::error('No files were uploaded', 422);
        // }

        // Check if the request has more than 5 files
        // if (count($request->file('images')) > 5) {
        //     return ApiResponse::error('You can only upload a maximum of 5 files', 422);
        // }
 
        try {
            
            // begin transaction
            DB::beginTransaction();

            // set created_by
            $data['created_by'] = ActorHelper::getUserId();

            // Adding sku if empty
            if (empty($data['sku'])) {
                $data['sku'] = CustomGenerator::generateUniqueSKU();
            }
            // If status is empty
            if (empty($data['status'])) {
                $data['status'] = 'active';
            }

            // upload banner
            if ($request->hasFile('banner')) {
                // upload file to cloudinary
                $cloudinaryImage = Cloudinary::uploadApi()->upload($request->file('banner')->getRealPath());
                // save product banner to assets
                $asset = Asset::create([
                    'name' =>  $request->file('banner'),
                    'description' => 'product upload',
                    'url' => $cloudinaryImage['url'],
                    'file_id' => $cloudinaryImage['public_id'],
                    'type' => $cloudinaryImage['resource_type'],
                    'size' => $cloudinaryImage['bytes'],
                ]);
                // save product banner
                $data['banner_id'] = $asset->id;
            }
            
            // create product category
            $product = Product::create($data);

            // Upload images
            if ($request->hasFile('images')) {
                foreach($request->file('images') as $file){
                    // upload file to cloudinary
                    $cloudinaryImage = Cloudinary::uploadApi()->upload($file->getRealPath());
                    // save product image to assets
                    $asset = Asset::create([
                        'name' =>  $data['name'],
                        'description' => 'product upload',
                        'url' => $cloudinaryImage['url'],
                        'file_id' => $cloudinaryImage['public_id'],
                        'type' => $cloudinaryImage['resource_type'],
                        'size' => $cloudinaryImage['bytes'],
                    ]);
                    info('product image', [$asset]);
                    // save product image
                    $product->images()->create(['asset_id' => $asset->id]);
                }    
            }

            // Load relationships
            $product->load(['banner', 'productType', 'productCategory', 'images.asset']);

            // transform data
            $response = new ProductResource($product);


            // log activity
            info('product created', [$product]);
            Activity::create([
                'user_id' => ActorHelper::getUserId()?? null,
                'description' => 'created product',
                'logs' => $product
            ]);
            // commit transaction
            DB::commit();
            // return response
            return ApiResponse::success($response, 'Product created successfully', 201, $response);
           
        }catch (\Throwable $th) {
            //throw $th;
            // rollback transaction
            DB::rollBack();
            // log error
            Log::error('Failed to create product', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);
            // return error response
            return ApiResponse::error([], 'Failed to create product '. $th->getMessage(), 500);
        }
    }

    /**
     * [public] Display the specified resource.
     */
    public function show(Product $product)
    {
        // load relationships
        $product->load(['banner', 'productType', 'productCategory', 'images.asset']);
        // transform data
        $response = new ProductResource($product);
        // return response
        return ApiResponse::success($response);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();

        try {
            // begin transaction
            DB::beginTransaction();

            // update product
            $product->update($data);

            // transform data
            $response = new ProductResource($product);

            // log activity
            info($this, [$product]);
            Activity::create([
                'user_id' => ActorHelper::getUserId()?? null,
                'description' => 'updated product',
                'logs' => $response
            ]);
            
            // commit transaction
            DB::commit();

            // return response
            return ApiResponse::success($response, 'Product updated successfully', 200, $product);

        } catch (\Throwable $th) {
            //throw $th;
            // rollback transaction
            DB::rollBack();
            // log error
            Log::error($this, $th);
            // return error response
            return ApiResponse::error('Failed to update product', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // delete product
        $product->delete();

        // return response
        return ApiResponse::success([], 'Product deleted successfully', 200);
    }

    /**
     * [public] Display all active products.
     */
    public function getProducts()
    {
        // get all products
        $products = Product::where('status', 'active')
            ->with(['banner', 'productType', 'productCategory', 'images.asset'])
            ->latest()->paginate(10);
        // transform data
        $response = ProductResource::collection($products);
        // return response
        return ApiResponse::success($response, 200);
    }


     /**
      * Get trashed data
      */
      public function trashed(){
        // Get data
        $products = Product::onlyTrashed()->paginate();
        // check if data is empty
        if ($products->count() < 1) {
            return ApiResponse::error([], "No trashed data found", 404);
        }
        // transform data [add pagination to data]
        $response = ProductResource::collection($products);
        // return response
        return ApiResponse::success($response, "successfully load trashed data", 200);
      }


    /**
     * Restore deleted data
     */
    public function restore($id){
        // Get data
        $product = Product::onlyTrashed()->find($id);
        // check if data is empty
        if (!$product) {
            return ApiResponse::error([], "No trashed data found", 404);
        }
        // transform data
        $product->restore();
        // return response
        return ApiResponse::success($product, "successfully restored trashed data", 200);
    }
}
