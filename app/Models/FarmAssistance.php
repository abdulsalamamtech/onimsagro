<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FarmAssistance extends Model
{
    use SoftDeletes;

    // -   full_name
    // -   phone_number
    // -   email
    // -   assistance_types_id
    // -   reason_for_request
    protected $fillable = [
        'full_name',
        'phone_number',
        'email',
        'assistance_type_id',
        'reason_for_request',
        'status',
        'created_by',
        'farming_stage',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];


    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // public function updatedBy()
    // {
    //     return $this->belongsTo(User::class, 'updated_by');
    // }

    public function assistanceType()
    {
        return $this->belongsTo(AssistanceType::class, 'assistance_type_id');
    }

    

}
