<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductType extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'name',
        'created_by',
    ];

    /**
     * Get the user that created the product type.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    /**
     * Get the products for the product type.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
