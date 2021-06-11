<?php

namespace App\Http\Controllers\API;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Student\Student;
use App\Admin\Admin;
use App\Admin\Admin1;
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

  public function allocateProctor(Request $request, Admin1 $a1)
  {
        if(Auth::user())
        {
            return $a1->allocateStudentToProctor($request);
        }
        else
        {
            return response()->json([
                "status"          =>  "failure",
                "message"         =>  "Unauthorized User...",
            ], 401);
        }
  }

  public function getProctorAllocation(Request $request,Admin1 $a1)
  {
      if(Auth::user())
      {
          return $a1->getStudentToProctors($request);
      }
      else
      {
          return response()->json([
              "status"          =>  "failure",
              "message"         =>  "Unauthorized User...",
          ], 401);
      }
  }

  public function deleteProctorAllocation($id,Admin1 $a1)
  {
      if(Auth::user())
      {
          return $a1->deleteStudentToProctors($id);
      }
      else
      {
          return response()->json([
              "status"          =>  "failure",
              "message"         =>  "Unauthorized User...",
          ], 401);
      }
  }


  public function searchProctorAllocation(Request $request,Admin1 $a1)
    {
        if(Auth::user())
        {
            return $a1->searchProctorAllocation($request);
        }
        else
        {
            return response()->json([
                "status"          =>  "failure",
                "message"         =>  "Unauthorized User...",
            ], 401);
        }
    }

    public function deleteBulkProctorAllocation(Request $request, Admin1 $a1)
    {
        if(Auth::user())
        {
            return $a1->deleteBulkProctorAllocation($request);
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
