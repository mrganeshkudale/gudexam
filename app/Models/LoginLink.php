<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginLink extends Model
{
    use HasFactory;
    protected $table = 'login_link';
    protected $fillable = ['id','stduid','inst_id','link','created_at','updated_at'];
}
