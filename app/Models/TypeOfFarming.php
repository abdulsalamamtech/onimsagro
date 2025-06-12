<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TypeOfFarming extends Model
{
    use SoftDeletes;
    
    protected $fillable = ['name', 'created_by'];

    protected $casts = [
        'created_by' => 'integer',
    ];

    /**
     * Get the user that created the type of farming.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }


}
