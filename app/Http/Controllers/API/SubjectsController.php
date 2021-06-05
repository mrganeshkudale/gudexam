<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Admin\Admin;
use App\Admin\Admin1;

class SubjectsController extends Controller
{
  public function show(Request $request,Admin $a)
  {
    if(Auth::user())
    {
        return $a->getSubjects($request->program_id);
    }
    else
    {
        return response()->json([
            "status"          =>  "failure",
            "message"         =>  "Unauthorized User...",
        ], 200);
    }
  }

  public function store(Request $request,Admin $a)
  {
    if(Auth::user())
    {
        return $a->storeSubjects($request);
    }
    else
    {
        return response()->json([
            "status"          =>  "failure",
            "message"         =>  "Unauthorized User...",
        ], 200);
    }
  }

  public function update($id,Request $request,Admin $a)
  {
    if(Auth::user())
    {
      if($request->type == 'test')
      {
        return $a->updateTestSubjects($id,$request);
      }
      else if($request->type == 'form')
      {
        return $a->updateSubjectMaster($id,$request);
      }
    }
    else
    {
        return response()->json([
            "status"          =>  "failure",
            "message"         =>  "Unauthorized User...",
        ], 200);
    }
  }

  public function upload(Request $request,Admin $a)
  {
    if(Auth::user())
    {
        return $a->uploadSubjects($request);
    }
    else
    {
        return response()->json([
            "status"          =>  "failure",
            "message"         =>  "Unauthorized User...",
        ], 200);
    }
  }

  public function index(Request $request,Admin $a)
  {
    if(Auth::user())
    {
      if($request->type == 'all')
      {
        return $a->getAllSubjects();
      }
      if($request->type == 'byInstUid')
      {
        return $a->getSubjectsByInstUid($request->instUid,$request->mode);
      }
    }
    else
    {
        return response()->json([
            "status"          =>  "failure",
            "message"         =>  "Unauthorized User...",
        ], 200);
    }
  }  

  public function del($id,Admin $a)
  {
    if(Auth::user())
    {
      return $a->delSubject($id);
    }
    else
    {
        return response()->json([
            "status"          =>  "failure",
            "message"         =>  "Unauthorized User...",
        ], 200);
    }
  }  

  public function storeTopic(Request $request,Admin $a)
  {
    if(Auth::user())
    {
      return $a->storeTopic($request);
    }
    else
    {
        return response()->json([
            "status"          =>  "failure",
            "message"         =>  "Unauthorized User...",
        ], 200);
    }
  }  

  public function storeTopicUpload(Request $request,Admin $a)
  {
    if(Auth::user())
    {
      return $a->storeTopicUpload($request);
    }
    else
    {
        return response()->json([
            "status"          =>  "failure",
            "message"         =>  "Unauthorized User...",
        ], 200);
    }
  }  

  public function getTopic(Request $request,Admin $a)
  {
    if(Auth::user())
    {
      if($request->type == 'single')
      {
        return $a->getTopicDataSingle($request->paperId);
      }
    }
    else
    {
        return response()->json([
            "status"          =>  "failure",
            "message"         =>  "Unauthorized User...",
        ], 200);
    }
  }  

  public function delTopic($id,Admin $a)
  {
    if(Auth::user())
    {
        return $a->delTopicData($id);
    }
    else
    {
        return response()->json([
            "status"          =>  "failure",
            "message"         =>  "Unauthorized User...",
        ], 200);
    }
  } 
  
  public function uploadTest(Request $request,Admin $a)
  {
    if(Auth::user())
    {
        return $a->uploadTestsSubjects($request);
    }
    else
    {
        return response()->json([
            "status"          =>  "failure",
            "message"         =>  "Unauthorized User...",
        ], 200);
    }
  }

  public function updateTest($id,Request $request,Admin $a)
  {
    if(Auth::user())
    {
      if($request->type == 'clearTest')
      {
        return $a->clearTestsSubjects($id);
      }
    }
    else
    {
        return response()->json([
            "status"          =>  "failure",
            "message"         =>  "Unauthorized User...",
        ], 200);
    }
  }

  public function updateConfig($id,Request $request,Admin $a)
  {
    if(Auth::user())
    {
      return $a->updateConfigSubject($id,$request);
    }
    else
    {
        return response()->json([
            "status"          =>  "failure",
            "message"         =>  "Unauthorized User...",
        ], 200);
    }
  }

 public function showById($id,Admin $a)
 {
    if(Auth::user())
    {
      return $a->getSubjectById($id);
    }
    else
    {
        return response()->json([
            "status"          =>  "failure",
            "message"         =>  "Unauthorized User...",
        ], 200);
    }
 }

 public function storeQuestion(Request $request, Admin $a,Admin1 $a1)
 {
    if(Auth::user())
    {
      if($request->questType == 'S')
      {
        return $a1->storeSubjectiveQuestion($request);
      }
      else
      {
        return $a->storeQuestion($request);
      }
    }
    else
    {
        return response()->json([
            "status"          =>  "failure",
            "message"         =>  "Unauthorized User...",
        ], 200);
    }
 }

 public function uploadQuestion(Request $request , Admin $a)
 {
    if(Auth::user())
    {
      return $a->uploadQuestion($request);
    }
    else
    {
        return response()->json([
            "status"          =>  "failure",
            "message"         =>  "Unauthorized User...",
        ], 200);
    }
 }

 public function uploadSubjectiveQuestion(Request $request , Admin1 $a1)
 {
    if(Auth::user())
    {
      return $a1->uploadSubjectiveQuestion($request);
    }
    else
    {
        return response()->json([
            "status"          =>  "failure",
            "message"         =>  "Unauthorized User...",
        ], 200);
    }
 }

 public function getGenericConfig(Request $request,Admin $a)
 {
    if(Auth::user())
    {
      return $a->getGenericConfig($request);
    }
    else
    {
        return response()->json([
            "status"          =>  "failure",
            "message"         =>  "Unauthorized User...",
        ], 200);
    }
 }

 public function updateGenericConfig($id,Request $request,Admin $a)
 {
    if(Auth::user())
    {
      return $a->updateGenericConfig($id,$request);
    }
    else
    {
        return response()->json([
            "status"          =>  "failure",
            "message"         =>  "Unauthorized User...",
        ], 200);
    }
 }

 public function getSubjectByDate($date,Admin $a)
 {
    if(Auth::user())
    {
      return $a->getSubjectByDate($date);
    }
    else
    {
        return response()->json([
            "status"          =>  "failure",
            "message"         =>  "Unauthorized User...",
        ], 200);
    }
 }

 public function getSubjectByDateInst($date,$inst,Admin $a)
 {
    if(Auth::user())
    {
      return $a->getSubjectByDateInst($date,$inst);
    }
    else
    {
        return response()->json([
            "status"          =>  "failure",
            "message"         =>  "Unauthorized User...",
        ], 200);
    }
 }

 public function getStudBySubject($id,Admin1 $a1)
 {
  if(Auth::user())
  {
    return $a1->getStudentsBySubject($id);
  }
  else
  {
      return response()->json([
          "status"          =>  "failure",
          "message"         =>  "Unauthorized User...",
      ], 200);
  }
 }

 public function getCheckerBySubject($id,Admin1 $a1)
 {
  if(Auth::user())
  {
    return $a1->getCheckersBySubject($id);
  }
  else
  {
      return response()->json([
          "status"          =>  "failure",
          "message"         =>  "Unauthorized User...",
      ], 200);
  }
 }

 public function getSubjectByChecker($uid,Admin1 $a1)
 {
  if(Auth::user())
  {
    return $a1->getSubjectByChecker($uid);
  }
  else
  {
      return response()->json([
          "status"          =>  "failure",
          "message"         =>  "Unauthorized User...",
      ], 200);
  }
 }

}
