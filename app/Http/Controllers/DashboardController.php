<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Order;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseOrder;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the dashboard information.
     */
    public function index(Request $request)
    {
        $data = [
            'users' => $this->getUsers(),
            'products' => $this->getProducts(),
            'orders' => $this->getOrders(),
            'warehouses' => $this->getWarehouses(),
            'warehouse_orders' => $this->getWarehouseOrders(),
            'transactions' => $this->getTransactions(),
        ];

        // return response()->json($data);
        return ApiResponse::success($data, 'Dashboard data retrieved successfully.');
    }


    private function getUsers()
    {
        // Logic to retrieve users
        $users = [
            'total' => User::count(),
            // 'admins' => User::where('role', 'admin')->count(),
            'admins' => User::whereHasRole('admin')?->count(),
            'verified' => User::where('email_verified_at')?->count(),
            'pending' => User::whereNot('email_verified_at')?->count(),
            // 'male' => User::where('gender', 'male')->count(),
            // 'female' => User::where('gender', 'female')->count(),
        ];
        return $users;
    }
    // product 
    private function getProducts()
    {
        // Logic to retrieve products
        $products = [
            'total' => Product::count(),
            'active' => Product::where('status', 'active')?->count(),
            'inactive' => Product::where('status', 'inactive')?->count(),
            'trashed' => Product::onlyTrashed()?->count(),
            // out of stock
            'out_of_stock' => Product::where('stock', 0)?->count(),
            // low stock
            'low_stock' => Product::where('stock', '<=', 5)?->count(),
            // high stock
            'high_stock' => Product::where('stock', '>', 5)?->count(),
            // product types
            'product_types' => Product::select('product_type_id')
                ->distinct()
                ->count(),
            // product categories
            'product_categories' => Product::select('product_category_id')
                ->distinct()
                ->count(),
            // product reviews
            'product_reviews' => Product::withCount('reviews')
                ->get()
                ->sum('reviews_count'),
        ];
        return $products;
    }
    // orders
    private function getOrders()
    {
        // Logic to retrieve orders
        $orders = [
            'total' => Order::count(),
            // 'pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'
            'pending' => Order::where('status', 'pending')->count(),
            'confirmed' => Order::where('status', 'confirmed')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'shipped' => Order::where('status', 'shipped')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
            // 'completed' => Order::where('status', 'completed')->count(),
            // 'refunded' => Order::where('status', 'refunded')->count(),
        ];
        return $orders;
    }
    // warehouse
    private function getWarehouses()
    {
        // Logic to retrieve warehouses
        $warehouses = [
            'total' => Warehouse::count(),
            'active' => Warehouse::where('status', 'active')->count(),
            'inactive' => Warehouse::where('status', 'inactive')->count(),
            'trashed' => Warehouse::onlyTrashed()->count(),
            // warehouse reviews
            'warehouse_reviews' => Warehouse::where('warehouseReviews')->count(),
            'ratings' => Warehouse::withCount('warehouseReviews')
                ->get()
                ->sum('rating'),
        ];
        return $warehouses;
    }
    // warehouse order
    private function getWarehouseOrders()
    {
        // Logic to retrieve warehouse orders
        $warehouseOrders = [
            'total' => WarehouseOrder::count(),
            'pending' => WarehouseOrder::where('status', 'pending')->count(),
            'confirmed' => WarehouseOrder::where('status', 'confirmed')->count(),
            'processing' => WarehouseOrder::where('status', 'processing')->count(),
            'shipped' => WarehouseOrder::where('status', 'shipped')->count(),
            'delivered' => WarehouseOrder::where('status', 'delivered')->count(),
            'cancelled' => WarehouseOrder::where('status', 'cancelled')->count(),
        ];
        return $warehouseOrders;
    }
    // transactions
    private function getTransactions()
    {
        // Logic to retrieve transactions
        $transactions = [
            'total' => Transaction::count(),
            //  [
            //         'pending', 
            //         'successful', 
            //         'cancelled', 
            //         'suspended', 
            //         'rejected'
            //     ]
            'pending' => Transaction::where('status', 'pending')->count(),
            'failed' => Transaction::where('status', 'failed')->count(),
            'successful' => Transaction::where('status', 'successful')->count(),
            'refunded' => Transaction::where('status', 'refunded')->count(),
            'cancelled' => Transaction::where('status', 'cancelled')->count(),
            'suspended' => Transaction::where('status', 'suspended')->count(),
            'rejected' => Transaction::where('status', 'rejected')->count(),
            // payment types
            'warehouse_orders' => Transaction::where('payment_type', 'warehouse_order')->count(),
            'orders' => Transaction::where('payment_type', 'order')->count(),
        ];
        return $transactions;
    }
}
