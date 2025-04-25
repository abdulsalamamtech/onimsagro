<?php 

namespace App\Helpers;

use App\Enums\UserRoleEnum;
use App\Models\Api\Driver;
use App\Models\Api\Marketer;
use App\Models\Api\Refinery;
use App\Models\Api\Transporter;

class ActorHelper {

    /** 
     * Retrieve user ID from authenticated user and return.
     */
    public static function getUserId(){
        // Implement logic to fetch user ID from authenticated actor
        // Return the user ID as a string
        return request()?->user()?->id ?? 1;
        // return request()?->user()?->id;

    }




    // public static function getActorByUserId($userId) {
    //     // Implement logic to fetch actor from database using user ID
    //     // Return the actor object
    // }
}