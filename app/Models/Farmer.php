<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Farmer extends Model
{
    use SoftDeletes;

    // -   full_name
    // -   phone_number
    // -   email
    // -   country
    // -   state
    // -   address
    // -   farm_name
    // -   farm_size
    // -   type_of_farming_id
    // -   main_products
    // -   do_you_own_farming_equipment [yes|no]
    // -   where_do_you_sell_your_products
    // -   challenge_in_selling_your_products
    // -   additional_comment

    protected $fillable = [
        'full_name',
        'phone_number',
        'email',
        'country',
        'state',
        'address',
        'farm_name',
        'farm_size',
        'type_of_farming_id',
        'main_products',
        'do_you_own_farming_equipment',
        'where_do_you_sell_your_products',
        'challenge_in_selling_your_products',
        'additional_comment',
        'created_by'
    ];

    protected $casts = [
        'farm_size' => 'decimal:2',
        'type_of_farming_id' => 'integer',
        'created_by' => 'integer'
    ];

    /**
     * Get the type of farming associated with the farmer.
     */
    public function typeOfFarming()
    {
        return $this->belongsTo(TypeOfFarming::class, 'type_of_farming_id');
    }

    /**
     * Get the user that created the farmer record.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
