<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Student\Student;
use App\Admin\Admin;
use App\Admin\Admin1;
use Illuminate\Http\Request;

class QuestionSetController extends Controller
{
  public function index(Admin1 $a1, Request $request)
  {
    if ($request->type == 'byQnid') {
      return $a1->searchQuestionByQnid($request->search);
    }
  }

  public function show(Admin $a, Request $request, Admin1 $a1)
  {
    if ($request->type == 'preview') {
      $paper_id = $request->paper_id;
      return $a->getPreviewQuestions($paper_id);
    } else if ($request->type == 'getAllQuestionsFromArray') {
      $subArray   = $request->paper_id;
      if ($request->questType == 'subjective') {
        return $a1->getAllQuestionsFromArray($subArray);
      } else {
        return $a->getAllQuestionsFromArray($subArray);
      }
    } else if ($request->type == 'getAllQuestionsByPaperCode') {
      $paper_id = $request->paper_id;
      return $a1->getAllQuestionsByPaperCode($paper_id);
    }
  }

  public function specificationCompare(Request $request, Admin $a)
  {
    if ($request->type == 'match') {
      return $a->specificationMatch();
    } else {
      return $a->specificationCompare();
    }
  }

  public function delete($qnid, Admin $a)
  {
    return $a->deleteQuestion($qnid);
  }

  public function getQuestion($qnid, Admin $a)
  {
    return $a->getQuestion($qnid);
  }

  public function updateQuestion($qnid, Request $request, Admin $a, Admin1 $a1)
  {
    if ($request->questType == 'S') {
      return $a1->updateSubjectiveQuestion($qnid, $request);
    } else {
      return $a->updateQuestion($qnid, $request);
    }
  }
}
