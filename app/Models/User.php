<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use App\Models\StudentExam;
use App\Models\Session;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;
    protected $table = 'users';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uid',
        'username',
        'pcode',
        'seatno',
        'inst_id',
        'region',
        'course_code',
        'semester',
        'mobile',
        'email',
        'role',
        'password',
        'origpass',
        'pa',
        'status',
        'name',
        'password',
        'regi_type',
        'college_name',
        'docpath','verified','verify_on','wallet_balance'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password','origpass','pcode','seatno','verify_on','region','wallet_balance','mobile','email','docpath'
    ];

    protected $primaryKey = 'uid';

    public function sessions()
    {
      return $this->hasMany('App\Models\Session','uid');
    }

    public function answers()
    {
        return $this->hasMany('App\Models\CandQuestion','stdid');
    }

    public function exams()
    {
        return $this->hasMany('App\Models\CandTest','stdid');
    }
}
