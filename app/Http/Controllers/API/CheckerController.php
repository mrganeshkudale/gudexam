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
        return $a1->allocateStudentToCheckers($request);
    }

    public function getCheckerAllocation(Request $request, Admin1 $a1)
    {
        return $a1->getStudentToCheckers($request);
    }

    public function deleteCheckerAllocation($id, Admin1 $a1)
    {
        return $a1->deleteStudentToCheckers($id);
    }

    public function searchCheckerAllocation(Request $request, Admin1 $a1)
    {
        return $a1->searchCheckerAllocation($request);
    }

    public function deleteBulkCheckerAllocation(Request $request, Admin1 $a1)
    {
        return $a1->deleteBulkCheckerAllocation($request);
    }

    public function getCheckerStudExams(Request $request, Admin1 $a1)
    {
        return $a1->getCheckerStudExams($request);
    }

    public function updateStudExamMarks($id, $marks,Request $request, Admin1 $a1)
    {
        return $a1->updateStudExamMarks($id, $marks, $request);
    }

    public function finishExamChecking($examid, Request $request, Admin1 $a1)
    {
        return $a1->finishExamChecking($examid, $request);
    }

    public function getCheckerType(Request $request , Admin1 $a1)
    {
        return $a1->getCheckerType($request);
    }

    public function getCheckedStudExams(Request $request, Admin1 $a1)
    {
        return $a1->getCheckedStudExams($request);
    }
}
