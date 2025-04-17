<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'description',
        'logs'
    ];

    protected $casts = [
        'logs' => 'array',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
