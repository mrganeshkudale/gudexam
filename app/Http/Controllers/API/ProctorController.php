<?php

namespace App\Http\Controllers\API;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Student\Student;
use Illuminate\Http\Request;

class ProctorController extends Controller
{
  public function store(Student $s,Request $request)
  {
    if(Auth::user())
    { 
        if($request->type == 'snapshot')
        {
            return $s->storeSnapshot($request->exam,$request->image);
        }
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
