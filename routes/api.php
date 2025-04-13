<?php

use App\Http\Controllers\API\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/user', function (Request $request) {
    // return $request->user();
    return response()->json([
        'success' => true,
        'message' => 'successful',
        'data' => [
            'user' => $request->user(),
            'token' => $request->bearerToken(),
        ]
    ], 200);

})->middleware('auth:sanctum');



Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
});



// Admin routes
require __DIR__.'/apis/admin.php';

// Quest routes
require __DIR__.'/apis/quest.php';



Route::get('/sku', function (Request $request) {

    return response()->json([
        'sku' => \App\Helpers\CustomGenerator::generateUniqueSKU(),
    ]);
});

