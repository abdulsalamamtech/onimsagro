<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TechnicalSupport extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'full_name',
        'phone_number',
        'email',
        'crop_type_id',
        'stage_of_plant',
        'problem_with_crop',
    ];

    public function cropType()
    {
        return $this->belongsTo(CropType::class);
    } 

    

}
