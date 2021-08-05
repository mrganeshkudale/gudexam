<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Student\Student;
use App\Admin\Admin;
use App\Admin\Admin1;
use Illuminate\Http\Request;


class AnswerController extends Controller
{
  public function index(Student $s, Request $request, Admin1 $a1)
  {
    if ($request->type == 'subjective') {
      return $a1->getSubjectiveAnswers($request->exam_id);
    } else {
      return $s->getAnswers($request->exam_id);
    }
  }

  public function show(Student $s, $id)
  {
    return $s->getAnswer($id);
  }

  public function upload(Student $s, $id, Request $request)
  {
    return $s->uploadAnswerImage($id, $request);
  }

  public function update(Student $s, Request $request, $id)
  {
    if ($request->type == 'saveanswer') {
      return $s->updateAnswer($request, $id);
    } else if ($request->type == 'savesubjectiveanswer') {
      return $s->updateSubjectiveAnswer($request, $id);
    } else if ($request->type == 'savereview') {
      return $s->updateReview($request, $id);
    } else if ($request->type == 'removeAnswerImage') {
      return $s->removeAnswerImage($request, $id);
    }
  }

  public function updateByExamId($qnidSr, $examId, Admin $a)
  {
    return $a->clearResponse($qnidSr, $examId);
  }
}
