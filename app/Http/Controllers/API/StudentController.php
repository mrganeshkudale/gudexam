<?php

namespace App\Http\Controllers\API;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Student\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function studenthome(Student $s)
    {
      $studData = array();
      if(Auth::user())
      {
        $url = Config::get('constants.PROJURL');
        //------------------------Get Total Exams Allocation Count-----------------
        array_push($studData,[
          'username'        => Auth::user()->username,
          'role'            => Auth::user()->role,
          'all'             => $s->getAllocatedSubjectCount(),
          'compleated'      => $s->getExamCompleatedCount(),
          'yetToStart'      => $s->getExamYetNotGivenCount(),
          'ongoing'         => $s->getExamOngoingCount(),
          'expired'         => $s->getExamExpiredCount(),
          'paperData'       => $s->getStudentExamData()
        ]);

        //-------------------------------------------------------------------------
        return response()->json([
          "status"          =>  "success",
          "message"         =>  "Student Logged in Successfully...",
          "logo"            =>  $url.'images/logo/gudExamLogo.png',
          "data"            =>  $studData,
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

    public function startexamInstructions(Request $request,Student $s)
    {
      if(Auth::user())
      {
        return $s->getInstructions($request->paper_code);
      }
      else
      {
        return response()->json([
          "status"          =>  "failure",
          "message"         =>  "Unauthorized User...",
        ], 401);
      }
    }

    public function startexam(Request $request,Student $s)
    {
      if(Auth::user())
      {
        return $s->startexam($request->paper_code,$request->flag);
      }
      else
      {
        return response()->json([
          "status"          =>  "failure",
          "message"         =>  "Unauthorized User...",
        ], 401);
      }
    }

    public function getQuestion(Request $request,Student $s)
    {
      if(Auth::user())
      {
        return $s->getQuestion($request->paper_code,$request->old_qnid_sr,$request->next_qnid_sr,$request->timer,$request->flag);
      }
      else
      {
        return response()->json([
          "status"          =>  "failure",
          "message"         =>  "Unauthorized User...",
        ], 401);
      }
    }

    public function markUnmarkReview(Request $request,Student $s)
    {
      if(Auth::user())
      {
        return $s->markUnmarkReview($request->paper_code,$request->qnid_sr,$request->question_review,$request->flag);
      }
      else
      {
        return response()->json([
          "status"          =>  "failure",
          "message"         =>  "Unauthorized User...",
        ], 401);
      }
    }

    public function saveAnswer(Request $request,Student $s)
    {
      if(Auth::user())
      {
        return $s->saveAnswer($request->paper_code,$request->qnid_sr,$request->answer,$request->timer,$request->flag);
      }
      else
      {
        return response()->json([
          "status"          =>  "failure",
          "message"         =>  "Unauthorized User...",
        ], 401);
      }
    }

    public function preEndExam(Request $request,Student $s)
    {
      if(Auth::user())
      {
        return $s->preEndExam($request->paper_code);
      }
      else
      {
        return response()->json([
          "status"          =>  "failure",
          "message"         =>  "Unauthorized User...",
        ], 401);
      }
    }

    public function endExam(Request $request,Student $s)
    {
      if(Auth::user())
      {
        return $s->endExam($request->paper_code,$request->timer,$request->flag);
      }
      else
      {
        return response()->json([
          "status"          =>  "failure",
          "message"         =>  "Unauthorized User...",
        ], 401);
      }
    }

    public function searchSubjects(Request $request, Student $s)
    {
      if(Auth::user())
      {
        $data = $s->searchSubjects($request->paper_name);
        if($data)
        {
          return response()->json([
            "status"          =>  "success",
            "data"            =>  $data,
          ], 200);
        }
        else
        {
          return response()->json([
            "status"          =>  "failure",
            "message"         =>  "Subject Not Found...",
          ], 401);
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
