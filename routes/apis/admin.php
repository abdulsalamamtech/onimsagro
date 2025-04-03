<?php 

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\PaymentAccountController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;










Route::prefix('admin')->name('admin.')->group(function () {

    // Role Routes
    Route::apiResource('roles', RoleController::class);


    // Activity Routes
    Route::apiResource('activities', ActivityController::class)
        ->only(['index', 'show']);

    // User Routes
    // Route::apiResource('users', UserController::class);

    // Asset Routes
    Route::apiResource('assets', AssetController::class);

    // User Profile Routes
    Route::apiResource('user-profiles', UserProfileController::class);

    // Payment Account Routes
    Route::apiResource('payment-accounts', PaymentAccountController::class);

    // Consultation Routes
    Route::apiResource('consultations', ConsultationController::class);


});