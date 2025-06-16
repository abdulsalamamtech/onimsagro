<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstallationType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'created_by',
    ];

    // created by
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    
}
