<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\StoreAssistanceTypeRequest;
use App\Http\Requests\UpdateAssistanceTypeRequest;
use App\Http\Resources\AssistanceTypeResource;
use App\Models\AssistanceType;

class AssistanceTypeController extends Controller
{
    /**
     * [public] Display a listing of the resource.
     */
    public function index()
    {
        $assistanceTypes = AssistanceType::get();

        // check if there are no assistance types
        if ($assistanceTypes->isEmpty()) {
            return ApiResponse::error([], 'No assistance type found', 404);
        }
        // transform data
        $response = AssistanceTypeResource::collection($assistanceTypes);

        // return response
        return ApiResponse::success($response, 'successful', 200, $assistanceTypes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAssistanceTypeRequest $request)
    {
        $assistanceType = AssistanceType::create($request->validated());

        return ApiResponse::success(new AssistanceTypeResource($assistanceType), 'Assistance type created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(AssistanceType $assistanceType)
    {
        return ApiResponse::success(new AssistanceTypeResource($assistanceType), 'Assistance type retrieved successfully', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAssistanceTypeRequest $request, AssistanceType $assistanceType)
    {
        $assistanceType->update($request->validated());

        return ApiResponse::success(new AssistanceTypeResource($assistanceType), 'Assistance type updated successfully', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AssistanceType $assistanceType)
    {
        $assistanceType->delete();

        return ApiResponse::success(null, 'Assistance type deleted successfully', 204);
    }
}
