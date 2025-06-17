<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RentalService extends Model
{
    use SoftDeletes;

    protected $table = 'rental_services';

    protected $fillable = [
        'full_name',
        'phone_number',
        'email',
        'farm_size',
        'equipment_type_id',
        'renting_purpose',
        'address',
        'state',
        'duration',
        'duration_unit',
        'amount',
        'notes', // by admin
        'status', // by admin
        'created_by',
        'updated_by',
    ];


    // relationship
    // created by
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    // updated by
    public function updatedBy() {
        return $this->belongsTo(User::class, 'updated_by');
    }
    // equipment type
    public function equipmentType()
    {
        return $this->belongsTo(EquipmentType::class);
    }
}
