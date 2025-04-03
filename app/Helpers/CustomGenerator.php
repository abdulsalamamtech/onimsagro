<?php 

namespace App\Helpers;

class CustomGenerator {

    /**
     * Generates a random atc number
     */
    public static function generateUniqueATC() {
        $prefix = 'ATC-';
        $time = time();
        $random_int = random_int(10, 99);
        $random_no = mt_rand(100, 999);
        return $prefix . $time . '-'. $random_int . $random_no;
    }
    /**
     * Generates a random pfi number
     */    
    public static function generateUniquePFI() {
        $prefix = 'PFI-';
        $time = time();
        $random_int = random_int(10, 99);
        $random_no = mt_rand(100, 999);
        return $prefix . $time . '-'. $random_int . $random_no;
    }

    /**
     * Generates a random ticket number
     */
    public static function generateUniqueTicketNumber() {
        $randomString = random_int(0, 9);
        $randomVal = uniqid('TIC');
        $time = time();
        return $randomVal . $time . $randomString;
    }


    /**
     * Generates a random waybill number
     */
    public static function generateUniqueWayBillNumber() {
        $randomString = random_int(0, 9);
        $randomVal = uniqid('WBN');
        $time = time();
        return $randomVal . $time . $randomString;
    }


    
    public static function generateUniqueName($length = 10) {
        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString.= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    public static function generateUniquePhoneNumber($length = 10) {
        $characters = '0123456789';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString.= $characters[rand(0, strlen($characters) - 1)];
        }
        return '+234'. $randomString;
    }
    
    public static function generateUniqueEmail($length = 10) {
        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString.= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString. '@tmmmsapp.com';
    }
    public static function generateUniqueID($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString.= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    public static function generateUniqueNumber($length = 10) {
        $characters = '0123456789';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString.= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    public static function generateUniqueString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    public static function generateUniqueCode($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    public static function generateRandomNumber($length = 10) {
        $characters = '0123456789';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString.= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    public static function generateRandomString($length = 10) {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString.= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
}