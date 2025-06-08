<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'warehouse_order_id',
        'order_id',
        'payment_type',
        'full_name',
        'email',
        'amount',
        'status',
        'reference',
        'payment_method',
        'payment_provider',
        'data'
    ];
    protected $casts = [
        'data' => 'array',
        'amount' => 'decimal:2',
    ];
    protected $attributes = [
        'payment_type' => 'product',
        'status' => 'pending',
        'payment_method' => 'online',
        'payment_provider' => 'paystack',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    /**
     * Get the user that owns the transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    /**
     * Get the warehouse order associated with the transaction.
     */
    public function warehouseOrder()
    {
        return $this->belongsTo(WarehouseOrder::class);
    }
    /**
     * Get the order associated with the transaction.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

}
