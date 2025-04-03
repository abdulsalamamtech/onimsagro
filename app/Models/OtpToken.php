<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpToken extends Model
{
    protected $fillable = ['user_id', 'phone_number', 'email', 'otp_code', 'expiry_time', 'is_verified'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function generateOTPCode($length = 6){
        return rand(pow(10, $length - 1), pow(10, $length) - 1);
    }

    public function generateOTPToken($length = 6){
        $otp = $this->generateOTPCode($length);
        $this->otp_code = $otp;
        $this->expiry_time = now()->addMinutes(10);
        $this->save();
        return $otp;
    }

    public function isExpired(){
        return now() > $this->expiry_time;
    }

    public function verifyToken(){
        $this->is_verified = true;
        $this->save();
    }

    public function sendOTP($phone_number, $email){
        // Implement sending OTP code via SMS or email
        // Example:
        // $this->sendSMS($phone_number, $this->otp_code);
        // $this->sendEmail($email, $this->otp_code);
        return true;
    }

    public function sendSMS($phone_number, $otp_code){
        // Implement sending SMS using a third-party API or SMS gateway
        // Example:
        // $message = "Your OTP code is: $otp_code";
        // $this->sendSMSGateway($phone_number, $message);
        return true;
    }

    public function sendEmail($email, $otp_code){
        // Implement sending email with OTP code using a third-party email service or SMTP
        // Example:
        // $subject = "OTP Code";
        // $message = "Your OTP code is: $otp_code";
        // $this->sendEmailGateway($email, $subject, $message);
        return true;
    }
}
