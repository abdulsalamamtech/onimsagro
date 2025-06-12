<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\StoreTypeOfFarmingRequest;
use App\Http\Requests\UpdateTypeOfFarmingRequest;
use App\Http\Resources\TypeOfFarmingResource;
use App\Models\TypeOfFarming;

class TypeOfFarmingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $typeOfFarmings = TypeOfFarming::with('createdBy')->get();

        // check if there are no type of farmings
        if ($typeOfFarmings->isEmpty()) {
            return ApiResponse::error([], 'No type of farming found', 404);
        }
        // transform data
        $response = TypeOfFarmingResource::collection($typeOfFarmings);

        // return response
        return ApiResponse::success($response, 'successful', 200, $typeOfFarmings);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTypeOfFarmingRequest $request)
    {
        $data = $request->validated();

        // create data
        $data['created_by'] = auth()->id();

        // create type of farming
        $typeOfFarming = TypeOfFarming::create($data);

        // transform data
        $response = new TypeOfFarmingResource($typeOfFarming);

        // return response
        return ApiResponse::success($response, 'Type of farming created successfully', 201, $typeOfFarming);
    }

    /**
     * Display the specified resource.
     */
    public function show(TypeOfFarming $typeOfFarming)
    {
        // load the createdBy relationship
        $typeOfFarming->load('createdBy');
        $response = new TypeOfFarmingResource($typeOfFarming);

        return ApiResponse::success($response, 'Type of farming retrieved successfully', 200, $typeOfFarming);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTypeOfFarmingRequest $request, TypeOfFarming $typeOfFarming)
    {
        $data = $request->validated();

        // update type of farming
        $typeOfFarming->update($data);

        // transform data
        $response = new TypeOfFarmingResource($typeOfFarming);

        // return response
        return ApiResponse::success($response, 'Type of farming updated successfully', 200, $typeOfFarming);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TypeOfFarming $typeOfFarming)
    {
        $typeOfFarming->delete();

        return ApiResponse::success([], 'Type of farming deleted successfully', 204);
    }
}
