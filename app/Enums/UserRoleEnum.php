<?php

namespace App\Enums;

enum UserRoleEnum : string
{
    case SUPERADMIN = "super-admin";
    case ADMIN = 'admin';
    case FARMER = 'farmer';
    case CUSTOMER = "customer";



    public function label(): string {
        return match($this) {
            UserRoleEnum::SUPERADMIN => "super-admin",
            UserRoleEnum::ADMIN => 'admin',
            UserRoleEnum::FARMER => 'farmer',
            UserRoleEnum::CUSTOMER => "customer",
        };
    }

    public static function getValues(): array {  
        return array_column(self::cases(), 'value');  
    } 

}
