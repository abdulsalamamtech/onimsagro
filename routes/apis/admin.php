<?php 

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\CloudinaryController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\FarmingInterestController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentAccountController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\ProductTypeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TrainingProgramController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;


Route::prefix('admin')->middleware('auth:sanctum')->name('admin.')->group(function () {

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

    // Product categories Routes
    Route::apiResource('product-categories', ProductCategoryController::class);

    // Product Types Routes
    Route::apiResource('product-types', ProductTypeController::class);


    // Product Routes
    Route::apiResource('products', ProductController::class);
    // Trashed product
    Route::get('product-trashed', [ProductController::class, 'trashed'])->name('products.trashed');
    // Restore product
    Route::patch('products/{id}/restore', [ProductController::class, 'restore'])->name('products.restore');


    // Product reviews Routes
    Route::apiResource('product-reviews', ProductReviewController::class);

    // Order Routes
    Route::apiResource('orders', OrderController::class);
    
    // Route::apiResource('order-items', OrderItemController::class);

    // Farming interest
    Route::apiResource('farming-interests', FarmingInterestController::class);

    // Training program
    Route::apiResource('training-programs', TrainingProgramController::class);


});


// Cloudinary
Route::apiResource('cloudinary', CloudinaryController::class);
// ->middlewareFor(['index'],['auth:sanctum']);