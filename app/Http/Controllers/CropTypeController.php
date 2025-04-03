<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCropTypeRequest;
use App\Http\Requests\UpdateCropTypeRequest;
use App\Models\CropType;

class CropTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCropTypeRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(CropType $cropType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCropTypeRequest $request, CropType $cropType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CropType $cropType)
    {
        //
    }
}
