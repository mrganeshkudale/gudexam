<?php

namespace App\Admin;

use App\Models\User;
use App\Http\Resources\ProctorStudentWarningCollection;
use App\Models\ProctorStudentWarningMaster;
use App\Http\Resources\ExamCollection;
use App\Http\Resources\CheckersCollection;
use App\Http\Resources\ProctorsCollection;
use App\Http\Resources\CheckerStudentCollection;
use App\Http\Resources\ProctorStudentCollection;
use App\Http\Resources\ProctorStudentExtendedCollection;
use App\Http\Resources\CheckerSubjectCollection;
use App\Http\Resources\ProctorSubjectCollection;
use App\Http\Resources\PaperCollection;
use App\Http\Resources\ProctorSnapCollection;
use App\Http\Resources\ProctorSnapResource;
use App\Http\Resources\AnswerCollection;
use App\Http\Resources\QuestionCollection;
use App\Models\CandTest;
use App\Models\LoginLink;
use App\Http\Resources\ProctorSubjectStudCountCollection;
use App\Http\Resources\PaperSetterSubjectCollection;
use App\Models\CheckerSubjectMaster;
use App\Models\QuestionSet;
use App\Models\CandQuestion;
use App\Models\ProctorSnaps;
use App\Models\SubjectMaster;
use App\Models\StudentCheckerAllocMaster;
use App\Models\StudentProctorAllocMaster;
use App\Models\ProctorSubjectMaster;
use App\Models\PaperSetterSubjectMaster;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Validator;
use Illuminate\Support\Facades\Auth;
use File;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;
use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class Admin1
{
  public function resetExam($request)
  {
    $stdid              = "'" . implode("','", $request->stdid) . "'";
    $paperId            = $request->paperId;
    $instId             = $request->instId;

    $rrrr = User::where('username', $instId)->where('role', 'EADMIN')->first();
    $instUid = $rrrr->uid;

    $r   = DB::select("SELECT GROUP_CONCAT(uid) as uid FROM users where username in($stdid) and inst_id='$instId' and role='STUDENT'");
    $uid = $r[0]->uid; // uid list of users

    $res = DB::select("select group_concat(id) as examId from cand_test where stdid in ($uid) and inst='$instId' and paper_id='$paperId'");
    $examId = $res[0]->examId; // exam id list of students

    $result = SubjectMaster::where('id', $paperId)->where('inst_uid', $instUid)->first();
    $static_assign = $result->static_assign;
    if ($examId) {
      DB::beginTransaction();
      try {
        //-------Save questions in backup table---------------------------
        $result = DB::statement("insert into cand_questions_reset select * from cand_questions where exam_id in($examId)");
        $result = DB::statement("delete from cand_questions where exam_id in($examId)");

        $result1 = DB::statement("insert into cand_questions_copy_reset select * from cand_questions_copy where exam_id in($examId)");
        $result1 = DB::statement("delete from cand_questions_copy where exam_id in($examId)");
        //----------------------------------------------------------------

        //-------Save exam in backup table--------------------------------
        $result2 = DB::statement("insert into cand_test_reset select * from cand_test where id in($examId)");
        if ($static_assign != 1) {
          $result2 = DB::statement("update cand_test set starttime=null,endtime=null,cqnid=null,wqnid=null,uqnid=null,status=null,entry_on=null,end_on=null,end_by=null,examip=null,continueexam=0,marksobt=null where id in($examId)");
        } else {
          $result2 = DB::statement("delete from cand_test where id in($examId)");
        }
        //----------------------------------------------------------------

        //-------Save Exam Session in backup table------------------------
        $result3 = DB::statement("insert into exam_session_reset select * from exam_session where exam_id in($examId)");
        $result3 = DB::statement("delete from exam_session where exam_id in($examId)");
        //----------------------------------------------------------------

        //-------Save Proctor Snaps in backup table------------------------
        $result4 = DB::statement("insert into proctor_snaps_reset select * from proctor_snaps where examid in($examId)");
        $result4 = DB::statement("delete from proctor_snaps where examid in($examId)");

        $result5 = DB::statement("insert into proctor_snap_details_reset select * from proctor_snap_details where examid in($examId)");
        $result5 = DB::statement("delete  from proctor_snap_details where examid in($examId)");
        //----------------------------------------------------------------
        //-----------------------------Delete from Proctor Allocation-----------------------------

        $r1   = DB::select("Delete FROM student_proctor_alloc_master where studid in($uid) and instId='$instId' and  paperId='$paperId'");
        //----------------------------------------------------------------------------------------

        DB::commit();
        return response()->json([
          'status'     => 'success',
        ], 200);
      } catch (\Exception $e) {
        DB::rollback();
        return response()->json([
          'status'     => 'failure',
        ], 400);
      }
    } else {
      DB::rollback();
      return response()->json([
        'status'     => 'failure',
      ], 400);
    }
  }

  public function loginLink($students, $request)
  {
    $students           = explode(',', $students);
    $paperId            = $request->paperId;
    $instId             = $request->instId;
    $instUid            = $request->instUid;
    $missedArray        = array();
    $uid                = null;
    $pass               = null;

    $results            = User::whereIn('username', $students)->where('inst_id', 'like', $instId)->where('role', 'STUDENT')->get();

    foreach ($results as $student) {
      $uid      = $student->uid;
      $pass     = $student->origpass;

      $std      = urlencode(base64_encode($student->username));
      $pass     = urlencode(base64_encode($pass));

      $inst     = urlencode(base64_encode($instId));
      $url      = Config::get('constants.PROJURL') . '/linkLogin/' . $std . '/' . $pass . '/' . $inst;

      try {
        $result1 = LoginLink::create([
          'stduid'  => $uid,
          'inst_id' => $inst,
          'link'    => $url
        ]);
      } catch (\Exception $e) {
        array_push($missedArray, $student);
      }
    }

    return response()->json([
      'status'     => 'success',
      'missed'    => $missedArray,
    ], 200);
  }

  public function searchQuestionByQnid($searchString)
  {
    //---------First Search by Qnid-----------------------------------
    $result = QuestionSet::where('qnid', $searchString)->paginate(50);
    if ($result->count() > 0) {
      return new QuestionCollection($result);
    } else {
      return response()->json([
        'status'     => 'failure',
      ], 400);
    }
    //----------------------------------------------------------------
  }

  public function getUser($username, $instId)
  {
    $result = User::where('username', $username)->where('inst_id', $instId)->paginate(50);
    if ($result->count() > 0) {
      return response($result, 200);
    } else {
      return response()->json([
        'status'     => 'failure',
      ], 400);
    }
  }

  public function storeSubjectiveQuestion($request)
  {
    $subjectId              = $request->subjectId;
    $subjectCode            = SubjectMaster::find($subjectId)->paper_code;
    $topic                  = $request->topic;
    $subtopic               = ($request->subtopic == null) ? 0 : $request->subtopic;
    $difficultyLevel        = $request->difficultyLevel;
    $marks                  = $request->marks;
    $questType              = $request->questType;
    $question               = $request->question;
    $modelAnswer            = $request->modelAnswer;
    $modelAnswerImage       = $request->modelAnswerImage;
    $allowImageUpload       = $request->allowImageUpload;
    $setter                 = $request->setter;

    $current_time           = Carbon::now();

    $qfilepath              = null;
    $ansfilepath            = null;

    //------------------------Upload Question Image and  Model Answer Image if any----------------
    $new_name = null;
    if ($request->qufig) {
      $part = rand(100000, 999999);
      $validation = Validator::make($request->all(), ['qufig' => 'required|mimes:jpeg,jpg']);
      $path = $request->file('qufig')->getRealPath();

      if ($validation->passes()) {
        $image = $request->file('qufig');
        $new_name = 'Q_' . $subjectId . '_' . $part . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('data/files'), $new_name);
        $path = public_path('data/files') . '/' . $new_name;
        $qfilepath = $new_name;
      } else {
        return response()->json([
          "status"            => "failure",
          "message"           => 'Question Image must be jpeg or jpg',
        ], 400);
      }
    }
    $new_name = null;
    if ($request->modelAnswerImage) {
      $part = rand(100000, 999999);
      $validation = Validator::make($request->all(), ['modelAnswerImage' => 'required|mimes:jpeg,jpg']);
      $path = $request->file('modelAnswerImage')->getRealPath();

      if ($validation->passes()) {
        $image = $request->file('modelAnswerImage');
        $new_name = 'ModelAnswer_' . $subjectId . '_' . $part . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('data/files'), $new_name);
        $path = public_path('data/files') . '/' . $new_name;
        $ansfilepath = $new_name;
      } else {
        return response()->json([
          "status"            => "failure",
          "message"           => 'Model Answer Image must be jpeg or jpg',
        ], 400);
      }
    }
    //-------------------------------------------------------------------------------------------

    $result = DB::statement("insert into question_set (paper_uid,paper_id,question,topic,subtopic,qu_fig,modelAnswer,modelAnswerImage,marks,difficulty_level,quest_type,coption,allowImageUpload,created_at,updated_at,psetter) values ('$subjectId','$subjectCode','$question','$topic','$subtopic','$qfilepath','$modelAnswer','$ansfilepath','$marks','$difficultyLevel','S','-','$allowImageUpload','$current_time','$current_time','$setter')");

    if ($result) {
      return response()->json([
        "status"            => "success",
        "message"           => 'Question Inserted Successfully...',
      ], 200);
    } else {
      return response()->json([
        "status"            => "failure",
        "message"           => 'Problem Inserting Question...',
      ], 400);
    }
  }


  public function uploadSubjectiveQuestion($request)
  {
    $validator = Validator::make($request->all(), [
      'file'      => 'required|max:10240',
    ]);

    $extension = File::extension($request->file->getClientOriginalName());

    if ($validator->fails()) {
      return response()->json([
        "status"          => "failure",
        "message"         => "File for uploading is required with max file size 10 MB",
      ], 400);
    }


    if ($extension == "xlsx") {
      $fileName           = 'SubjectiveQuestionBank.xlsx';

      $request->file->move(public_path('assets/tempfiles/'), $fileName);
      $reader             = IOFactory::createReader("Xlsx");
      $spreadsheet        = $reader->load(public_path('assets/tempfiles/') . $fileName);
      $current_time       = Carbon::now();
      $highestRow         = $spreadsheet->getActiveSheet()->getHighestRow();
      $values             = [];
      $new_name           = '';
      $new_name1          = '';
      $psetter            = $request->psetter;

      for ($i = 2; $i <= $highestRow; $i++) {
        $correctAnswer    = '';
        $instId           = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(1, $i)->getValue();
        $paper_code       = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(2, $i)->getValue();
        $question         = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(3, $i)->getValue();
        $topic            = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(4, $i)->getValue();
        $subtopic         = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(5, $i)->getValue();
        $modelAnswer      = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(6, $i)->getValue();
        $marks            = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(7, $i)->getValue();
        $diff_level       = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(8, $i)->getValue();
        $allowImageUpload = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(9, $i)->getValue();

        $questionImage    = trim($spreadsheet->getActiveSheet()->getCellByColumnAndRow(10, $i)->getValue());
        $answerImage      = trim($spreadsheet->getActiveSheet()->getCellByColumnAndRow(11, $i)->getValue());

        if($subtopic == '')
        {
          $subtopic = '0';
        }
        //------------------------Upload Question Image and  Model Answer Image if any----------------
        $new_name = null;
        if ($questionImage != '') 
        {
          $part     = rand(100000, 999999);
          $new_name = 'Q_' . $paper_code . '_' . $part . '.jpg';
          file_put_contents('files/' . $new_name, file_get_contents($questionImage));
        }

        $new_name1 = null;
        if ($answerImage != '') {
          $part     = rand(100000, 999999);
          $new_name1 = 'Q_' . $paper_code . '_' . $part . '.jpg';
          file_put_contents('files/' . $new_name1, file_get_contents($answerImage));
        }
        //-------------------------------------------------------------------------------------------

        $inst_uid         = User::where('username', $instId)->first()->uid;

        $paper_id         = SubjectMaster::where('paper_code', $paper_code)->where('inst_uid', $inst_uid)->first()->id;

        $values = [
          'paper_uid'       => $paper_id,
          'paper_id'        => $paper_code,
          'question'        => $question,
          'topic'           => $topic,
          'subtopic'        => $subtopic,
          'coption'         => '-',
          'modelAnswer'     => $modelAnswer,
          'quest_type'      => 'S',
          'allowImageUpload' => $allowImageUpload,
          'marks'           => $marks,
          'difficulty_level' => $diff_level,
          'qu_fig'          => $new_name,
          'modelAnswerImage' => $new_name1,
          'psetter'         => $psetter
        ];

        $result         = QuestionSet::create($values);

        if (!$result) {
          return response()->json([
            "status"            => "failure",
            "message"           => 'Problem Inserting Question on Row ' . $i . '...',
          ], 400);
        }
      }
      return response()->json([
        "status"            => "success",
        "message"           => 'Questions uploaded successfully... ',
      ], 200);
    }
  }

  public function getAllQuestionsFromArray($subArray)
  {
    $subArray = rtrim($subArray, ",");
    $array = explode(",", $subArray);
    $result = null;
    if(Auth::user()->role == 'PAPERSETTER')
    {
      $r = User::where('inst_id',Auth::user()->inst_id)->where('role','EADMIN')->first();
      $rrr = PaperSetterSubjectMaster::where('uid',Auth::user()->uid)->where('instId',$r->uid)->whereIn('paperId',$array)->where('type','PS')->first();

      if($rrr)
      {
        $result = QuestionSet::whereIn('paper_uid', $array)->where('quest_type', 'S')->where('psetter',Auth::user()->uid)->orderBy('created_at')->paginate(50);
      }
      else
      {
        $rrr = PaperSetterSubjectMaster::where('uid',Auth::user()->uid)->where('instId',$r->uid)->whereIn('paperId',$array)->whereIn('type',['PM','PSM'])->first();
        if($rrr)
        {
          $result = QuestionSet::whereIn('paper_uid', $array)->where('quest_type', 'S')->orderBy('created_at')->paginate(50);
        }
        else
        {
          $result = [];
        }
      }
    }
    else
    {
      $result = QuestionSet::whereIn('paper_uid', $array)->where('quest_type', 'S')->orderBy('created_at')->paginate(50);
    }

    if ($result) {
      return new QuestionCollection($result);
    } else {
      return response()->json([
        "status"            => "failure",
        "message"           => 'Unable to fetch Questions...',
      ], 400);
    }
  }

  public function updateSubjectiveQuestion($qnid, $request)
  {
    $subjectId              = $request->subjectId;
    $subjectCode            = SubjectMaster::find($subjectId)->paper_code;
    $topic                  = $request->topic;
    $subtopic               = ($request->subtopic == null) ? 0 : $request->subtopic;
    $difficultyLevel        = $request->difficultyLevel;
    $marks                  = $request->marks;
    $questType              = $request->questType;
    $question               = $request->question;
    $qufig                  = $request->qufig;
    $allowImageUpload       = $request->allowImageUpload;
    $modelAnswer            = $request->modelAnswer;
    $imgChange              = explode(',', $request->imgChange);
    $values                 = [];
    $origQustion            = QuestionSet::find($qnid);

    $qfilepath              = '';
    $modelAnswerImagePath   = '';
    $moderator = $request->moderator;



    $new_name = '';
    if (in_array("qufig", $imgChange)) {
      $validation = Validator::make($request->all(), ['qufig' => 'required|mimes:jpeg,jpg']);
      $path = $request->file('qufig')->getRealPath();

      if ($validation->passes()) {
        $image = $request->file('qufig');
        $new_name = $origQustion->qu_fig;

        if ($new_name == '') {
          $part = rand(100000, 999999);
          $new_name = 'Q_' . $subjectId . '_' . $part . '.' . $image->getClientOriginalExtension();
        }

        $image->move(public_path('data/files'), $new_name);
        $path = public_path('data/files') . '/' . $new_name;
        $qfilepath = $new_name;

        $request->qufig = $qfilepath;
      } else {
        return response()->json([
          "status"            => "failure",
          "message"           => 'Question Image must be jpeg or jpg',
        ], 400);
      }
    } else {
      $request->qufig = $origQustion->qu_fig;
    }
    $new_name1 = '';
    if (in_array("modelAnswerImage", $imgChange)) {
      $validation = Validator::make($request->all(), ['modelAnswerImage' => 'required|mimes:jpeg,jpg']);
      $path = $request->file('modelAnswerImage')->getRealPath();

      if ($validation->passes()) {
        $image = $request->file('modelAnswerImage');
        $new_name1 = $origQustion->modelAnswerImage;

        if ($new_name1 == '') {
          $part = rand(100000, 999999);
          $new_name1 = 'ModelAnswer_' . $subjectId . '_' . $part . '.' . $image->getClientOriginalExtension();
        }

        $image->move(public_path('data/files'), $new_name1);
        $path = public_path('data/files') . '/' . $new_name1;
        $modelAnswerImagePath = $new_name1;

        $request->modelAnswerImage = $modelAnswerImagePath;
      } else {
        return response()->json([
          "status"            => "failure",
          "message"           => 'Option A Image must be jpeg or jpg',
        ], 400);
      }
    } else {
      $request->modelAnswerImage = $origQustion->modelAnswerImage;
    }

    $values = [
      'paper_uid'       => $subjectId,
      'paper_id'        => $subjectCode,
      'question'        => $question,
      'topic'           => $topic,
      'subtopic'        => $subtopic,
      'coption'         => '-',
      'modelAnswer'     => $modelAnswer,
      'quest_type'      => 'S',
      'allowImageUpload' => $allowImageUpload,
      'marks'           => $marks,
      'difficulty_level' => $difficultyLevel,
      'moderator'       => $moderator,
      'updated_at'      => Carbon::now(),
    ];

    if (in_array("qufig", $imgChange)) {
      $values['qu_fig']   = $qfilepath;
    }
    if (in_array("modelAnswerImage", $imgChange)) {
      $values['modelAnswerImage']       = $modelAnswerImagePath;
    }

    $result         = QuestionSet::find($qnid)->update($values);

    if ($result) {
      return response()->json([
        "status"            => "success",
        "message"           => 'Question Moderated Successfully...',
      ], 200);
    } else {
      return response()->json([
        "status"            => "failure",
        "message"           => 'Problem Moderating Question...',
      ], 400);
    }
  }

  public function getAllQuestionsByPaperCode($paper_id)
  {
    $result = QuestionSet::where('paper_uid', $paper_id)->get();
    if ($result) {
      return response()->json([
        "status"            => "success",
        "data"              => new QuestionCollection($result),
      ], 200);
    } else {
      return response()->json([
        "status"            => "success",
        "message"           => "Data not Found...",
      ], 400);
    }
  }

  public function storeCheckerUsers($request)
  {
    $username       = $request->username;
    $name           = $request->name;
    $mobile         = $request->mobile;
    $email          = $request->email;
    $inst           = $request->instId;
    $rrr = User::where('username', $inst)->where('role', 'EADMIN')->first();
    $college_name   = $rrr->college_name;
    $password       = Hash::make($request->password);
    
    try {
      $result = User::create([
        'username' => $username,
        'name'     => $name,
        'mobile'   => $mobile,
        'email'    => $email,
        'inst_id'  => $inst,
        'password' => $password,
        'role'     => 'CHECKER',
        'status'   => 'ON',
        'regi_type' => 'CHECKER',
        'verified' => 'verified',
        'college_name' => $college_name,
        'origpass' => $request->password,
      ]);

      if ($result) {
        return response()->json([
          "status"  => "success"
        ], 200);
      }
    } catch (\Exception $e) {
      return response()->json([
        "status"  => "failure"
      ], 400);
    }
  }

  public function deleteCheckerSubjects($id)
  {
    $result = CheckerSubjectMaster::where('uid', $id)->delete();
  }

  public function deleteProctorSubjects($id)
  {
    $result = ProctorSubjectMaster::where('uid', $id)->delete();
  }

  public function deleteSetterSubjects($id)
  {
    $result = PaperSetterSubjectMaster::where('uid', $id)->delete();
  }

  public function uploadCheckers($request)
  {
    $validator = Validator::make($request->all(), [
      'file'      => 'required|max:1024',
    ]);

    $extension = File::extension($request->file->getClientOriginalName());

    if ($validator->fails()) {
      return response()->json([
        "status"          => "failure",
        "message"         => "File for uploading is required with max file size 1 MB",
      ], 400);
    }

    if ($extension == "xlsx") 
    {
      $fileName           = 'checkers.xlsx';
      $request->file->move(public_path('assets/tempfiles/'), $fileName);
      $reader             = IOFactory::createReader("Xlsx");
      $spreadsheet        = $reader->load(public_path('assets/tempfiles/') . $fileName);
      $current_time       = Carbon::now();
      $highestRow         = $spreadsheet->getActiveSheet()->getHighestRow();

      for ($i = 2; $i <= $highestRow; $i++) 
      {
        $instId         = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(1, $i)->getValue();
        $name           = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(2, $i)->getValue();
        $mobile         = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(3, $i)->getValue();
        $email          = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(4, $i)->getValue();
        $origpass       = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(5, $i)->getValue();
  
       
          try {
            $result = User::create([
              'username' => $email,
              'name'     => $name,
              'mobile'   => $mobile,
              'email'    => $email,
              'inst_id'  => $instId,
              'password' => Hash::make($origpass),
              'role'     => 'CHECKER',
              'status'   => 'ON',
              'regi_type' => 'CHECKER',
              'verified' => 'verified',
              'origpass' => $origpass,
            ]);
          } 
          catch (\Exception $e) {
            return response()->json([
              "status"  => "failure",
              "message" => "Problem Uploading Data on Row " . $i . "..."
            ], 400);
          }
      }

      return response()->json([
        "status"  => "success",
        "message" => "Data uploaded Successfully..."
      ], 200);
    } else {
      return response()->json([
        "status"          => "failure",
        "message"         => "File must be .xlsx only with maximum 1 MB  of Size.",
      ], 400);
    }
  }

  public function getStudentsBySubject($id,$request)
  {
    $type = $request->type;
    $result = DB::select("SELECT * FROM cand_test WHERE paper_id='$id' and stdid not in(select distinct studid from student_checker_alloc_master where paperId='$id' and type='$type')");

    if ($result) {
      return json_encode([
        'status' => 'success',
        'data' => new ExamCollection($result)
      ], 200);
    } else {
      return json_encode([
        'status' => 'failure',
      ], 400);
    }
  }

  public function getStudentsBySubject1($id)
  {
    $result = DB::select("SELECT * FROM cand_test WHERE paper_id='$id' and stdid not in(select distinct studid from student_proctor_alloc_master where paperId='$id')");

    if ($result) {
      return json_encode([
        'status' => 'success',
        'data' => new ExamCollection($result)
      ], 200);
    } else {
      return json_encode([
        'status' => 'failure',
      ], 400);
    }
  }

  public function getCheckersBySubject($id,$request)
  {
    $result = CheckerSubjectMaster::where('paperId',$id)->where('type',$request->type)->get();
    if ($result) {
      return json_encode([
        'status' => 'success',
        'data' => new CheckersCollection($result)
      ], 200);
    } else {
      return json_encode([
        'status' => 'failure',
      ], 400);
    }
  }

  public function allocateStudentToCheckers($request)
  {
    $students = $request->students;
    $checkers = $request->checkers;
    $paperId  = $request->paperId;
    $instId   = $request->inst;
    $type = $request->type;

    $query    = [];
    $now      = Carbon::now();

    if (sizeof($students) > sizeof($checkers)) 
    {
      $k = 0;
      for ($j = 1; $j <= ceil(sizeof($students) / sizeof($checkers)); $j++) 
      {
        for ($i = 0; $i < sizeof($checkers); $i++) 
        {
          if (sizeof($students) == $k) 
          {
            break;
          }
          array_push($query, ['instId' => $instId, 'checkerid' => $checkers[$i], 'paperId' => $paperId, 'studid' => $students[$k], 'type' => $type,'created_at' => $now]);

          $k++;
        }
        $i = 0;
      }
    } 
    else if (sizeof($students) < sizeof($checkers)) 
    {
      for ($i = 0; $i < sizeof($students); $i++) 
      {
        array_push($query, ['instId' => $instId, 'checkerid' => $checkers[$i], 'paperId' => $paperId, 'studid' => $students[$i], 'type' => $type, 'created_at' => $now]);
      }
    } 
    else if (sizeof($students) == sizeof($checkers)) 
    {
      for ($i = 0; $i < sizeof($students); $i++) 
      {
        array_push($query, ['instId' => $instId, 'checkerid' => $checkers[$i], 'paperId' => $paperId, 'studid' => $students[$i], 'type' => $type, 'created_at' => $now]);
      }
    }

    $result = DB::table('student_checker_alloc_master')->insert($query);

    if ($result) {
      return json_encode([
        'status' => 'success',
        'message' => 'Student Checkers Allocation Successful...'
      ], 200);
    } else {
      return json_encode([
        'status' => 'failure'
      ], 400);
    }
  }

  public function getStudentToCheckers($request)
  {
    $paperId = $request->paperId;
    $instId = $request->inst;

    if ($paperId == 'all') {
      $result = StudentCheckerAllocMaster::where('instId', $instId)->paginate(50);
    } else {
      $result = StudentCheckerAllocMaster::where('instId', $instId)->where('paperId', $paperId)->paginate(50);
    }

    if ($result) {
      return new CheckerStudentCollection($result);
    } else {
      return json_encode([
        'status' => 'failure'
      ], 200);
    }
  }

  public function deleteStudentToCheckers($id)
  {
    $result = StudentCheckerAllocMaster::find($id)->delete();

    return json_encode([
      'status' => 'success',
      'message' => 'Record Deleted Successfully...'
    ], 200);
  }

  public function deleteCheckerAllocationByCheckerId($checkerId)
  {
    $result = StudentCheckerAllocMaster::where('checkerid', $checkerId)->delete();
  }

  public function deleteProctorAllocationByProctorId($checkerId)
  {
    $result = StudentProctorAllocMaster::where('proctorid', $checkerId)->delete();
  }

  public function searchCheckerAllocation($request)
  {
    $username   = $request->username;
    if (Auth::user()->role === 'EADMIN') {
      $inst       = Auth::user()->username;
    }

    $result = User::where('username', $username)->where('inst_id', $inst)->first();
    if ($result) {
      $id = $result->uid;
      $res = StudentCheckerAllocMaster::where('checkerid', $id);
      $res1 = StudentCheckerAllocMaster::where('studid', $id)->union($res)->get();

      if ($res1->count() > 0) {
        return new CheckerStudentCollection($res1);
      } else {
        return json_encode([
          'status' => 'failure',
          'message' => 'User Not Found...',
          'data'  => []
        ], 400);
      }
    } else {
      return json_encode([
        'status' => 'failure',
        'message' => 'User Not Found...',
        'data'  => []
      ], 400);
    }
  }

  public function deleteBulkCheckerAllocation($request)
  {
    $paperId      = $request->paperId;
    $students     = $request->students;
    $instId       = $request->inst;

    $result1      = User::select('uid')->whereIn('username', $students)->where('inst_id', $instId)->get();
    $studentList  = [];

    foreach ($result1 as $res) {
      array_push($studentList, $res->uid);
    }

    $result = StudentCheckerAllocMaster::where('instId', $instId)->where('paperId', $paperId)->whereIn('studid', $studentList)->delete();

    return json_encode([
      'status' => 'success',
      'message' => 'Record Deleted Successfully...'
    ], 200);
  }

  public function getSubjectByChecker($uid)
  {
    $result = CheckerSubjectMaster::where('uid', $uid)->get();

    if ($result) {
      return json_encode([
        'status'  => 'success',
        'data'    => new CheckerSubjectCollection($result)
      ], 200);
    }
  }

  public function getCheckerStudExams($request)
  {
    $paperId      = $request->paperId;
    $checkeruid   = $request->checkeruid;

    $results = StudentCheckerAllocMaster::where('checkerId', $checkeruid)->where('paperId', $paperId)->where('type','QPC')->get();
    $studList = [];

    if ($results && $results->count() > 0) {
      foreach ($results as $result) {
        array_push($studList, $result->studid);
      }

      $res = CandTest::where('paper_id', $paperId)->whereIn('stdid', $studList)->get();

      if ($res->count() > 0) {
        return json_encode([
          'status'  => 'success',
          'data'    => new ExamCollection($res)
        ], 200);
      } else {
        return json_encode([
          'status'  => 'failure',
          'message' => 'No Exam Data found...'
        ], 400);
      }
    } else {
      return json_encode([
        'status'  => 'failure',
        'message' => 'No Exam Data found...'
      ], 400);
    }
  }

  public function updateStudExamMarks($id, $marks,$request)
  {
    $result = CandQuestion::find($id);
    $checkerType = $request->checkerType;
    if($checkerType == 'QPC')
    {
      $result->obtmarks = $marks;
    }
    else if($checkerType == 'QPM')
    {
      $result->obtmarks1 = $marks;
    }

    $result->save();

    return json_encode([
      'status'  => 'success',
      'message' => 'Marks Saved Successfully...'
    ], 200);
  }

  public function finishExamChecking($examid, $request)
  {
    $totalScore = $request->score;
    $checkerType = $request->checkerType;

    $result = CandTest::find($examid);
    if($checkerType == 'QPC')
    {
      $result->paper_checking = '1';
      $result->result = $totalScore;
    }
    else if($checkerType == 'QPM')
    {
      $result->paper_moderation = '1';
      $result->result1 = $totalScore;
    }

    $result->save();

    return json_encode([
      'status'  => 'success',
    ], 200);
  }

  public function deleteStudentSubjectMapping($id)
  {
    $result = CandTest::where('stdid', $id)->delete();
  }

  public function deleteStudentCheckerMapping($id)
  {
    $result = StudentCheckerAllocMaster::where('studid', $id)->delete();
  }

  public function deleteStudentProctorMapping($id)
  {
    $result = StudentProctorAllocMaster::where('studid', $id)->delete();
  }

  public function updateChecker($id, $request)
  {
    $username       = $request->username;
    $name           = $request->name;
    $mobile         = $request->mobile;
    $email          = $request->email;
    $inst           = $request->instId;
    $password       = Hash::make($request->password);

    try 
    {
      $result = User::find($id);

      if ($result) {
        $result->username = $username;
        $result->name     = $name;
        $result->mobile   = $mobile;
        $result->email    = $email;
        $result->inst_id  = $inst;
        $result->password = $password;
        $result->origpass = $request->password;

        $result->save();
      }

      if ($result) 
      {
        return response()->json([
          "status"  => "success"
        ], 200);
      }
    } 
    catch (\Exception $e) 
    {
      return response()->json([
        "status"  => "failure"
      ], 400);
    }
  }

  public function getSubjectiveAnswers($id)
  {
    $answers = CandQuestion::where('exam_id', $id)->where('questMode', 'S')->get();
    if ($answers) {
      return json_encode([
        'status' => 'success',
        'data' => new AnswerCollection($answers)
      ], 200);
    } else {
      return json_encode([
        'status' => 'failure'
      ], 400);
    }
  }

  public function storeProctorUsers($request)
  {
    $username       = $request->username;
    $name           = $request->name;
    $mobile         = $request->mobile;
    $email          = $request->email;
    $inst           = $request->instId;
    $rrr = User::where('username', $inst)->where('role', 'EADMIN')->first();
    $college_name   = $rrr->college_name;
    $password       = Hash::make($request->password);
    $subjects       = $request->subjects;

    DB::beginTransaction();
    try {
      $result = User::create([
        'username' => $username,
        'name'     => $name,
        'mobile'   => $mobile,
        'email'    => $email,
        'inst_id'  => $inst,
        'password' => $password,
        'role'     => 'PROCTOR',
        'status'   => 'ON',
        'college_name'   => $college_name,
        'regi_type' => 'PROCTOR',
        'verified' => 'verified',
        'origpass' => $request->password,
      ]);

      if ($result) {
        foreach ($subjects as $subject) {
          try {
            $result1 = ProctorSubjectMaster::create([
              'uid' => $result->uid,
              'paperId' => $subject
            ]);
          } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
              "status"  => "failure"
            ], 400);
          }
        }
        DB::commit();
        return response()->json([
          "status"  => "success"
        ], 200);
      }
    } catch (\Exception $e) {
      DB::rollback();
      return response()->json([
        "status"  => "failure"
      ], 400);
    }
  }

  public function uploadProctors($request)
  {
    $validator = Validator::make($request->all(), [
      'file'      => 'required|max:1024',
    ]);

    $extension = File::extension($request->file->getClientOriginalName());

    if ($validator->fails()) {
      return response()->json([
        "status"          => "failure",
        "message"         => "File for uploading is required with max file size 1 MB",
      ], 400);
    }

    if ($extension == "xlsx") {
      $fileName           = 'proctors.xlsx';
      $request->file->move(public_path('assets/tempfiles/'), $fileName);
      $reader             = IOFactory::createReader("Xlsx");
      $spreadsheet        = $reader->load(public_path('assets/tempfiles/') . $fileName);
      $current_time       = Carbon::now();
      $highestRow         = $spreadsheet->getActiveSheet()->getHighestRow();

      for ($i = 2; $i <= $highestRow; $i++) {
        $paperIdList    = [];
        $checkerType    = '';
        $instId         = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(1, $i)->getValue();
        $instUid        = User::where('username', $instId)->where('role', 'EADMIN')->first()->uid;
        $name           = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(2, $i)->getValue();
        $mobile         = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(3, $i)->getValue();
        $email          = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(4, $i)->getValue();

        $origpass       = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(5, $i)->getValue();
        $list           = explode(',', $spreadsheet->getActiveSheet()->getCellByColumnAndRow(6, $i)->getValue());

        if (sizeof($list) > 0) {
          DB::beginTransaction();
          try {
            $result = User::create([
              'username' => $email,
              'name'     => $name,
              'mobile'   => $mobile,
              'email'    => $email,
              'inst_id'  => $instId,
              'password' => Hash::make($origpass),
              'role'     => 'PROCTOR',
              'status'   => 'ON',
              'regi_type' => 'PROCTOR',
              'verified' => 'verified',
              'origpass' => $origpass,
            ]);

            $subjects = SubjectMaster::whereIn('paper_code', $list)->where('inst_uid', $instUid)->get();

            foreach ($subjects as $subject) {
              array_push($paperIdList, $subject->id);
              $result1 = ProctorSubjectMaster::create([
                'uid' => $result->uid,
                'paperId' => $subject->id
              ]);
            }
            DB::commit();
          } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
              "status"  => "failure",
              "message" => "Problem Uploading Data on Row " . $i . "..."
            ], 400);
          }
        }
      }

      return response()->json([
        "status"  => "success",
        "message" => "Data uploaded Successfully..."
      ], 200);
    } else {
      return response()->json([
        "status"          => "failure",
        "message"         => "File must be .xlsx only with maximum 1 MB  of Size.",
      ], 400);
    }
  }

  public function updatePaperSetter($id,$request)
  {
    $username       = $request->username;
    $name           = $request->name;
    $mobile         = $request->mobile;
    $email          = $request->email;
    $inst           = $request->instId;
    $password       = Hash::make($request->password);
    
    try {
      $result = User::find($id);

      if ($result) {
        $result->username = $username;
        $result->name     = $name;
        $result->mobile   = $mobile;
        $result->email    = $email;
        $result->inst_id  = $inst;
        $result->password = $password;
        $result->origpass = $request->password;

        $result->save();
      }
        return response()->json([
          "status"  => "success"
        ], 200);
    } catch (\Exception $e) {
      return response()->json([
        "status"  => "failure"
      ], 400);
    }
  }

  public function updateProctor($id, $request)
  {
    $username       = $request->username;
    $name           = $request->name;
    $mobile         = $request->mobile;
    $email          = $request->email;
    $inst           = $request->instId;
    $password       = Hash::make($request->password);
    $subjects       = $request->subjects;

    DB::beginTransaction();
    try {
      $result = User::find($id);

      if ($result) {
        $result->username = $username;
        $result->name     = $name;
        $result->mobile   = $mobile;
        $result->email    = $email;
        $result->inst_id  = $inst;
        $result->password = $password;
        $result->origpass = $request->password;

        $result->save();
      }

      if ($result) {
        $res = ProctorSubjectMaster::where('uid', $id)->delete();

        foreach ($subjects as $subject) {
          try {
            $result1 = ProctorSubjectMaster::create([
              'uid' => $result->uid,
              'paperId' => $subject
            ]);
          } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
              "status"  => "failure"
            ], 400);
          }
        }
        DB::commit();
        return response()->json([
          "status"  => "success"
        ], 200);
      }
    } catch (\Exception $e) {
      DB::rollback();
      return response()->json([
        "status"  => "failure"
      ], 400);
    }
  }


  public function getProctorBySubject($id)
  {
    $result = SubjectMaster::find($id)->proctors;
    if ($result) {
      return json_encode([
        'status' => 'success',
        'data' => new ProctorsCollection($result)
      ], 200);
    } else {
      return json_encode([
        'status' => 'failure',
      ], 400);
    }
  }

  public function allocateStudentToProctor($request)
  {
    $students = $request->students;
    $proctors = $request->proctors;
    $paperId  = $request->paperId;
    $instId   = $request->inst;


    $query    = [];
    $now      = Carbon::now();

    if (sizeof($students) > sizeof($proctors)) 
    {
      $k = 0;
      if(ceil(sizeof($students) / sizeof($proctors)) > 30)
      {
        return json_encode([
          'status' => 'failure'
        ], 400);
      }

      for ($j = 1; $j <= ceil(sizeof($students) / sizeof($proctors)); $j++) 
      {
        for ($i = 0; $i < sizeof($proctors); $i++) 
        {
          if ((sizeof($students) == $k)) {
            break;
          }
          array_push($query, ['instId' => $instId, 'proctorid' => $proctors[$i], 'paperId' => $paperId, 'studid' => $students[$k], 'created_at' => $now]);

          $k++;
        }
        $i = 0;
      }
    } 
    else if (sizeof($students) < sizeof($proctors)) 
    {
      for ($i = 0; $i < sizeof($students); $i++) {
        array_push($query, ['instId' => $instId, 'proctorid' => $proctors[$i], 'paperId' => $paperId, 'studid' => $students[$i], 'created_at' => $now]);
      }
    } 
    else if (sizeof($students) == sizeof($proctors)) 
    {
      for ($i = 0; $i < sizeof($students); $i++) {
        array_push($query, ['instId' => $instId, 'proctorid' => $proctors[$i], 'paperId' => $paperId, 'studid' => $students[$i], 'created_at' => $now]);
      }
    }

    $result = DB::table('student_proctor_alloc_master')->insert($query);

    if ($result) {
      return json_encode([
        'status' => 'success',
        'message' => 'Student Proctors Allocation Successful...'
      ], 200);
    } else {
      return json_encode([
        'status' => 'failure'
      ], 400);
    }
  }

  public function getStudentToProctors($request)
  {
    $paperId = $request->paperId;
    $instId = $request->inst;

    if ($paperId == 'all') {
      $result = StudentProctorAllocMaster::where('instId', $instId)->paginate(50);
    } else {
      $result = StudentProctorAllocMaster::where('instId', $instId)->where('paperId', $paperId)->paginate(50);
    }

    if ($result) {
      return new ProctorStudentCollection($result);
    } else {
      return json_encode([
        'status' => 'failure'
      ], 200);
    }
  }

  public function deleteStudentToProctors($id)
  {
    try {
      $result = StudentProctorAllocMaster::find($id)->delete();
    } catch (\Exception $e) {
      return response()->json([
        "status"  => "failure"
      ], 400);
    }

    return json_encode([
      'status' => 'success',
      'message' => 'Record Deleted Successfully...'
    ], 200);
  }


  public function searchProctorAllocation($request)
  {
    $username   = $request->username;
    if (Auth::user()->role === 'EADMIN') {
      $inst       = Auth::user()->username;
    }

    $result = User::where('username', $username)->where('inst_id', $inst)->first();
    if ($result) {
      $id = $result->uid;
      $res = StudentProctorAllocMaster::where('proctorid', $id);
      $res1 = StudentProctorAllocMaster::where('studid', $id)->union($res)->get();

      if ($res1->count() > 0) {
        return new ProctorStudentCollection($res1);
      } else {
        return json_encode([
          'status' => 'failure',
          'message' => 'User Not Found...',
          'data'  => []
        ], 400);
      }
    } else {
      return json_encode([
        'status' => 'failure',
        'message' => 'User Not Found...',
        'data'  => []
      ], 400);
    }
  }

  public function deleteBulkProctorAllocation($request)
  {
    $paperId      = $request->paperId;
    $students     = $request->students;
    $instId       = $request->inst;

    $result1      = User::select('uid')->whereIn('username', $students)->where('inst_id', $instId)->get();
    $studentList  = [];

    foreach ($result1 as $res) {
      array_push($studentList, $res->uid);
    }

    $result = StudentProctorAllocMaster::where('instId', $instId)->where('paperId', $paperId)->whereIn('studid', $studentList)->delete();

    return json_encode([
      'status' => 'success',
      'message' => 'Record Deleted Successfully...'
    ], 200);
  }

  public function getSubjectByProctor($uid)
  {
    $result = ProctorSubjectMaster::where('uid', $uid)->get();

    if ($result) {
      return json_encode([
        'status'  => 'success',
        'data'    => new ProctorSubjectCollection($result)
      ], 200);
    }
  }

  public function getStudentToProctorsBySubject($request)
  {
    $paperId      = $request->paperId;
    $proctorUid   = $request->proctorUid;

    if (Auth::user()->role == 'PROCTOR') {
      $instId  = $request->instId;

      $res = StudentProctorAllocMaster::select("student_proctor_alloc_master.*", "cand_test.status as status", "cand_test.id as examid")
        ->join("cand_test", function ($join) {
          $join->on("cand_test.paper_id", "=", "student_proctor_alloc_master.paperId")
            ->on("cand_test.stdid", "=", "student_proctor_alloc_master.studid");
        })
        ->where('student_proctor_alloc_master.proctorid', $proctorUid)
        ->where('student_proctor_alloc_master.paperId', $paperId)
        ->where('student_proctor_alloc_master.instId', $instId)
        ->get();
    } else if (Auth::user()->role == 'EADMIN') {
      $instId  = Auth::user()->username;
      $res = StudentProctorAllocMaster::where('paperId', $paperId)->where('instId', $instId)->paginate(50);
    }

    if ($res) {
      if (Auth::user()->role == 'PROCTOR') {
        return json_encode([
          'status'  =>  'success',
          'data' =>  new ProctorStudentExtendedCollection($res)
        ], 200);
      } else {
        return  new ProctorStudentCollection($res);
      }
    } else {
      return json_encode([
        'status'  =>  'failure',
        'message' =>  'Problem Fetching Data from System...'
      ], 400);
    }
  }

  public function proctorLatestByEnrollno($enrollno, $paperId, $instId, $stdid, $examId)
  {
    $result2 = ProctorSnaps::where('examid', $examId)->orderBy('created_at', 'DESC')->first();
    if ($result2) {
      return json_encode([
        'status'  => 'success',
        'data'    => new ProctorSnapResource($result2),
      ], 200);
    } else {
      $result = CandTest::where('id', $examId)->first();
      return json_encode([
        'status'  => 'failure',
        'message' => 'No Proctoring Data found for this Candidate...',
        'examstatus' => $result->status
      ], 400);
    }
  }

  public function proctorByEnrollnoInstId($enrollno, $paperId, $instId)
  {
    $result = User::where('username', $enrollno)->where('inst_id', $instId)->first();
    if ($result) {
      $stdid = $result->uid;
      //----------Find Exam Id of this student---------------------------------------
      $result1 = CandTest::where('stdid', $stdid)->where('paper_id', $paperId)->where('inst', $instId)->first();
      //------------------------------------------------------------------------------
      if ($result1) {
        $examId = $result1->id;
        $result2 = ProctorSnaps::where('examid', $examId)->get();
        return json_encode([
          'status'  => 'success',
          'data'    => new ProctorSnapCollection($result2),
        ], 200);
      } else {
        return json_encode([
          'status'  => 'failure',
          'message' => 'No Proctoring Data found for this Candidate...',
        ], 400);
      }
    } else {
      return json_encode([
        'status'  => 'failure',
        'message' => 'Invalid User Data...'
      ], 400);
    }
  }

  public function getAllStudentsBySubject($paperId, $instId)
  {
    $result = DB::select("SELECT * FROM cand_test WHERE paper_id='$paperId' and inst='$instId'");

    if ($result) {
      return json_encode([
        'status' => 'success',
        'data' => new ExamCollection($result)
      ], 200);
    } else {
      return json_encode([
        'status' => 'failure',
      ], 400);
    }
  }

  public  function getSingleStudentToProctorsBySubject($request)
  {
    $paperId      = $request->paperId;
    $proctorUid   = $request->proctorUid;
    $studid       = $request->studid;

    if (Auth::user()->role == 'PROCTOR') {
      $instId  = $request->instId;

      $res = StudentProctorAllocMaster::select("student_proctor_alloc_master.*", "cand_test.status as status", "cand_test.id as examid")
        ->join("cand_test", function ($join) {
          $join->on("cand_test.paper_id", "=", "student_proctor_alloc_master.paperId")
            ->on("cand_test.stdid", "=", "student_proctor_alloc_master.studid");
        })
        ->where('student_proctor_alloc_master.proctorid', $proctorUid)
        ->where('student_proctor_alloc_master.paperId', $paperId)
        ->where('student_proctor_alloc_master.instId', $instId)
        ->get();
    } else if (Auth::user()->role == 'EADMIN') {
      $instId  = Auth::user()->username;
      $res = StudentProctorAllocMaster::where('paperId', $paperId)->where('instId', $instId)->paginate(50);
    }

    if ($res) {
      if (Auth::user()->role == 'PROCTOR') {
        return json_encode([
          'status'  =>  'success',
          'data' =>  new ProctorStudentExtendedCollection($res)
        ], 200);
      } else {
        return  new ProctorStudentCollection($res);
      }
    } else {
      return json_encode([
        'status'  =>  'failure',
        'message' =>  'Problem Fetching Data from System...'
      ], 400);
    }
  }

  public function sendProctorWarning($examid, $request)
  {

    $warning  = $request->warning;
    $to       = $request->to;
    $from     = $request->from;
    $paperId  = $request->paperId;
    $instId   = $request->instId;
    $warningNo = $request->warningNo;

    $current_time = Carbon::now();

    try {
      $res = ProctorStudentWarningMaster::create([
        'examId' => $examid,
        'paperId' => $paperId,
        'instId' => $instId,
        'proctor' => $from,
        'student' => $to,
        'warning' => $warning,
        'warningNo' => $warningNo,
        'created_at' => $current_time
      ]);

      $rrr = ProctorStudentWarningMaster::where('examId', $examid)->where('paperId', $paperId)->where('instId', $instId)->where('proctor', $from)->where('student', $to)->orderBy('created_at', 'DESC')->get();

      return response()->json([
        "status"          => "success",
        "message"         => "Warning Saved...",
        "data"            =>  $rrr,
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        "status"          => "failure",
        "message"         => "Problem Saving Warning",
      ], 400);
    }
  }

  public function uploadStudProctor($request)
  {
    $validator = Validator::make($request->all(), [
      'file'      => 'required|max:5024',
    ]);

    $extension = File::extension($request->file->getClientOriginalName());

    if ($validator->fails()) {
      return response()->json([
        "status"          => "failure",
        "message"         => "File for uploading is required with max file size 1 MB",
      ], 400);
    }


    if ($extension == "xlsx") {
      $fileName           = 'StudProctorAllocation.xlsx';

      $request->file->move(public_path('assets/tempfiles/'), $fileName);
      $reader             = IOFactory::createReader("Xlsx");
      $spreadsheet        = $reader->load(public_path('assets/tempfiles/') . $fileName);
      $current_time       = Carbon::now();
      $highestRow         = $spreadsheet->getActiveSheet()->getHighestRow();

      for ($i = 2; $i <= $highestRow; $i++) {
        $instId   = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(1, $i)->getValue();
        $result  = User::where('username', $instId)->where('role', 'EADMIN')->first();
        if ($result) {
          $inst = $result->username;
        } else {
          return response()->json([
            'status'     => 'failure',
            'message'   => 'Invalid Institute Code on row number ' . $i . ' All Mappings till row number ' . ($i - 1) . ' in Excel file are Inserted Successfully',
            'row'       =>  $i
          ], 400);
        }
        //------------------------------------------------------------------------------------------
        $proctorCode = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(2, $i)->getValue();
        $result1  = User::where('username', $proctorCode)->where('role', 'PROCTOR')->where('inst_id', $instId)->first();
        if ($result1) {
          $proctor = $result1->uid;
        } else {
          return response()->json([
            'status'     => 'failure',
            'message'   => 'Invalid Proctor Code on row number ' . $i . ' All Mappings till row number ' . ($i - 1) . ' in Excel file are Inserted Successfully',
            'row'       =>  $i
          ], 400);
        }
        //-----------------------------------------------------------------------------------------
        $enrollno   = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(3, $i)->getValue();
        $result2  = User::where('username', $enrollno)->where('role', 'STUDENT')->where('inst_id', $instId)->first();
        if ($result2) {
          $student = $result2->uid;
        } else {
          return response()->json([
            'status'     => 'failure',
            'message'   => 'Invalid Enrollment Number on row number ' . $i . ' All Mappings till row number ' . ($i - 1) . ' in Excel file are Inserted Successfully',
            'row'       =>  $i
          ], 400);
        }
        //------------------------------------------------------------------------------------------
        $paperCode   = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(4, $i)->getValue();
        $result3  = SubjectMaster::where('paper_code', $paperCode)->where('inst_uid', $result->uid)->first();
        if ($result3) {
          $subject = $result3->id;
        } else {
          return response()->json([
            'status'     => 'failure',
            'message'   => 'Invalid Subject Code on row number ' . $i . ' All Mappings till row number ' . ($i - 1) . ' in Excel file are Inserted Successfully',
            'row'       =>  $i
          ], 400);
        }
        //------------------------------------------------------------------------------------------
        $current_time     = Carbon::now();


        try {
          $result = DB::statement("INSERT INTO `student_proctor_alloc_master`(`instId`, `proctorid`, `paperId`, `studid`, `created_at`, `updated_at`) VALUES ('$inst','$proctor','$subject','$student','$current_time','$current_time')");
        } catch (\Exception $e) {
          return response()->json([
            'status'     => 'failure',
            'message'   => 'Problem Uploading Student Proctor Allocation in Database.Probably Duplicate Entry.All Mappings till row number ' . ($i - 1) . ' in Excel file are Inserted Successfully',
            'row'       =>  $i
          ], 400);
        }
      }
      return response()->json([
        'status'     => 'success',
        'message'   => 'Student Proctor Allocation Uploaded Successfully...',
      ], 200);
    } else {
      return response()->json([
        "status"          => "failure",
        "message"         => "File must be .xlsx only with maximum 1 MB  of Size.",
      ], 400);
    }
  }

  public function notedWarning($warningId)
  {
    $result = ProctorStudentWarningMaster::where('id', $warningId)->update(['noted' => '1']);
    return response()->json([
      'status'     => 'success',
      'message'   => 'Student Noted...',
    ], 200);
  }

  public function getProctors($request)
  {
    $role = $request->role;
    $instId = $request->instId;

    if ($role == 'ADMIN') {
      $result = User::where('role', 'PROCTOR')->get();
    } else if ($role == 'EADMIN') {
      $result = User::where('role', 'PROCTOR')->where('inst_id', $instId)->get();
    }

    if ($result) {
      return response()->json([
        'status'     => 'success',
        'data'   => $result,
      ], 200);
    } else {
      return response()->json([
        'status'     => 'failure',
        'message'   => 'Data not found',
      ], 400);
    }
  }

  public function getSubjectByProctorId($id)
  {
    $res = User::where('username', $id)->where('role', 'PROCTOR')->first();
    $result = ProctorSubjectMaster::where('uid', $res->uid)->get();

    if ($result) {
      return json_encode([
        'status'  => 'success',
        'data'    => new ProctorSubjectCollection($result)
      ], 200);
    }
  }

  public function getProctorSummary($request)
  {
    $proctor = $request->proctor;
    $paperId = $request->paperId;

    $result = ProctorStudentWarningMaster::where('proctor', $proctor)->where('paperId', $paperId)->get();

    if ($result) {
      return json_encode([
        'status'  => 'success',
        'data'    => new ProctorStudentWarningCollection($result)
      ], 200);
    } else {
      return json_encode([
        'status'  => 'failure',
      ], 400);
    }
  }

  public function deleteStudentQuestions($id)
  {
    $result = CandQuestion::where('stdid', $id)->delete();
  }

  public function endExamProctor($id, $reason)
  {
    $cqnid = '';
    $wqnid = '';
    $uqnid = '';
    $marksobt = 0;
    //----------get value of cqnid,wqnid,uqnid--------------------------------
    $result = DB::select("SELECT GROUP_CONCAT(qnid) as cqnid,sum(marks) as marksobt FROM `cand_questions` where exam_id='$id' and answered in('answered','answeredandreview') and trim(stdanswer)=trim(cans)");
    if ($result) {
      $cqnid     = $result[0]->cqnid;
      $marksobt   = $result[0]->marksobt;
    }

    $result1 = DB::select("SELECT GROUP_CONCAT(qnid) as wqnid FROM `cand_questions` where exam_id='$id' and answered in('answered','answeredandreview') and trim(stdanswer)!=trim(cans)");
    if ($result1) {
      $wqnid = $result1[0]->wqnid;
    }

    $result2 = DB::select("SELECT GROUP_CONCAT(qnid) as uqnid FROM `cand_questions` where exam_id='$id' and answered in('unanswered','unansweredandreview')");
    if ($result2) {
      $uqnid = $result2[0]->uqnid;
    }
    //------------------------------------------------------------------------

    //-------------------Update Exam Resource with End Exam Records-----------
    $results = DB::table('cand_test')->where('id', $id)->update([
      'cqnid'       =>   $cqnid,
      'wqnid'       =>   $wqnid,
      'uqnid'       =>   $uqnid,
      'end_on'       =>  Carbon::now(),
      'end_by'      =>  Auth::user()->uid,
      'status'      =>  'over',
      'marksobt'    =>  $marksobt,
      'updated_at'  =>  Carbon::now(),
      'endExamReason' => $reason,
    ]);
    //------------------------------------------------------------------------
    return json_encode([
      'status' => 'success'
    ], 200);
  }

  public function getProctorDashboard($request)
  {
    $date = $request->fromDate;
    $slot = $request->slot;
    $status = $request->status;
    $inst  = $request->inst;

    $total = 0;
    $totalStud = 0;
    $totalLoggedIn = 0;
    $totalNotLoggedIn = 0;

    $papercodes = '';
    $uid = '';

    $slotadd = '';
    $statusadd = '';

    if ($slot != '') {
      $slotadd = " and slot='$slot'";
    }

    $result = DB::select("select group_concat(id) as id from subject_master where from_date like '%$date%' and inst_uid='$inst' $slotadd");

    if ($result) {
      if ($result[0]->id != null && $result[0]->id != '') {
        $papercodes = $result[0]->id;
      }
    }

    if ($papercodes != '') {
      $result1 = DB::select("SELECT group_concat(uid) as uid FROM proctor_subject_master where paperId in($papercodes)");

      if ($result1) {
        if ($result1[0]->uid != null && $result1[0]->uid != '') {
          $uid = $result1[0]->uid;
          $total = sizeof(explode(',', $uid));

          $result2 = DB::select("select count(distinct uid) as cnt,group_concat(uid) as loggedin from sessions where starttime like '%$date%' and uid in($uid)");
          if ($result2) {
            $totalLoggedIn = $result2[0]->cnt;
            $totalNotLoggedIn = $total - $totalLoggedIn;
            $result3 = null;

            $result3 = DB::select("select count(*) as cnt from student_proctor_alloc_master where proctorid in($uid)");
            $loggedinUid = $result2[0]->loggedin;

            if ($result3) {
              $totalStud = $result3[0]->cnt;
              if ($status == 'all') {
                $result4 = ProctorSubjectMaster::whereIn('paperId', explode(',', $papercodes))->whereIn('uid', explode(',', $uid))->get();
              } else if ($status == 'loggedin') {
                $result4 = ProctorSubjectMaster::whereIn('paperId', explode(',', $papercodes))->whereIn('uid', explode(',', $loggedinUid))->get();
              } else if ($status == 'notloggedin') {
                $uidArr = array_unique(explode(',', $uid));
                $uidLoggedArr = array_unique(explode(',', $loggedinUid));

                sort($uidArr);
                sort($uidLoggedArr);
                
                $notLoggedinUid = $this->getUncommon($uidArr, $uidLoggedArr, sizeof($uidArr), sizeof($uidLoggedArr));

                $result4 = ProctorSubjectMaster::whereIn('paperId', explode(',', $papercodes))->whereIn('uid', $notLoggedinUid)->get();
              }

              return json_encode([
                'status' => 'success',
                'total' => $total,
                'totalLoggedIn' => $totalLoggedIn,
                'totalNotLoggedIn' => $totalNotLoggedIn,
                'totalStud' => $totalStud,
                'reportData' => new ProctorSubjectStudCountCollection($result4),
              ], 200);
            }
          }
        } else {
          return json_encode([
            'status' => 'failure',
            'message' => 'No Invigilator Data found for your selected filters',
          ], 400);
        }
      }
    } else {
      return json_encode([
        'status' => 'failure',
        'message' => 'No Subjects found for your selected filters',
      ], 400);
    }
  }


  function getUncommon($arr1, $arr2, $n1, $n2)
  {
    $i = 0;
    $j = 0;
    $k = 0;
    $tmp1 = [];
    while ($i < $n1 && $j < $n2) {

      // If not common, prsmaller
      if ($arr1[$i] < $arr2[$j]) {
        array_push($tmp1, $arr1[$i]);
        $i++;
        $k++;
      } else if ($arr2[$j] < $arr1[$i]) {
        array_push($tmp1, $arr2[$j]);
        $k++;
        $j++;
      }

      // Skip common element
      else {
        $i++;
        $j++;
      }
    }

    // get remaining elements
    while ($i < $n1) {
      array_push($tmp1, $arr1[$i]);
      $i++;
      $k++;
    }
    while ($j < $n2) {
      array_push($tmp1, $arr2[$j]);
      $j++;
      $k++;
    }

    return $tmp1;
  }

  public function deleteStudentWarningMessages($id)
  {
    $result = DB::select("SELECT group_concat(id) as id FROM cand_test WHERE `stdid` ='$id'");
  
    if($result)
    {
      $examList = $result[0]->id;
      if($examList!=null && $examList!='')
      {
        $res = DB::statement("Delete from proctor_student_warning_master WHERE examId in ($examList)");
      }
    }
  }

  public function storeSetterUsers($request)
  {
    $username       = $request->username;
    $name           = $request->name;
    $mobile         = $request->mobile;
    $email          = $request->email;
    $inst           = $request->instId;
    $rrr = User::where('username', $inst)->where('role', 'EADMIN')->first();
    $college_name   = $rrr->college_name;
    $password       = Hash::make($request->password);
    
    try {
          $result = User::create([
          'username' => $username,
          'name'     => $name,
          'mobile'   => $mobile,
          'email'    => $email,
          'inst_id'  => $inst,
          'password' => $password,
          'role'     => 'PAPERSETTER',
          'status'   => 'ON',
          'college_name'   => $college_name,
          'regi_type' => 'PAPERSETTER',
          'verified' => 'verified',
          'origpass' => $request->password,
        ]);
        return response()->json([
            "status"  => "success"
          ], 200);
    } catch (\Exception $e) {
      return response()->json([
        "status"  => "failure"
      ], 400);
    }
  }

  public function uploadPaperSetter($request)
  {
    $validator = Validator::make($request->all(), [
      'file'      => 'required|max:1024',
    ]);

    $extension = File::extension($request->file->getClientOriginalName());

    if ($validator->fails()) {
      return response()->json([
        "status"          => "failure",
        "message"         => "File for uploading is required with max file size 1 MB",
      ], 400);
    }

    if ($extension == "xlsx") 
    {
      $fileName           = 'papersetter.xlsx';
      $request->file->move(public_path('assets/tempfiles/'), $fileName);
      $reader             = IOFactory::createReader("Xlsx");
      $spreadsheet        = $reader->load(public_path('assets/tempfiles/') . $fileName);
      
      $highestRow         = $spreadsheet->getActiveSheet()->getHighestRow();

      for ($i = 2; $i <= $highestRow; $i++) 
      {
        $instId         = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(1, $i)->getValue();
        $name           = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(2, $i)->getValue();
        $mobile         = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(3, $i)->getValue();
        $email          = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(4, $i)->getValue();
        $rrr = User::where('username', $instId)->where('role', 'EADMIN')->first();
        $college_name   = $rrr->college_name;
        $origpass       = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(5, $i)->getValue();
      
          try {
            $result = User::create([
              'username' => $email,
              'name'     => $name,
              'mobile'   => $mobile,
              'email'    => $email,
              'inst_id'  => $instId,
              'password' => Hash::make($origpass),
              'role'     => 'PAPERSETTER',
              'status'   => 'ON',
              'college_name'   => $college_name,
              'regi_type' => 'PAPERSETTER',
              'verified' => 'verified',
              'origpass' => $origpass,
            ]);
          }
          catch (\Exception $e) {
            return response()->json([
              "status"  => "failure",
              "message" => "Problem Uploading Data on Row " . $i . "..."
            ], 400);
          }
      }
      return response()->json([
        "status"  => "success",
        "message" => "Data uploaded Successfully..."
      ], 200);
    } else {
      return response()->json([
        "status"          => "failure",
        "message"         => "File must be .xlsx only with maximum 1 MB  of Size.",
      ], 400);
    }
  }

  public function getSubjectByPaperSetter($paperSetterId,$instId,$mode)
  {
    if($mode == '' || $mode == null)
    {
      $result = PaperSetterSubjectMaster::select(DB::raw('group_concat(paperId) as paperId'))->where('uid', $paperSetterId)->groupBy('uid')->first();
      $subArr = null;$result1=null;

      if($result->paperId != '' && $result->paperId != null)
      {
        $subArr = explode(',',$result->paperId);
        $result1= SubjectMaster::whereIn('id',$subArr)->where('exam_mode','!=','subjective')->get();
      }

      if ($result) {
        return json_encode([
          'status'  => 'success',
          'data'    => new PaperCollection($result1)
        ], 200);
      }
    }
    else if($mode == 'subjective' || $mode =='both')
    {
      $result = PaperSetterSubjectMaster::select(DB::raw('group_concat(paperId) as paperId'))->where('uid', $paperSetterId)->groupBy('uid')->first();
      $subArr = null;$result1=null;

      if($result->paperId != '' && $result->paperId != null)
      {
        $subArr = explode(',',$result->paperId);
        $result1= SubjectMaster::whereIn('id',$subArr)->whereIn('exam_mode',['subjective','both'])->get();
      }

      if ($result) 
      {
        return json_encode([
          'status'  => 'success',
          'data'    => new PaperCollection($result1)
        ], 200);
      }
    }
    else if($mode == 'objective' || $mode =='both')
    {
      $result = PaperSetterSubjectMaster::select(DB::raw('group_concat(paperId) as paperId'))->where('uid', $paperSetterId)->groupBy('uid')->first();
      $subArr = null;$result1=null;

      if($result->paperId != '' && $result->paperId != null)
      {
        $subArr = explode(',',$result->paperId);
        $result1= SubjectMaster::whereIn('id',$subArr)->whereIn('exam_mode',['objective','both'])->get();
      }

      if ($result) 
      {
        return json_encode([
          'status'  => 'success',
          'data'    => new PaperCollection($result1)
        ], 200);
      }
    }

  }

  public function storeSubSetterAlloc($request)
  {
    $uid = $request->uid;
    $paperId = $request->paperId;
    $type = $request->type;
    $instId = $request->instId;
  

    $res = PaperSetterSubjectMaster::create([
      'instId' => $instId,
      'uid' => $uid,
      'paperId' => $paperId,
      'type' => $type,
      'created_at' => Carbon::now()
    ]);  
    
    if($res)
    {
      return json_encode([
        'status'  => 'success',
        'message'  => 'Subject Allocated to Paper Setter Successfully...',
      ], 201);
    }
    else
    {
      return json_encode([
        'status'  => 'failure',
        'message'  => 'Problem Allocating Subject to Paper Setter...',
      ], 400);
    }
  }

  public function uploadSetterSubjects($request)
  {
    $validator = Validator::make($request->all(), [
      'file'      => 'required|max:1024',
    ]);

    $extension = File::extension($request->file->getClientOriginalName());

    if ($validator->fails()) {
      return response()->json([
        "status"          => "failure",
        "message"         => "File for uploading is required with max file size 1 MB",
      ], 400);
    }

    if ($extension == "xlsx") 
    {
      $fileName           = 'setterSubjectAlloc.xlsx';
      $request->file->move(public_path('assets/tempfiles/'), $fileName);
      $reader             = IOFactory::createReader("Xlsx");
      $spreadsheet        = $reader->load(public_path('assets/tempfiles/') . $fileName);
      $highestRow         = $spreadsheet->getActiveSheet()->getHighestRow();

      for ($i = 2; $i <= $highestRow; $i++) 
      {
        try 
        {
          $instId         = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(1, $i)->getValue();
          $instUid        = User::where('username', $instId)->where('role', 'EADMIN')->first()->uid;

          $username       = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(2, $i)->getValue();
          $uid            = User::where('username', $username)->where('inst_id',$instId)->where('role', 'PAPERSETTER')->first()->uid;

          $paperCode      = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(3, $i)->getValue();
          $paperId        = SubjectMaster::where('paper_code',$paperCode)->where('inst_uid',$instUid)->first()->id;
          $type           = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(4, $i)->getValue();

        
          $result = PaperSetterSubjectMaster::create([
            'instId' => $instUid,
            'uid' => $uid,
            'paperId' => $paperId,
            'type' => $type,
            'created_at' => Carbon::now()
          ]);
        }
        catch(\Exception $e)
        {
          return response()->json([
            'status' 		=> 'failure',
            'message'   => 'Problem Inserting Paper Setter Subject Allocation. All Users till row number '.$i.' in Excel file are Inserted Successfully. Error:'.$e->getMessage(),
            'row'       =>  $i
          ],400);
        }
      } 

      return response()->json([
        'status' 		=> 'success',
        'message'   => 'Paper Setter and Subjects Allocation is Successfuly Uploaded.',
      ],200);

    }
  }

  public function getSubSetterAlloc($request)
  {
    if($request->type == 'search')
    {
      $username = $request->username;
      $instId = $request->instId;
      $instUid = $request->instUid;

      $uid = '';
      $res = User::where('username',$username)->where('inst_id',$instId)->where('role','PAPERSETTER')->first();
      if($res)
      {
        $uid = $res->uid;
      }

      $result = PaperSetterSubjectMaster::where('uid',$uid)->where('instId',$instUid)->paginate(50);
      if($result)
      {
        return new PaperSetterSubjectCollection($result);
      }
    }
    else if($request->type =='searchByPaperId')
    {
      $paperId = $request->paperId;
      $instId = $request->instId;
      $res = User::where('username',$instId)->where('role','EADMIN')->first();
      $instUid = $res->uid;
      $setterId = $request->setterId;

      $result = PaperSetterSubjectMaster::where('uid',$setterId)->where('instId',$instUid)->where('paperId',$paperId)->get();
      if($result)
      {
        return response()->json([
          'status' 		=> 'success',
          'data'      => new PaperSetterSubjectCollection($result),
        ],200);
      }
      else
      {
        return response()->json([
          'status' 		=> 'failure',
        ],400);
      }
    }
    else
    {
      $instId = $request->instId;
      $result = PaperSetterSubjectMaster::where('instId',$instId)->paginate(50);
      if($result)
      {
        return new PaperSetterSubjectCollection($result);
      }
    }
  }

  public function deleteSubSetterAlloc($id)
  {
    $result = PaperSetterSubjectMaster::where('id',$id)->delete();

    return response()->json([
      'status' 		=> 'success',
      'message'   => 'Paper Setter and Subjects Allocation is Deleted Successfuly.',
    ],200);
  }

  public function searchStudentByUsername($username,$instId)
  {
    $result = User::where('username',$username)->where('inst_id',$instId)->paginate(50);
    return $result;
  }

  public function getAnswerPdfStatistics($paper_code,$request)
  {
    $inst_id = $request->instId;

    $result = CandTest::where('paper_id',$paper_code)->where('inst',$inst_id)->get();
    if($result)
    {
      return response()->json([
        'status' 		=> 'success',
        'data'   => $result,
      ],200);
    }
    else
    {
      return response()->json([
        'status' 		=> 'failure',
      ],400);
    }
  }

  public function downloadAnswerPdf($paperId,$request)
  {
    $inst_id = $request->instId;
    $results = CandTest::where('paper_id',$paperId)->where('inst',$inst_id)->get();
    //-------------------------Create Directory-----------------------------------------------
    $path = public_path().'/data/answers/'.$paperId;
    File::makeDirectory($path, $mode = 0777, true, true);
    //----------------------------------------------------------------------------------------
    $cnt = 0;
    //---------------------Move Desired files in created directory----------------------------
    foreach($results as $result)
    {
      if($result->answerFile != null && $result->answerFile != '')
      {
        try
        {
          File::copy(public_path().'/data/answers/'.$result->answerFile, public_path().'/data/answers/'.$paperId.'/'.$result->answerFile);
          $cnt++;
        }
        catch(\Exception $e)
        {

        }
      }
    }
    //----------------------------------------------------------------------------------------

    //---------------------------Make Zip of the directory----------------------------------
    try 
    {
        $rootPath = realpath(public_path().'/data/answers/'.$paperId);
        $zip = new ZipArchive();
        $zip->open($paperId.'.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file)
        {
            if (!$file->isDir())
            {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
        $zip->close();

        File::move($paperId.'.zip',public_path().'/data/answers/'.$paperId.'.zip');
        File::deleteDirectory(public_path().'/data/answers/'.$paperId);

        $url = Config::get('constants.PROJURL');
        return response()->json([
          'status' 		=> 'success',
          'path'   => $url.'/data/answers/'.$paperId.'.zip',
        ],200);

    }
    catch(\Exception $e)
    {
      return response()->json([
        'status' 		=> 'failure',
      ],400);
    }
    //-----------------------------------------------------------------------------------------
  }

  public function setterConfirmation($uid,$request)
  {
    $paperId = $request->paperId;
    $setterType = $request->setterType;
    $instId= $request->instId;
    $res = User::where('username',$instId)->where('role','EADMIN')->first();
    $instUid = $res->uid;

    $result = PaperSetterSubjectMaster::where('uid',$uid)->where('paperId',$paperId)->where('type',$setterType)->where('instId',$instUid)->first();

    if($result)
    {
      $result->conf = '1';
      $result->save();
      return response()->json([
        'status' 		=> 'success',
      ],200);
    }
    else
    {
      return response()->json([
        'status' 		=> 'failure',
      ],400);
    }
  }

  public function unconfSubList($request)
  {
    $instId= $request->instId;
    $res = User::where('username',$instId)->where('role','EADMIN')->first();
    $instUid = $res->uid;
    $result = PaperSetterSubjectMaster::where('instId',$instUid)->where('type','PS')->where('conf','0')->get();
    if($result)
    {
      return response()->json([
        'status' 		=> 'success',
        'data'      => $result,
      ],200);
    }
    else
    {
      return response()->json([
        'status' 		=> 'success',
        'data'      => [],
      ],200);
    }
  }

  public function getSubjectConfInfo($id)
  {
    $cnt = QuestionSet::where('paper_uid',$id)->count();
    $cnt1 = 0;
    $result = DB::select("select count(*) as cnt from question_set where paper_uid='$id'  and (moderator is not null) and moderator!=0");
    if($result[0])
    {
      $cnt1 = $result[0]->cnt;

      return response()->json([
        'status' 		=> 'success',
        'questCount'      => $cnt,
        'modQuestCount' => $cnt1
      ],200);
    }
    else
    {
      return response()->json([
        'status' 		=> 'failure',
      ],400);
    }
  }

  public function storeSubCheckerAlloc($request)
  {
    $uid = $request->uid;
    $paperId = $request->paperId;
    $type = $request->type;
    $instId = $request->instId;

    try
    {
      $result = CheckerSubjectMaster::create([
        'instId' => $instId,
        'uid' => $uid,
        'paperId' => $paperId,
        'type' =>$type,
        'created_at' => Carbon::now()
      ]);

      return response()->json([
        'status' 		=> 'success',
        'message' => 'Checker Subject Allocation Added Successfully...'
      ],200);
    }
    catch(\Exeption $e)
    {
      return response()->json([
        'status' 		=> 'failure',
      ],400);
    }
  }

  public function uploadCheckerSubjects($request)
  {
    $validator = Validator::make($request->all(), [
      'file'      => 'required|max:1024',
    ]);

    $extension = File::extension($request->file->getClientOriginalName());

    if ($validator->fails()) {
      return response()->json([
        "status"          => "failure",
        "message"         => "File for uploading is required with max file size 1 MB",
      ], 400);
    }

    if ($extension == "xlsx") 
    {
      $fileName           = 'checkerSubjectAlloc.xlsx';
      $request->file->move(public_path('assets/tempfiles/'), $fileName);
      $reader             = IOFactory::createReader("Xlsx");
      $spreadsheet        = $reader->load(public_path('assets/tempfiles/') . $fileName);
      $highestRow         = $spreadsheet->getActiveSheet()->getHighestRow();

      for ($i = 2; $i <= $highestRow; $i++) 
      {
        try 
        {
          $instId         = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(1, $i)->getValue();
          $instUid        = User::where('username', $instId)->where('role', 'EADMIN')->first()->uid;

          $username       = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(2, $i)->getValue();
          $uid            = User::where('username', $username)->where('inst_id',$instId)->where('role', 'CHECKER')->first()->uid;

          $paperCode      = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(3, $i)->getValue();
          $paperId        = SubjectMaster::where('paper_code',$paperCode)->where('inst_uid',$instUid)->first()->id;
          $type           = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(4, $i)->getValue();

        
          $result = CheckerSubjectMaster::create([
            'instId' => $instUid,
            'uid' => $uid,
            'paperId' => $paperId,
            'type' => $type,
            'created_at' => Carbon::now()
          ]);
        }
        catch(\Exception $e)
        {
          return response()->json([
            'status' 		=> 'failure',
            'message'   => 'Problem Inserting Paper Checker Subject Allocation. All Users till row number '.$i.' in Excel file are Inserted Successfully. Error:'.$e->getMessage(),
            'row'       =>  $i
          ],400);
        }
      } 

      return response()->json([
        'status' 		=> 'success',
        'message'   => 'Paper Checker and Subjects Allocation is Successfuly Uploaded.',
      ],200);

    }
  }

  public function getSubCheckerAlloc($request)
  {
    if($request->type == 'search')
    {
      $username = $request->username;
      $instId = $request->instId;
      $instUid = $request->instUid;

      $uid = '';
      $res = User::where('username',$username)->where('inst_id',$instId)->where('role','CHECKER')->first();
      if($res)
      {
        $uid = $res->uid;
      }

      $result = CheckerSubjectMaster::where('uid',$uid)->where('instId',$instUid)->paginate(50);
      if($result)
      {
        return new CheckerSubjectCollection($result);
      }
    }
    else
    {
      $instId = $request->instId;
      $result = CheckerSubjectMaster::where('instId',$instId)->paginate(50);
      return new CheckerSubjectCollection($result);
    }
  }

  public function deleteSubCheckerAlloc($id)
  {
    $result = CheckerSubjectMaster::where('id',$id)->delete();

    return response()->json([
      'status' 		=> 'success',
      'message' => 'Subject Checker Allocation Deleted Successfully...'
    ],200);    
  }
  
  public function getCheckerType($request)
  {
    $paperId = $request->paperId;
    $uid = $request->checkeruid;
    $instId = $request->instId;

    $result = User::where('username',$instId)->where('role','EADMIN')->first();
    $instUid = $result->uid;

    $result = CheckerSubjectMaster::where('instId',$instUid)->where('uid',$uid)->where('paperId',$paperId)->first();

    if($result)
    {
      return response()->json([
        'status' 		=> 'success',
        'data' => $result
      ],200);    
    }
    else
    {
      return response()->json([
        'status' 		=> 'failure',
      ],400);  
    }
  }

  public function getCheckedStudExams($request)
  {
    $paperId      = $request->paperId;
    $checkeruid   = $request->checkeruid;

    $results = StudentCheckerAllocMaster::where('checkerId', $checkeruid)->where('paperId', $paperId)->where('type','QPM')->get();
    $studList = [];

    if ($results && $results->count() > 0) {
      foreach ($results as $result) {
        array_push($studList, $result->studid);
      }

      $res = CandTest::where('paper_id', $paperId)->whereIn('stdid', $studList)->where('paper_checking','1')->get();

      if ($res->count() > 0) {
        return json_encode([
          'status'  => 'success',
          'data'    => new ExamCollection($res)
        ], 200);
      } else {
        return json_encode([
          'status'  => 'failure',
          'message' => 'No Exam Data found...'
        ], 400);
      }
    } else {
      return json_encode([
        'status'  => 'failure',
        'message' => 'No Exam Data found...'
      ], 400);
    }
  }
}
