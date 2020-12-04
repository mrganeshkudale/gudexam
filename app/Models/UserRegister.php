<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRegister extends Model
{
    use HasFactory;
    protected $table = 'users_register';
    protected $fillable = ['regi_type','username','eadmin_name','inst_id','college_name','mobile',
    'email','password','origpass','docpath','status','wallet_balance','created_at',
    'updated_at','verify_on'];
}

