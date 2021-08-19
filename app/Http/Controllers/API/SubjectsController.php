<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Admin\Admin;
use App\Admin\Admin1;

class SubjectsController extends Controller
{
  public function show(Request $request, Admin $a)
  {
    return $a->getSubjects($request->program_id);
  }

  public function store(Request $request, Admin $a)
  {
    return $a->storeSubjects($request);
  }

  public function update($id, Request $request, Admin $a)
  {
    if ($request->type == 'test') {
      return $a->updateTestSubjects($id, $request);
    } else if ($request->type == 'form') {
      return $a->updateSubjectMaster($id, $request);
    }
  }

  public function upload(Request $request, Admin $a)
  {
    return $a->uploadSubjects($request);
  }

  public function index(Request $request, Admin $a, Admin1 $a1)
  {
    if ($request->type == 'all') {
      return $a->getAllSubjects();
    }
    if ($request->type == 'byInstUid') {
      return $a->getSubjectsByInstUid($request->instUid, $request->mode);
    }
    if ($request->type == 'byProctorUid') {
      return $a1->getSubjectByProctor($request->proctorUid);
    }
    if ($request->type == 'byProctorId') {
      return $a1->getSubjectByProctorId($request->proctorId);
    }
    if ($request->type == 'byPaperSetter') {
      return $a1->getSubjectByPaperSetter($request->paperSetterId,$request->instId,$request->mode);
    }
  }

  public function del($id, Admin $a)
  {
    return $a->delSubject($id);
  }

  public function storeTopic(Request $request, Admin $a)
  {
    return $a->storeTopic($request);
  }

  public function storeTopicUpload(Request $request, Admin $a)
  {
    return $a->storeTopicUpload($request);
  }

  public function getTopic(Request $request, Admin $a)
  {
    if ($request->type == 'single') {
      return $a->getTopicDataSingle($request->paperId);
    }
  }

  public function delTopic($id, Admin $a)
  {
    return $a->delTopicData($id);
  }

  public function uploadTest(Request $request, Admin $a)
  {
    return $a->uploadTestsSubjects($request);
  }

  public function updateTest($id, Request $request, Admin $a)
  {
    if ($request->type == 'clearTest') {
      return $a->clearTestsSubjects($id);
    }
  }

  public function updateConfig($id, Request $request, Admin $a)
  {
    return $a->updateConfigSubject($id, $request);
  }

  public function showById($id, Admin $a)
  {
    return $a->getSubjectById($id);
  }

  public function storeQuestion(Request $request, Admin $a, Admin1 $a1)
  {
    if ($request->questType == 'S') {
      return $a1->storeSubjectiveQuestion($request);
    } else {
      return $a->storeQuestion($request);
    }
  }

  public function uploadQuestion(Request $request, Admin $a)
  {
    return $a->uploadQuestion($request);
  }

  public function uploadSubjectiveQuestion(Request $request, Admin1 $a1)
  {
    return $a1->uploadSubjectiveQuestion($request);
  }

  public function getGenericConfig(Request $request, Admin $a)
  {
    return $a->getGenericConfig($request);
  }

  public function updateGenericConfig($id, Request $request, Admin $a)
  {
    return $a->updateGenericConfig($id, $request);
  }

  public function getSubjectByDate($date, Admin $a)
  {
    return $a->getSubjectByDate($date);
  }

  public function getSubjectByDateInst($date, $inst, Admin $a)
  {
    return $a->getSubjectByDateInst($date, $inst);
  }

  public function getStudBySubject($id, Admin1 $a1, Request $request)
  {
    if ($request->type == 'allStudents') {
      $instId = $request->instId;
      return $a1->getAllStudentsBySubject($id, $instId);
    } else {
      return $a1->getStudentsBySubject($id,$request);
    }
  }

  public function getStudBySubject1($id, Admin1 $a1)
  {
    return $a1->getStudentsBySubject1($id);
  }

  public function getCheckerBySubject($id, Request $request,Admin1 $a1)
  {
    return $a1->getCheckersBySubject($id,$request);
  }

  public function getProctorBySubject($id, Admin1 $a1)
  {
    return $a1->getProctorBySubject($id);
  }

  public function getSubjectByChecker($uid, Admin1 $a1)
  {
    return $a1->getSubjectByChecker($uid);
  }

  public function storeSubSetterAlloc(Request $request,Admin1 $a1)
  {
    return $a1->storeSubSetterAlloc($request);
  }

  public function uploadSetterSubjects(Request $request,Admin1 $a1)
  {
    return $a1->uploadSetterSubjects($request);
  }

  public function getSubSetterAlloc(Request $request,Admin1 $a1)
  {
    return $a1->getSubSetterAlloc($request);
  }

  public function deleteSubSetterAlloc($id,Admin1 $a1)
  {
    return $a1->deleteSubSetterAlloc($id);
  }

  public function setterConfirmation($uid,Request $request, Admin1 $a1)
  {
    return $a1->setterConfirmation($uid,$request);
  }

  public function unconfSubList(Request $request, Admin1 $a1)
  {
    return $a1->unconfSubList($request);
  }

  public function getSubjectConfInfo($id,Admin1 $a1)
  {
    return $a1->getSubjectConfInfo($id);
  }

  public function storeSubCheckerAlloc(Request $request, Admin1 $a1)
  {
    return $a1->storeSubCheckerAlloc($request);
  }

  public function uploadCheckerSubjects(Request $request , Admin1 $a1)
  {
    return $a1->uploadCheckerSubjects($request);
  }

  public function getSubCheckerAlloc(Request $request , Admin1 $a1)
  {
    return $a1->getSubCheckerAlloc($request);
  }

  public function deleteSubCheckerAlloc($id, Admin1 $a1)
  {
    return $a1->deleteSubCheckerAlloc($id);
  }
}
