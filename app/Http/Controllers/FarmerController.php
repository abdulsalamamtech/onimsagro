<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFarmerRequest;
use App\Http\Requests\UpdateFarmerRequest;
use App\Models\Farmer;

class FarmerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFarmerRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Farmer $farmer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFarmerRequest $request, Farmer $farmer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Farmer $farmer)
    {
        //
    }
}
