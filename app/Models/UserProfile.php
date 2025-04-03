<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id', 'asset_id', 'first_name','middle_name', 'last_name', 'address', 'city','state', 'zip_code', 'country'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function asset(){
        return $this->belongsTo(Asset::class);
    }

    public function getFullNameAttribute(){
        return $this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name;
    }

    public function getAddressAttribute(){
        return $this->address . ', ' . $this->city . ', ' . $this->state . ', ' . $this->zip_code . ', ' . $this->country;
    }

    
}
