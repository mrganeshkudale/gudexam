<?php

namespace App\Http\Controllers\API;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Student\Student;
use App\Admin\Admin;
use Illuminate\Http\Request;

class ProctorController extends Controller
{
  public function store($id,Request $request,Student $s)
  {
    if(Auth::user())
    {
        if($request->type == 'snapshot')
        {
            return $s->storeSnapshot($id,$request->image);
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


  public function proctorByEnrollno($enrollno,$paperId,Admin $a)
  {
    if(Auth::user())
    {
      return $a->proctorByEnrollno($enrollno,$paperId);
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
