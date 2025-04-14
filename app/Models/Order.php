<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;
    

    protected $fillable = [
        'user_id',
        'updated_by',
        'full_name',
        'email',
        'phone_number',
        'address',
        'total_price',
        'status',
    ];
    /**
     * Get the user that owns the order.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    /**
     * Get the product that owns the order.
     */ 
    // public function product()
    // {
    //     return $this->belongsTo(Product::class);
    // }

    /**
     * Get the order items for the order.
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }


}
