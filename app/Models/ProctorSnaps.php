<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use App\Models\User;
use App\Models\CandTest;

class ProctorSnaps extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'examid',
        'path'
    ];
}
