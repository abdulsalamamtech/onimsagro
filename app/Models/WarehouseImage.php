<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseImage extends Model
{
    protected $fillable = ['warehouse_id', 'asset_id'];

    /**
     * Get the warehouse that owns the image.
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
    /**
     * Get the asset that owns the image.
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
