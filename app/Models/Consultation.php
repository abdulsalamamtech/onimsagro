<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Consultation extends Model
{
    use HasFactory, SoftDeletes;
    

    protected $fillable = [
        'full_name',
        'phone_number',
        'email',
        'consultation_time',
        'description',
        'status',
    ];

    
    protected $casts = [
        'consultation_time' => 'datetime',
    ];


}
