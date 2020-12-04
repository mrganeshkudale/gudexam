<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OTPVerify extends Model
{
    use HasFactory;
    protected $table = 'otp_verify';
    protected $fillable = ['id','mobile','otp','created_at','updated_at'];
}
