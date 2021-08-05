<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Student\Student;
use App\Admin\Admin;
use App\Admin\Admin1;
use Illuminate\Http\Request;

class ExamController extends Controller
{
  public function index(Student $s, Admin $a, Request $request)
  {
    $AuthUser = Auth::user();

    if ($AuthUser->role == 'STUDENT') {
      return $s->getExams();
    } else if ($AuthUser->role != 'EADMIN' && $request->type == 'byEnrollno') {
      return $a->getExamsByEnrollnoInstId($request->enrollno, $request->instId);
    } else if ($AuthUser->role == 'ADMIN') {
      return $a->getAllExams();
    } else if ($AuthUser->role == 'EADMIN' && $request->type == '') {
      return $a->getFilteredExams($request->instId);
    } else if ($AuthUser->role == 'EADMIN' && $request->type == 'byEnrollno') {
      return $a->getExamsByEnrollno($request->enrollno);
    } else if ($AuthUser->role == 'EADMIN' && $request->type == 'StudentSubAllocReport') {
      return $a->studSubAlloc($request);
    }
  }

  public function show(Request $request, Admin $a)
  {
    if ($request->type === 'byprogramid') {
      return $a->getExams($request->id);
    }
  }
  public function update2(Student $s, Admin1 $a1, Request $request)
  {
    if ($request->status === 'reset') {
      return $a1->resetExam($request);
    }
  }

  public function update(Student $s, Admin $a, Request $request, $id, Admin1 $a1)
  {
    if ($request->status === 'start') {
      return $s->startExam($id);
    } else if ($request->status === 'end') {
      return $s->endExam($id);
    } else if ($request->status === 'endExamProctor') {
      return $a1->endExamProctor($id, $request->reason);
    } else if ($request->status === 'windowswitch') {
      return $s->windowSwitchExam($id);
    } else if ($request->status === 'preview') {
      return $a->previewExam(true);
    } else if ($request->status == 'saveCurQuestion') {
      return $s->updateCurQuestion($id, $request);
    } else {
      return response()->json([
        "status"          =>  "failure",
        "message"         =>  "Incorrect Input Parameters...",
      ], 401);
    }
  }

  public function startexam(Student $s, Request $request)
  {
    return $s->startExam($request->exam_id);
  }

  public function store(Request $request, Admin $a)
  {
    return $a->storeStudSubjectMapping($request);
  }

  public function upload(Request $request, Admin $a)
  {
    return $a->uploadStudSubjectMapping($request);
  }

  public function del($id, Admin $a)
  {
    return $a->delExam($id);
  }

  public function examReportCount(Request $request, Admin $a)
  {
    $AuthUser = Auth::user();
    if ($AuthUser && $AuthUser->role != 'STUDENT') {
      if ($request->type == 'instwise') {
        return $a->examReportCount($request->instId);
      } else {
        if ($AuthUser->role == 'EADMIN') {
          return $a->examReportCount($AuthUser->username);
        }
      }
    } else {
      return response()->json([
        "status"          =>  "failure",
        "message"         =>  "Unauthorized User...",
      ], 400);
    }
  }

  public function examByPaperIdAndType(Request $request, Admin $a)
  {
    return $a->examByPaperIdAndType($request);
  }

  public function examLog($enrollno, $paperId, Admin $a, Request $request)
  {
    $instId = $request->instId;
    return $a->getExamLog($enrollno, $paperId, $instId);
  }

  public function examReportCountByDate(Request $request, Admin $a)
  {
    return $a->examReportCountByDate($request);
  }

  public function examReportCountDatewise($date, $subject, $slot, Admin $a, Request $request)
  {
    $AuthUser = Auth::user();
    if ($AuthUser->role == 'EADMIN') {
      return $a->examReportCountDatewise($date, $subject, $slot, $AuthUser->username);
    } else if ($AuthUser->role == 'ADMIN') {
      return $a->examReportCountDatewise($date, $subject, $slot, $request->instId);
    }
  }

  public function getAutoEndExamCount(Request $request, Admin $a)
  {
    return $a->getAutoEndExamCount($request);
  }

  public function autoEndExam($date, Request $request, Admin $a)
  {
    return $a->autoEndExam($date, $request);
  }

  public function getActiveExamCount(Request $request, Admin $a)
  {
    return $a->getActiveExamCount($request);
  }

  public function examReportCountDateInstWise(Request $request, Admin $a)
  {
    if($request->date == '' || $request->date == null)
    {
      return $a->allExamReportCountDateInstWise();
    }
    else
    {
      return $a->examReportCountDateInstWise($request->date, $request->slot);
    }
  }

  public function getExamSwitchCount($id, Student $s, Admin1 $a1)
  {
    return $s->getExamSwitchCount($id);
  }
}
