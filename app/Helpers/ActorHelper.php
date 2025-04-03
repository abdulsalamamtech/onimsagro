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
        return request()->user()->id;
    }

    /** 
     * Retrieve Refinery's ID from authenticated actor and return.
     */
    public static function getRefineryId(){
        // Return the user Refinery's ID
        $user =  request()->user();
        info('Refinery information is being access:', [$user]);

        // Getting refinery information
        // $refinery = Refinery::where('user_id', request()->user()->id)->first();
        // return $refinery->id?? 1;

        // Getting marketer information
        $refinery = Refinery::where('user_id', $user->id)->first();
        if($refinery){
            return $refinery->id;
        }
        else if(!$refinery && $user 
            && $user->role_actor_name == UserRoleEnum::TRANSPORTER 
            && $user->role_actor_id){
            return Refinery::findOrFail($user->role_actor_id);
        }else{
            return throw new \Exception("Error Processing Request: Refinery actor helper", 1);
        }
    } 

    /** 
     * Retrieve Marketer's ID from authenticated actor and return.
     */
    public static function getMarketerId(){

        // Return the user marketer's ID
        $user = request()->user();
        info('Marketer information is being access:', [$user]);

        // Getting marketer information
        $marketer = Marketer::where('user_id', $user->id)->first();
        if($marketer){
            return $marketer->id;
        }
        else if(!$marketer && $user 
            && $user->role_actor_name == UserRoleEnum::MARKETER 
            && $user->role_actor_id){
            return Marketer::findOrFail($user->role_actor_id);
        }else{
            return throw new \Exception("Error Processing Request: Marketer actor helper", 1);
        }
    }


    /** 
     * Retrieve Transporter's ID from authenticated actor and return.
     */
    public static function getTransporterId(){
        
        // Return the user Transporter's ID
        $user = request()->user();
        info('Transporter information is being access:', [$user]);

        // Getting marketer information
        $transporter = Transporter::where('user_id', $user->id)->first();
        if($transporter){
            return $transporter->id;
        }
        else if(!$transporter && $user 
            && $user->role_actor_name == UserRoleEnum::TRANSPORTER 
            && $user->role_actor_id){
            return Transporter::findOrFail($user->role_actor_id);
        }else{
            return throw new \Exception("Error Processing Request: Transporter actor helper", 1);
        }
    }    


    /** 
     * Retrieve Driver's ID from authenticated actor and return.
     */
    public static function getDriverId(){
        // Return the user Driver's ID
        $user = request()->user();
        info('Driver information is being access:', [$user]);

        // Getting driver information
        $driver = Driver::where('user_id', $user->id)->first();
        if($driver){
            return $driver->id;
        }else{
            return throw new \Exception("Error Processing Request: Driver actor helper", 1);
        }
    }     



    // public static function getActorByUserId($userId) {
    //     // Implement logic to fetch actor from database using user ID
    //     // Return the actor object
    // }
}