<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AssistanceTypeController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EquipmentTypeController;
use App\Http\Controllers\FarmAssistanceController;
use App\Http\Controllers\FarmerController;
use App\Http\Controllers\FarmingInterestController;
use App\Http\Controllers\InstallationServiceController;
use App\Http\Controllers\InstallationTypeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentAccountController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TrainingProgramController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TypeOfFarmingController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WarehouseOrderController;
use App\Http\Controllers\WarehouseReviewController;
use Illuminate\Support\Facades\Route;


















// Asset Routes
// Route::apiResource('assets', AssetController::class)
// ->only(['show']);

// Payment Account Routes
Route::apiResource('payment-accounts', PaymentAccountController::class)
    ->only(['index', 'show']);

// Consultation Routes
Route::apiResource('consultations', ConsultationController::class)
    ->only(['store']);


// Product Routes
Route::get('products', [ProductController::class, 'getProducts']);
Route::get('products/{product}', [ProductController::class, 'show']);

// Product Reviews
Route::get('product-reviews', [ProductReviewController::class, 'getReviews']);

// Order Routes
Route::apiResource('orders', OrderController::class)
    ->only(['store']);


// Farming interest
Route::apiResource('farming-interests', FarmingInterestController::class)
    ->only(['index']);

// Training program
Route::apiResource('training-programs', TrainingProgramController::class)
    ->only(['store']);


// Warehouse Routes
Route::get('warehouses', [WarehouseController::class, 'getWarehouses']);
Route::get('warehouses/{warehouse}', [WarehouseController::class, 'show']);

// Warehouse Reviews
Route::get('product-reviews', [WarehouseReviewController::class, 'getReviews']);

// Warehouse Order Routes
Route::apiResource('warehouse-orders', WarehouseOrderController::class)
    ->only(['store']);



// Farming Types Routes
Route::apiResource('type-of-farmings', TypeOfFarmingController::class)
    ->only(['index']);

// Farmer store
Route::post('farmers', [FarmerController::class, 'store'])
    ->name('farmers.store');
    
// Assistance type Routes
Route::apiResource('assistance-types', AssistanceTypeController::class)
    ->only(['index']);

// Assistance Routes
Route::apiResource('farm-assistances', FarmAssistanceController::class)
    ->only(['store']);

// Installation Types Routes
Route::apiResource('installation-types', InstallationTypeController::class)
    ->only(['index']);

// Installation Service Routes
Route::apiResource('installation-services', InstallationServiceController::class)
    ->only(['store']);

// Equipment Types Routes
Route::apiResource('equipment-types', EquipmentTypeController::class)
    ->only(['index']);




// Verify transaction
Route::get('transactions/verify', [TransactionController::class, 'verifyTransaction'])
    ->name('transactions.verify');
