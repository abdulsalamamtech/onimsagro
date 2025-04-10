<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingProgram extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'full_name',
        'phone_number',
        'email',
        'farming_interest_id',
        'created_by',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    } 

    public function farmingInterest()
    {
        return $this->belongsTo(FarmingInterest::class);
    }  
}
