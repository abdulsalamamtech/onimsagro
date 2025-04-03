<?php

namespace App\Enums;

enum UserRoleEnum : string
{
    case SUPERADMIN = "super-admin";
    case ADMIN = 'admin';
    case CUSTOMER = "customer";



    public function label(): string {
        return match($this) {
            UserRoleEnum::SUPERADMIN => "super-admin",
            UserRoleEnum::ADMIN => 'admin',
            UserRoleEnum::CUSTOMER => "customer",
        };
    }

    public static function getValues(): array {  
        return array_column(self::cases(), 'value');  
    } 

}
