<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'banner_id',
        'name',
        'description',
        'capacity',
        'sku',
        'price',
        'tag',
        'location',
        'status',
        'created_by'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function banner()
    {
        return $this->belongsTo(Asset::class, 'banner_id');
    }

    /**
     * Get the images for the warehouse.
     */
    public function images()
    {
        return $this->hasMany(WarehouseImage::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%$search%")
                ->orWhere('description', 'like', "%$search%")
                ->orWhere('sku', 'like', "%$search%")
                ->orWhere('location', 'like', "%$search%");
        });
    }
    public function scopeFilter($query, $filters)
    {
        if ($filters['search'] ?? false) {
            $query->search($filters['search']);
        }
        if ($filters['status'] ?? false) {
            $query->where('status', $filters['status']);
        }
        if ($filters['created_by'] ?? false) {
            $query->where('created_by', $filters['created_by']);
        }
    }
    public function scopeSort($query, $sort)
    {
        if ($sort == 'asc') {
            return $query->orderBy('created_at', 'asc');
        } elseif ($sort == 'desc') {
            return $query->orderBy('created_at', 'desc');
        }
        return $query;
    }

    // warehouse orders
    public function warehouseOrders()
    {
        return $this->hasMany(WarehouseOrder::class, 'warehouse_id');
    }
    // warehouse reviews
    public function warehouseReviews()
    {
        return $this->hasMany(WarehouseReview::class);
    }
}
