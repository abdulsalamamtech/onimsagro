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
        'is_paid',
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

    /**
     * Get the transactions associated with the order.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    
    /**
     * Get the activity logs for the order.
     */
    // public function activityLogs()
    // {
    //     return $this->hasMany(Activity::class, 'subject_id')->where('subject_type', 'App\Models\Order');
    // }

}
