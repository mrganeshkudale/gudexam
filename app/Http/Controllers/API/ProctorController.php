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
  public function index(Request $request,Admin1 $a1)
  {
    if(Auth::user())
    {
        return $a1->getProctors($request);
    }
    else
    {
      return response()->json([
        "status"          =>  "failure",
        "message"         =>  "Unauthorized User...",
      ], 401);
    }
  }

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


  public function proctorByEnrollno($enrollno,$paperId,Admin $a,Request $request,Admin1 $a1)
  {
    if(Auth::user())
    {
        if($request->type == 'latestSnap')
        {
            return $a1->proctorLatestByEnrollno($enrollno,$paperId,$request->instId,$request->stdid,$request->examid);
        }
        else
        {
            if(Auth::user()->role == 'EADMIN')
            {
                return $a->proctorByEnrollno($enrollno,$paperId);
            }
            else
            {
                $instId = $request->instId;
                return $a1->proctorByEnrollnoInstId($enrollno,$paperId,$instId);
            }
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
            ], 401);<Link  className="nav-link" to={{pathname: "/proctoringSummaryReport"}}>
            Proctoring Summary Report
        </Link>
      {
          if($request->type == 'byPaperId')
          {
              if($request->studid != '')
              {
                return $a1->getSingleStudentToProctorsBySubject($request);
              }
              else
              {
                return $a1->getStudentToProctorsBySubject($request);
              }
          }
          else
          {
            return $a1->getStudentToProctors($request);
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

    public function sendWarning($examid,Request $request,Admin1 $a1)
    {
        if(Auth::user())
        {
            return $a1->sendProctorWarning($examid,$request);
        }
        else
        {
            return response()->json([
                "status"          =>  "failure",
                "message"         =>  "Unauthorized User...",
            ], 401);
        }
    }

    public function uploadStudProctor(Request $request,Admin1 $a1)
    {
        if(Auth::user())
        {
            return $a1->uploadStudProctor($request);
        }
        else
        {
            return response()->json([
                "status"          =>  "failure",
                "message"         =>  "Unauthorized User...",
            ], 401);
        }
    }

    public function notedWarning($warningId,Admin1 $a1)
    {
        if(Auth::user())
        {
            return $a1->notedWarning($warningId);
        }
        else
        {
            return response()->json([
                "status"          =>  "failure",
                "message"         =>  "Unauthorized User...",
            ], 401);
        }
    }

    public function getProctorSummary(Request $request,Admin1 $a1)
    {
        if(Auth::user())
        {
            return $a1->getProctorSummary($request);
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
