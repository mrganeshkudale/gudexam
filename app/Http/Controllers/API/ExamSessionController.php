<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Student\Student;
use Illuminate\Http\Request;

class ExamSessionController extends Controller
{
  public function update(Request $request, Student $s)
  {
    if ($request->type == 'additionalTime') {
      return $s->additionalExamSession($request->exam_id, $request->time);
    } else {
      return $s->updateExamSession($request->exam_id);
    }
  }

  public function show(Request $request, Student $s)
  {
    return $s->getExamSession($request->exam_id);
  }
}
