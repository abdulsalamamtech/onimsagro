<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'product_type_id',
        'product_category_id',
        'banner_id',
        'name',
        'description',
        'sku',
        'price',
        'stock',
        'tag',
        'location',
        'estimated_delivery',
        'moq',
        'specs',
        'status',
        'created_by'
    ];
    /**
     * Get the product type that owns the product.
     */
    public function productType()
    {
        return $this->belongsTo(ProductType::class);
    }
    /**
     * Get the product category that owns the product.
     */
    public function productCategory()
    {
        return $this->belongsTo(ProductCategory::class);
    }
    /**
     * Get the banner that is associated with the product.
     */
    public function banner()
    {
        return $this->belongsTo(Asset::class, 'banner_id');
    }
    /**
     * Get the user that created the product.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // product reviews
    /**
     * Get the reviews for the product.
     */
    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    /**
     * Get the images for the product.
     */
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
}
