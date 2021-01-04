<?php

namespace App\Http\Controllers\API;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class HeaderImageController extends Controller
{
    public function index()
    {
      if(Auth::user())
      {
        $url = Config::get('constants.PROJURL');
        $imageurl = $url.'images/logo/gudExamLogo.png';
        
        return response()->json([
          "status"          =>  "success",
          "url"             =>  $imageurl,
        ], 200);
      }
      else
      {
        return response()->json([
          "status"          =>  "failure",
          "message"         =>  "Unauthorized User...",
        ], 401);
      }
    }
}
