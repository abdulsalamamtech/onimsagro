<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AssistanceTypeController;
use App\Http\Controllers\CloudinaryController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FarmAssistanceController;
use App\Http\Controllers\FarmerController;
use App\Http\Controllers\FarmingInterestController;
use App\Http\Controllers\InstallationServiceController;
use App\Http\Controllers\InstallationTypeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentAccountController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\ProductTypeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TrainingProgramController;
use App\Http\Controllers\TypeOfFarmingController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WarehouseOrderController;
use App\Http\Controllers\WarehouseReviewController;
use Illuminate\Support\Facades\Route;










Route::prefix('admin')
    ->middleware('auth:sanctum')
    ->name('admin.')
    ->group(function () {

        // Dashboard Route
        Route::get('dashboard', [DashboardController::class, 'index'])
            ->name('dashboard.index');

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
        Route::apiResource('user-profiles', UserProfileController::class)
            ->except(['update']);
        // User Profile Update
        Route::post('user-profiles/{user}/update', [UserProfileController::class, 'updateUserProfile'])
            ->name('user-profiles.update');
        // User Profile Update Authenticated User
        Route::post('update-user-profile', [UserProfileController::class, 'updateProfile'])
            ->name('user-profile.update');

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
        Route::patch('orders/{order}/confirm', [OrderController::class, 'confirmOrder'])->name('orders.confirm');
        Route::patch('orders/{order}/cancel', [OrderController::class, 'cancelOrder'])->name('orders.cancel');
        Route::get('order-status', [OrderController::class, 'getOrderStatus'])->name('orders.status');

        // Farming interest
        Route::apiResource('farming-interests', FarmingInterestController::class);

        // Training program
        Route::apiResource('training-programs', TrainingProgramController::class);

        // Warehouse Routes
        Route::apiResource('warehouses', WarehouseController::class);
        // Trashed product
        Route::get('warehouse-trashed', [WarehouseController::class, 'trashed'])->name('warehouses.trashed');
        // Restore product
        Route::patch('warehouses/{id}/restore', [WarehouseController::class, 'restore'])->name('warehouses.restore');

        // Warehouse Reviews Routes
        Route::apiResource('warehouse-reviews', WarehouseReviewController::class);

        // Warehouse Order Routes
        Route::apiResource('warehouse-orders', WarehouseOrderController::class);
        Route::patch('warehouse-orders/{warehouseOrder}/confirm', [WarehouseOrderController::class, 'confirmOrder'])->name('warehouse-orders.confirm');
        Route::patch('warehouse-orders/{warehouseOrder}/cancel', [WarehouseOrderController::class, 'cancelOrder'])->name('warehouse-orders.cancel');
        Route::get('warehouse-order-status', [WarehouseOrderController::class, 'getOrderStatus'])->name('warehouse-orders.status');


        // Farming Types Routes
        Route::apiResource('type-of-farmings', TypeOfFarmingController::class)
            ->except(['destroy']);
        // Trashed type of farming
        // Route::get('type-of-farmings-trashed', [TypeOfFarmingController::class, 'trashed'])->name('type-of-farmings.trashed');
        // Restore type of farming
        // Route::patch('type-of-farmings/{id}/restore', [TypeOfFarmingController::class, 'restore'])->name('type-of-farmings.restore');
        // Restore type of farming
        // Route::delete('type-of-farmings/{id}/force-delete', [TypeOfFarmingController::class, 'forceDelete'])->name('type-of-farmings.force-delete');

        // Farmer Types Routes
        Route::apiResource('farmers', FarmerController::class)
            ->except(['destroy']);

        // Assistance Types Routes
        Route::apiResource('assistance-types', AssistanceTypeController::class)
            ->except(['destroy']);

        // Farmer Assistance Routes
        Route::apiResource('farm-assistances', FarmAssistanceController::class)
            ->except(['destroy']);
        
        // Installation Types Routes
        Route::apiResource('installation-types', InstallationTypeController::class)
            ->except(['destroy']);

        // Installation Service Routes
        Route::apiResource('installation-services', InstallationServiceController::class)
            ->except(['destroy']);
    });


// Cloudinary
Route::apiResource('cloudinary', CloudinaryController::class);
// ->middlewareFor(['index'],['auth:sanctum']);