<?php
namespace App\Admin;
use App\Models\User;
use App\Models\StudentExam;
use App\Http\Resources\ExamCollection;
use App\Http\Resources\CheckersCollection;
use App\Http\Resources\CheckerStudentCollection;
use App\Http\Resources\CheckerSubjectCollection;
use App\Http\Resources\CustomExamReportCollection;
use App\Http\Resources\InstProgramCollection;
use App\Http\Resources\PaperCollection;
use App\Http\Resources\ProctorSnapCollection;
use App\Http\Resources\AnswerCollection;
use App\Http\Resources\QuestionCollection;
use App\Http\Resources\ProgramCollection;
use App\Http\Resources\TopicCollection;
use App\Http\Resources\PaperResource;
use App\Http\Resources\ExamResource;
use App\Models\CandTest;
use App\Models\CandTestReset;
use App\Models\LoginLink;
use App\Models\TopicMaster;
use App\Models\Elapsed;
use App\Models\CheckerSubjectMaster;
use App\Models\QuestionSet;
use App\Models\CandQuestion;
use App\Models\CandQuestionReset;
use App\Models\OauthAccessToken;
use App\Models\ProctorSnaps;
use App\Models\ProctorSnapDetails;
use App\Models\InstPrograms;
use App\Models\ProgramMaster;
use App\Models\SubjectMaster;
use App\Models\HeaderFooterText;
use App\Models\StudentCheckerAllocMaster;
use App\Models\Session;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Validator;
use Auth;
use File;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\InstituteResource;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use QuestionCollection as GlobalQuestionCollection;

class Admin1
{
  private $uid;
  private $username;
  private $mobile;
  private $email;
  private $role;
  private $name;

  public function __construct($arr)
  {
	  $this->uid 		      = $arr->uid;
    $this->username     = $arr->username;
    $this->mobile       = $arr->mobile;
    $this->email        = $arr->email;
    $this->role         = $arr->role;
    $this->name         = $arr->name;
  }

  public function resetExam($request)
  {
    $stdid              = "'".implode("','",$request->stdid)."'";
    $paperId            = $request->paperId;
    $instId             = $request->instId;
    $instUid            = $request->instUid;

    $r   = DB::select("SELECT GROUP_CONCAT(uid) as uid FROM users where username in($stdid) and inst_id='$instId'");
    $uid = $r[0]->uid; // uid list of users

    $res = DB::select("select group_concat(id) as examId from cand_test where stdid in ($uid) and inst='$instId' and paper_id='$paperId'");
    $examId = $res[0]->examId; // exam id list of students
    
    if($examId)
    {
        DB::beginTransaction();
        try
        {
          //-------Save questions in backup table---------------------------
          $result = DB::statement("insert into cand_questions_reset select * from cand_questions where exam_id in($examId)");
          $result = DB::statement("update cand_questions set answered='unanswered',stdanswer=null,answer_by=null,answer_on=null where exam_id in($examId)");

          $result1 = DB::statement("insert into cand_questions_copy_reset select * from cand_questions_copy where exam_id in($examId)");
          $result1 = DB::statement("delete from cand_questions_copy where exam_id in($examId)");
          //----------------------------------------------------------------

          //-------Save exam in backup table--------------------------------
          $result2 = DB::statement("insert into cand_test_reset select * from cand_test where id in($examId)");
          $result2 = DB::statement("update cand_test set starttime=null,endtime=null,cqnid=null,wqnid=null,uqnid=null,status=null,entry_on=null,end_on=null,end_by=null,examip=null,continueexam=0,marksobt=null where id in($examId)");
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
          DB::commit();
          return response()->json([
            'status' 		=> 'success',
          ],200);
        }
        catch(\Exception $e)
        {
          DB::rollback();
          return response()->json([
            'status' 		=> 'failure',
          ],400);
        }
    }
    else
    {
      DB::rollback();
      return response()->json([
        'status' 		=> 'failure',
      ],400);
    }
  }

  public function loginLink($students,$request)
  {
    $students           = explode(',',$students);
    $paperId            = $request->paperId;
    $instId             = $request->instId;
    $instUid            = $request->instUid;
    $missedArray        = array();
    $uid                = null;
    $pass               = null;

    $results            = User::whereIn('username',$students)->where('inst_id','like',$instId)->where('role','STUDENT')->get();
    
    foreach($results as $student)
    {
        $uid      = $student->uid;
        $pass     = $student->origpass;

        $std      = urlencode(base64_encode($student->username));
        $pass     = urlencode(base64_encode($pass));

        $inst     = urlencode(base64_encode($instId));
        $url      = Config::get('constants.PROJURL').'/linkLogin/'.$std.'/'.$pass.'/'.$inst;

        try
        {
          $result1 = LoginLink::create([
            'stduid'  => $uid,
            'inst_id' => $inst,
            'link'    => $url
          ]);
        }
        catch(\Exception $e)
        {
          array_push($missedArray,$student);
        }
    }

    return response()->json([
      'status' 		=> 'success',
      'missed'    => $missedArray,
    ],200);

  }

  public function searchQuestionByQnid($searchString)
  {
    //---------First Search by Qnid-----------------------------------
    $result = QuestionSet::where('qnid',$searchString)->paginate(50);
    if($result->count() > 0)
    {
      return new QuestionCollection($result);
    }
    else
    {
      return response()->json([
        'status' 		=> 'failure',
      ],400);
    }
    //----------------------------------------------------------------
  }
  
  public function getUser($username,$instId)
  {
    $result = User::where('username',$username)->where('inst_id',$instId)->paginate(50);
    if($result->count() > 0)
    {
      return response($result, 200);
    }
    else
    {
      return response()->json([
        'status' 		=> 'failure',
      ],400);
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
    $question               = str_replace('&lt;','<',str_replace('&gt;','>',str_replace('amp;','',$request->question)));
    $modelAnswer            = $request->modelAnswer;
    $modelAnswerImage       = $request->modelAnswerImage;
    $allowImageUpload       = $request->allowImageUpload;

    $current_time 			    = Carbon::now();

    $qfilepath              = '';
    $ansfilepath            = '';

    //------------------------Upload Question Image and  Model Answer Image if any----------------
      $new_name='';
      if($request->qufig)
      {
            $part = rand(100000,999999);
            $validation = Validator::make($request->all(), ['qufig' => 'required|mimes:jpeg,jpg']);
            $path = $request->file('qufig')->getRealPath();

            if($validation->passes())
            {
                $image = $request->file('qufig');
                $new_name = 'Q_'.$subjectId.'_'.$part.'.' . $image->getClientOriginalExtension();
                $image->move(public_path('files'), $new_name);
                $path=public_path('files').'/'.$new_name;
                $qfilepath = $new_name;
            }
            else
            {
              return response()->json([
                "status"            => "failure",
                "message"           => 'Question Image must be jpeg or jpg',
              ], 400);
            }
      }
      $new_name='';
      if($request->modelAnswerImage)
      {
            $part = rand(100000,999999);
            $validation = Validator::make($request->all(), ['modelAnswerImage' => 'required|mimes:jpeg,jpg']);
            $path = $request->file('modelAnswerImage')->getRealPath();
           
            if($validation->passes())
            {
                $image = $request->file('modelAnswerImage');
                $new_name = 'ModelAnswer_'.$subjectId.'_'.$part.'.' .$image->getClientOriginalExtension();
                $image->move(public_path('files'), $new_name);
                $path=public_path('files').'/'.$new_name;
                $ansfilepath = $new_name;
            }
            else
            {
              return response()->json([
                "status"            => "failure",
                "message"           => 'Model Answer Image must be jpeg or jpg',
              ], 400);
            }
      }
      //-------------------------------------------------------------------------------------------
      
      $result = DB::statement("insert into question_set (paper_uid,paper_id,question,topic,subtopic,qu_fig,modelAnswer,modelAnswerImage,marks,difficulty_level,quest_type,coption,allowImageUpload,created_at,updated_at) values ('$subjectId','$subjectCode','$question','$topic','$subtopic','$qfilepath','$modelAnswer','$ansfilepath','$marks','$difficultyLevel','S','-','$allowImageUpload','$current_time','$current_time')");

      if($result)
      {
        return response()->json([
          "status"            => "success",
          "message"           => 'Question Inserted Successfully...',
        ], 200);
      }
      else
      {
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

    if ($validator->fails())
    {
      return response()->json([
        "status"          => "failure",
        "message"         => "File for uploading is required with max file size 10 MB",
      ], 400);
    }


    if ($extension == "xlsx") 
    {
      $fileName           = 'SubjectiveQuestionBank.xlsx';  

      $request->file->move(public_path('assets/tempfiles/'), $fileName);
      $reader             = IOFactory::createReader("Xlsx");
      $spreadsheet        = $reader->load(public_path('assets/tempfiles/').$fileName);
      $current_time 			= Carbon::now();
      $highestRow         = $spreadsheet->getActiveSheet()->getHighestRow();
      $values             = [];
      $new_name           = '';
      $new_name1          = '';
    
      for($i=2;$i<=$highestRow;$i++)
      {
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

      //------------------------Upload Question Image and  Model Answer Image if any----------------
      $new_name='';
      if($questionImage != '')
      {
        $part     = rand(100000,999999);
        $new_name = 'Q_'.$paper_code.'_'.$part.'.jpg';
        file_put_contents('files/'.$new_name, file_get_contents($questionImage));
      }
      $new_name1='';
      if($answerImage != '')
      {
        $part     = rand(100000,999999);
        $new_name1 = 'Q_'.$paper_code.'_'.$part.'.jpg';
        file_put_contents('files/'.$new_name1, file_get_contents($answerImage));
      }
      //-------------------------------------------------------------------------------------------
    
        $inst_uid         = User::where('username',$instId)->first()->uid;
       
        $paper_id         = SubjectMaster::where('paper_code',$paper_code)->where('inst_uid',$inst_uid)->first()->id;

        $values = [
          'paper_uid'       => $paper_id,
          'paper_id'        => $paper_code,
          'question'        => $question,
          'topic'           => $topic,
          'subtopic'        => $subtopic,
          'coption'         => '-',
          'modelAnswer'     => $modelAnswer,
          'quest_type'      => 'S',
          'allowImageUpload'=> $allowImageUpload,
          'marks'           => $marks,
          'difficulty_level'=> $diff_level,
          'qu_fig'          => $new_name,
          'modelAnswerImage'=> $new_name1
        ];

        $result         = QuestionSet::create($values); 

        if(!$result)
        {
          return response()->json([
            "status"            => "failure",
            "message"           => 'Problem Inserting Question on Row '.$i.'...',
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
    $subArray = rtrim($subArray,",");
    $array = explode(",",$subArray);
  
    $result = QuestionSet::whereIn('paper_uid',$array)->where('quest_type','S')->orderBy('created_at')->paginate(50);

    if($result)
    {
      return new QuestionCollection($result);
    }
    else
    {
      return response()->json([
        "status"            => "failure",
        "message"           => 'Unable to fetch Questions...',
      ], 400);
    }
  }

  public function updateSubjectiveQuestion($qnid,$request)
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
    $imgChange              = explode(',',$request->imgChange);
    $values                 = [];
    $origQustion            = QuestionSet::find($qnid);

    $qfilepath              = '';
    $modelAnswerImagePath   = '';



      $new_name='';
      if(in_array("qufig",$imgChange))
      {
            $validation = Validator::make($request->all(), ['qufig' => 'required|mimes:jpeg,jpg']);
            $path = $request->file('qufig')->getRealPath();

            if($validation->passes())
            {
                $image = $request->file('qufig');
                $new_name = $origQustion->qu_fig;

                if($new_name == '')
                {
                  $part = rand(100000,999999);
                  $new_name = 'Q_'.$subjectId.'_'.$part.'.' . $image->getClientOriginalExtension();
                }
                
                $image->move(public_path('files'), $new_name);
                $path=public_path('files').'/'.$new_name;
                $qfilepath = $new_name;

                $request->qufig = $qfilepath;
            }
            else
            {
              return response()->json([
                "status"            => "failure",
                "message"           => 'Question Image must be jpeg or jpg',
              ], 400);
            }
      }
      else
      {
        $request->qufig = $origQustion->qu_fig;
      }
      $new_name1='';
      if(in_array("modelAnswerImage",$imgChange))
      {
            $validation = Validator::make($request->all(), ['modelAnswerImage' => 'required|mimes:jpeg,jpg']);
            $path = $request->file('modelAnswerImage')->getRealPath();

            if($validation->passes())
            {
                $image = $request->file('modelAnswerImage');
                $new_name1 = $origQustion->modelAnswerImage;

                if($new_name1 == '')
                {
                  $part = rand(100000,999999);
                  $new_name1 = 'ModelAnswer_'.$subjectId.'_'.$part.'.' . $image->getClientOriginalExtension();
                }

                $image->move(public_path('files'), $new_name1);
                $path=public_path('files').'/'.$new_name1;
                $modelAnswerImagePath = $new_name1;

                $request->modelAnswerImage = $modelAnswerImagePath;
            }
            else
            {
              return response()->json([
                "status"            => "failure",
                "message"           => 'Option A Image must be jpeg or jpg',
              ], 400);
            }
      }
      else
      {
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
        'allowImageUpload'=> $allowImageUpload,
        'marks'           => $marks,
        'difficulty_level'=> $difficultyLevel,
        'moderator'       => Auth::user()->uid,
        'updated_at'      => Carbon::now(),
      ];

      if(in_array("qufig",$imgChange))
      {
        $values['qu_fig']   = $qfilepath;
      }
      if(in_array("modelAnswerImage",$imgChange))
      {
        $values['modelAnswerImage']       = $modelAnswerImagePath;
      }
  
      $result         = QuestionSet::find($qnid)->update($values); 

      if($result)
      {
        return response()->json([
          "status"            => "success",
          "message"           => 'Question Moderated Successfully...',
        ], 200);
      }
      else
      {
        return response()->json([
          "status"            => "failure",
          "message"           => 'Problem Moderating Question...',
        ], 400);
      }
  }

  public function getAllQuestionsByPaperCode($paper_id)
  {
    $result = QuestionSet::where('paper_uid',$paper_id)->get();
    if($result)
    {
      return response()->json([
        "status"            => "success",
        "data"              => new QuestionCollection($result),
      ], 200);
    }
    else
    {
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
    $password       = Hash::make($request->password);
    $checkerType    = $request->chekerType;
    $subjects       = $request->subjects;

    DB::beginTransaction();
    try
    {
      $result = User::create([
        'username' => $username,
        'name'     => $name,
        'mobile'   => $mobile,
        'email'    => $email,
        'inst_id'  => $inst,
        'password' => $password,
        'role'     => 'CHECKER',
        'status'   => 'ON',
        'regi_type'=> 'CHECKER',
        'verified' => 'verified',
        'origpass' => $request->password,
        'type'     => $checkerType
      ]);

      if($result)
      {
        foreach($subjects as $subject)
        {
          try
          {
            $result1= CheckerSubjectMaster::create([
              'uid' => $result->uid,
              'paperId' => $subject
            ]);
          }
          catch(\Exception $e)
          {
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

    }
    catch(\Exception $e)
    {
      DB::rollback();
      return response()->json([
        "status"  => "failure"
      ], 400);
    }

  }

  public function deleteCheckerSubjects($id)
  {
    $result=CheckerSubjectMaster::where('uid',$id)->delete();
  }

  public function uploadCheckers($request)
  {
    $validator = Validator::make($request->all(), [
      'file'      => 'required|max:1024',
    ]);

    $extension = File::extension($request->file->getClientOriginalName());

    if ($validator->fails())
    {
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
      $spreadsheet        = $reader->load(public_path('assets/tempfiles/').$fileName);
      $current_time 			= Carbon::now();
      $highestRow         = $spreadsheet->getActiveSheet()->getHighestRow();

      for($i=2;$i<=$highestRow;$i++)
      {
        $paperIdList    = [];
        $checkerType    = '';
        $instId         = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(1, $i)->getValue();
        $name           = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(2, $i)->getValue();
        $mobile         = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(3, $i)->getValue();
        $email          = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(4, $i)->getValue();
        $type           = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(5, $i)->getValue();

        if(strtolower($type) == 'checker')
        {
          $checkerType = 'QPC';
        }
        else if(strtolower($type) == 'moderator')
        {
          $checkerType = 'QPM';
        }
        else if(strtolower($type) == 'both')
        {
          $checkerType = 'QPCM';
        }

        $origpass       = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(6, $i)->getValue();
        $list           = explode(',',$spreadsheet->getActiveSheet()->getCellByColumnAndRow(7, $i)->getValue());

        if(sizeof($list) > 0)
        {
          DB::beginTransaction();
          try
          {
            $result = User::create([
              'username' => $email,
              'name'     => $name,
              'mobile'   => $mobile,
              'email'    => $email,
              'inst_id'  => $instId,
              'password' => Hash::make($origpass),
              'role'     => 'CHECKER',
              'status'   => 'ON',
              'regi_type'=> 'CHECKER',
              'verified' => 'verified',
              'origpass' => $origpass,
              'type'     => $checkerType
            ]);

            $subjects= SubjectMaster::whereIn('paper_code',$list)->get();

            foreach($subjects as $subject)
            {
              array_push($paperIdList,$subject->id);
              $result1= CheckerSubjectMaster::create([
                'uid' => $result->uid,
                'paperId' => $subject->id
              ]);
            }
            DB::commit();

            return response()->json([
              "status"  => "success",
              "message" => "Data uploaded Successfully..."
            ], 200);

          }
          catch(\Exception $e)
          {
            DB::rollback();
            return response()->json([
              "status"  => "failure",
              "message" => "Problem Uploading Data on Row ".$i."..."
            ], 400);
          }
        }
      }
    }
    else 
    {
      return response()->json([
        "status"          => "failure",
        "message"         => "File must be .xlsx only with maximum 1 MB  of Size.",
      ], 400);
    }
  }

  public function getStudentsBySubject($id)
  {
    $result = DB::select("SELECT * FROM cand_test WHERE paper_id='$id' and stdid not in(select distinct studid from student_checker_alloc_master where paperId='$id')");

    if($result)
    {
      return json_encode([
				'status' => 'success',
        'data' => new ExamCollection($result)
			],200);
    }
    else
    {
      return json_encode([
				'status' => 'failure',
			],400);
    }
  }

  public function getCheckersBySubject($id)
  {
    $result = SubjectMaster::find($id)->checkers;
    if($result)
    {
      return json_encode([
				'status' => 'success',
        'data' => new CheckersCollection($result)
			],200);
    }
    else
    {
      return json_encode([
				'status' => 'failure',
			],400);
    }
  }

  public function allocateStudentToCheckers($request)
  {
    $students = $request->students;
    $checkers = $request->checkers;
    $paperId  = $request->paperId;
    $instId   = $request->inst;
    
    $query    = [];
    $now      = Carbon::now();

    if(sizeof($students) > sizeof($checkers))
    {
      $k = 0;
      for($j=1; $j <= ceil(sizeof($students)/sizeof($checkers)); $j++)
      {
        for($i=0;$i<sizeof($checkers);$i++)
        {
          if(sizeof($students) == $k)
          {
            break;
          }
          array_push($query,['instId' => $instId,'checkerid' => $checkers[$i],'paperId' => $paperId,'studid' => $students[$k],'created_at' => $now]);

          $k++;
        }
        $i=0;

      }
    }
    else if(sizeof($students) < sizeof($checkers))
    {
      for($i=0;$i<sizeof($students);$i++)
      {
        array_push($query,['instId' => $instId,'checkerid' => $checkers[$i],'paperId' => $paperId,'studid' => $students[$i],'created_at' => $now]);
      }
    }
    else if(sizeof($students) == sizeof($checkers))
    {
      for($i=0;$i<sizeof($students);$i++)
      {
        array_push($query,['instId' => $instId,'checkerid' => $checkers[$i],'paperId' => $paperId,'studid' => $students[$i],'created_at' => $now]);
      }
    }

    $result = DB::table('student_checker_alloc_master')->insert($query);

    if($result)
    {
      return json_encode([
				'status' => 'success',
        'message'=> 'Student Checkers Allocation Successful...'
			],200);
    }
    else
    {
      return json_encode([
				'status' => 'failure'
			],400);
    }
  }

  public function getStudentToCheckers($request)
  {
    $paperId=$request->paperId;
    $instId = $request->inst;

    if($paperId == 'all')
    {
      $result = StudentCheckerAllocMaster::where('instId',$instId)->paginate(50);
    }
    else
    {
      $result = StudentCheckerAllocMaster::where('instId',$instId)->where('paperId',$paperId)->paginate(50);
    }

    if($result)
		{
			return new CheckerStudentCollection($result);
		}
		else
		{
			return json_encode([
				'status' => 'failure'
			],200);
		}
  }

  public function deleteStudentToCheckers($id)
  {
    $result = StudentCheckerAllocMaster::find($id)->delete();

    return json_encode([
      'status' => 'success',
      'message'=> 'Record Deleted Successfully...'
    ],200);
  }

  public function deleteCheckerAllocationByCheckerId($checkerId)
  {
    $result = StudentCheckerAllocMaster::where('checkerid',$checkerId)->delete();
  }

  public function searchCheckerAllocation($request)
  {
    $username   = $request->username;
    if(Auth::user()->role === 'EADMIN')
    {
      $inst       = Auth::user()->username;
    }

    $result = User::where('username',$username)->where('inst_id',$inst)->first();
    if($result)
    {
      $id = $result->uid;
      $res = StudentCheckerAllocMaster::where('checkerid',$id);
      $res1 = StudentCheckerAllocMaster::where('studid',$id)->union($res)->get();
      
      if($res1->count() > 0)
      {
        return new CheckerStudentCollection($res1);
      }
      else
      {
        return json_encode([
          'status' => 'failure',
          'message'=> 'User Not Found...',
          'data'  => []
        ],400);
      }
    }
    else
    {
      return json_encode([
        'status' => 'failure',
        'message'=> 'User Not Found...',
        'data'  => []
      ],400);
    }
  }

  public function deleteBulkCheckerAllocation($request)
  {
    $paperId      = $request->paperId;
    $students     = $request->students;
    $instId       = $request->inst;

    $result1      = User::select('uid')->whereIn('username',$students)->where('inst_id',$instId)->get();
    $studentList  = [];

    foreach($result1 as $res)
    {
      array_push($studentList,$res->uid);
    }   

    $result = StudentCheckerAllocMaster::where('instId',$instId)->where('paperId',$paperId)->whereIn('studid',$studentList)->delete();

    return json_encode([
      'status' => 'success',
      'message'=> 'Record Deleted Successfully...'
    ],200);
  }

  public function getSubjectByChecker($uid)
  {
    $result = CheckerSubjectMaster::where('uid',$uid)->get();

    if($result)
    {
      return json_encode([
        'status'  => 'success',
        'data'    => new CheckerSubjectCollection($result)
      ],200);
    }
  }

  public function getCheckerStudExams($request)
  {
    $paperId      = $request->paperId;
    $checkeruid   = $request->checkeruid;

    $results = StudentCheckerAllocMaster::where('checkerId',$checkeruid)->where('paperId',$paperId)->get();
    $studList = [];

    if($results && $results->count() > 0)
    {
      foreach($results as $result)
      {
        array_push($studList, $result->studid);
      }

      $res = CandTest::where('paper_id',$paperId)->whereIn('stdid',$studList)->get();

      if($res->count() > 0)
      {
        return json_encode([
          'status'  => 'success',
          'data'    => new ExamCollection($res)
        ],200);
      }
      else
      {
        return json_encode([
          'status'  => 'failure',
          'message' => 'No Exam Data found...'
        ],400);
      }
    }
    else
    {
      return json_encode([
        'status'  => 'failure',
        'message' => 'No Exam Data found...'
      ],400);
    }
    
  }

}
?>