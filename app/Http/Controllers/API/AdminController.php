<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AdminController extends Controller
{
  public function adminhome()
  {
      if(Auth::user()->role != 'ADMIN')
      {
        return response()->json([
          "status"    =>  "failure",
          "message"   =>  "Unauthorized User...",
        ], 401);
      }

      return response()->json([
        "status"    =>  "success",
        "message"   =>  "User logged in successfully...",
        "data"      =>  Auth::user(),
      ], 200);
  }
}
