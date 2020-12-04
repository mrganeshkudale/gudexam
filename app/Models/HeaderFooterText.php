<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeaderFooterText extends Model
{
    use HasFactory;
    protected $table = 'header_footer_text';
    protected $fillable = ['id','header','footer','created_at','updated_at'];
    }
