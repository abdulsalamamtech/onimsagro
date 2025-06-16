<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstallationService extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'full_name',
        'phone_number',
        'email',
        'farm_size',
        'installation_type_id',
        'form_location',
        'notes',
        'status',
    ];
    protected $casts = [
        'installation_type_id' => 'integer',
        'status' => 'string',
    ];
    public function installationType()
    {
        return $this->belongsTo(InstallationType::class);
    }
}
