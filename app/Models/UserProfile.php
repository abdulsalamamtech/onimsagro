<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id', 
        'asset_id', 
        'first_name',
        'middle_name', 
        'last_name', 
        'address', 
        'city',
        'state', 
        'zip_code', 
        'country'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function profileImage(){
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function getFullNameAttribute(){
        return $this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name;
    }


}
