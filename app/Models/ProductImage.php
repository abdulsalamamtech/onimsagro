<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'asset_id'];

    /**
     * Get the product that owns the image.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    /**
     * Get the asset that owns the image.
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
