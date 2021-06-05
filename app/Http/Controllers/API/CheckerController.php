<?php

namespace App\Http\Controllers\API;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Student\Student;
use App\Admin\Admin;
use App\Admin\Admin1;
use Illuminate\Http\Request;

class CheckerController extends Controller
{
    public function allocateChecker(Request $request, Admin1 $a1)
    {
        if(Auth::user())
        {
            return $a1->allocateStudentToCheckers($request);
        }
        else
        {
            return response()->json([
                "status"          =>  "failure",
                "message"         =>  "Unauthorized User...",
            ], 401);
        }
    }

    public function getCheckerAllocation(Request $request,Admin1 $a1)
    {
        if(Auth::user())
        {
            return $a1->getStudentToCheckers($request);
        }
        else
        {
            return response()->json([
                "status"          =>  "failure",
                "message"         =>  "Unauthorized User...",
            ], 401);
        }
    }

    public function deleteCheckerAllocation($id,Admin1 $a1)
    {
        if(Auth::user())
        {
            return $a1->deleteStudentToCheckers($id);
        }
        else
        {
            return response()->json([
                "status"          =>  "failure",
                "message"         =>  "Unauthorized User...",
            ], 401);
        }
    }

    public function searchCheckerAllocation(Request $request,Admin1 $a1)
    {
        if(Auth::user())
        {
            return $a1->searchCheckerAllocation($request);
        }
        else
        {
            return response()->json([
                "status"          =>  "failure",
                "message"         =>  "Unauthorized User...",
            ], 401);
        }
    }

    public function deleteBulkCheckerAllocation(Request $request, Admin1 $a1)
    {
        if(Auth::user())
        {
            return $a1->deleteBulkCheckerAllocation($request);
        }
        else
        {
            return response()->json([
                "status"          =>  "failure",
                "message"         =>  "Unauthorized User...",
            ], 401);
        }
    }

    public function getCheckerStudExams(Request $request,Admin1 $a1)
    {
        if(Auth::user())
        {
            return $a1->getCheckerStudExams($request);
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
