<?php 

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentAccountController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;



// Asset Routes
// Route::apiResource('assets', AssetController::class)
// ->only(['show']);

// Payment Account Routes
Route::apiResource('payment-accounts', PaymentAccountController::class)
->only(['show']);

// Consultation Routes
Route::apiResource('consultations', ConsultationController::class)
->only(['store']);


// Order Routes
Route::apiResource('orders', OrderController::class)
->only(['store']);

