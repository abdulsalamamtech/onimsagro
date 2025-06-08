<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseOrder extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'warehouse_id',
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
     * Get the warehouse for the order.
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    } 


    /**
     * Get the transactions associated with the order.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
