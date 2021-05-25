<?php
namespace App\Admin;
use App\Models\User;
use App\Models\StudentExam;
use App\Http\Resources\ExamCollection;
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

}
?>