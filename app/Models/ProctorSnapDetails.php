<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\CandTest;

class ProctorSnapDetails extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'examid',
        'snapid',
        'agerange',
        'beared',
        'eyeglasses',
        'eyesopen',
        'gender',
        'mustache',
        'smile',
        'sunglasses',
        'created_at',
        'updated_at'

    ];
}
