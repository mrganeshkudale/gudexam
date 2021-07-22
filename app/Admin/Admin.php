<?php
namespace App\Admin;
use App\Models\User;
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
use App\Models\TopicMaster;
use App\Models\QuestionSet;
use App\Models\CandQuestion;
use App\Models\OauthAccessToken;
use App\Models\ProctorSnaps;
use App\Models\InstPrograms;
use App\Models\ProgramMaster;
use App\Models\SubjectMaster;
use App\Models\HeaderFooterText;
use App\Models\Session;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Validator;
use Auth;
use File;
use App\Http\Resources\InstituteResource;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;


class Admin
{
  private $uid;
	private $username;
  private $mobile;
  private $email;
  private $role;
  private $name;

  public function __construct($arr)
	{
		$this->uid 					= $arr->uid;
    $this->username     = $arr->username;
    $this->mobile       = $arr->mobile;
    $this->email        = $arr->email;
    $this->role         = $arr->role;
    $this->name         = $arr->name;
	}

  public function clearSession($uid)
  {
      $date     = new Carbon('2001-01-01 01:01:01');

      $result = OauthAccessToken::where('user_id',$uid)->orderBy('created_at','DESC')->first();

      $result1  = Session::where('uid',$uid)->where('endtime',NULL)->orderBy('starttime','DESC')->first();

      if($result1)
      {
        $result1->endtime =  $date;
        $result1->save();

        $result->revoked ='1';
        $result->save();

        return response()->json([
          "status" => "success",
          "message" => "Session Cleared Successfully...",
        ], 200);
      }
      else
      {
        return response()->json([
          "status" => "failure",
          "message" => "Session is already cleared for this User...",
        ], 200);
      }
  }

  public function getUserDetails($username,$instId,$flag)
  {
    
    $result   = User::where('username',$username)->where('inst_id',$instId)->first();

    if($result)
    {
      if($result->role != 'STUDENT')
      {
        return response()->json([
          "status"        => "success",
          "uid"           => $result->uid,
          "username"      => $result->username,
          "instid"        => $result->instid,
          "region"        => $result->region,
          "mobile"        => $result->mobile,
          "email"         => $result->email,
          "role"          => $result->role,
          "name"          => $result->name,
        ], 200);
      }
      else
      {
        return response()->json([
          "status"        => "success",
          "uid"           => $result->uid,
          "username"      => $result->username,
          "instid"        => new InstituteResource(User::where('username',$result->inst_id)->first()),
          "region"        => $result->region,
          "mobile"        => $result->mobile,
          "email"         => $result->email,
          "role"          => $result->role,
          "name"          => $result->name,
        ], 200);
      }
    }
    else
    {
      return response()->json([
        "status"        => "failure",
      ], 400);
    }
  }

  public function updateFooter($orgName)
  {
    $result = HeaderFooterText::find(1)->first();
    $result->footer = $orgName;
    $result->save();
    if($result)
    {
      return response()->json([
        "status"        => "success",
        "message"        => "Footer Updated Successfully...",
        "footer"        => $orgName,
      ], 200);
    }
    else
    {
      return response()->json([
        "status"        => "failure",
        "message"        => "Problem Updating Footer...",
      ], 400);
    }
  }

  public function updateHeader($request)
  {
    $validator = Validator::make($request->all(), [
      'type'      => 'required',
      'orgName'   => 'required',
      'file'      => 'required|mimes:png|max:512|dimensions:max_width=256,max_height=256',
    ]);

    if ($validator->fails())
    {
      return response()->json([
        "status"        => "failure",
        "message"        => "Image must be .png only with maximum 256x256 dimensions.",
      ], 400);
    }

    $imageName = 'logo.png';  
    
    $request->file->move(public_path('assets/images/'), $imageName);

    $result = HeaderFooterText::find(1)->first();
    $result->header = $request->orgName;
    $result->logo   = 'assets/images/'.$imageName;
    $result->save();

    return response()->json([
      "status"        => "success",
      "message"        => "Data uploaded Successfully",
    ], 200);
  }

  public function getPrograms()
  {
    if($this->role == 'EADMIN')
    {
      $inst_uid = $this->uid;
      $inst_id  = $this->username;
      $result = User::find($inst_uid)->programs;
      if($result)
      {
        return response()->json([
          "status"        => "success",
          "data"          => new ProgramCollection($result),
        ], 200);
      }
      else
      {
        return response()->json([
          "status"        => "failure",
        ], 400);
      }
    }
    else
    {
      return response()->json([
        "status"        => "failure",
        "message"        => "Invalid Institute Id",
      ], 400);
    }
  }


  public function getAllPrograms()
  {
    if(Auth::user()->role == 'ADMIN')
    {
      $result = ProgramMaster::all();
    }
    else if(Auth::user()->role == 'EADMIN')
    {
      $result = ProgramMaster::where('inst_uid',Auth::user()->uid)->get();
    }
      if($result)
      {
        return response()->json([
          "status"        => "success",
          "data"          => new ProgramCollection($result),
        ], 200);
      }
      else
      {
        return response()->json([
          "status"        => "failure",
        ], 400);
      }
  }

  public function deleteProgram($request)
  {
    $id = $request->id;
    $result  =  ProgramMaster::find($id)->delete();

    return response()->json([
      "status"        => "success",
      "message"       => "Record Deleted Successfully..."
    ], 200);    
  }


  public function getUserPrograms($username)
  {
    $result = User::where('username',$username)->first();
    if($result)
    {
      if($result->role==='EADMIN')
      {
        $result1 = User::find($result->uid)->programs;
        if($result1)
        {
          return response()->json([
            "status"        => "success",
            "data"          => $result1,
          ], 200);
        }
        else
        {
          return response()->json([
            "status"        => "failure",
          ], 400);
        }
      }
      else
      {
        return response()->json([
          "status"        => "failure",
          "message"        => "Invalid Institute Id",
        ], 400);
      }
    }
    else
    {
      return response()->json([
        "status"        => "failure",
        "message"        => "Data not found",
      ], 400);
    }
  }

  public function getSubjects($program_id)
  {
    $result = ProgramMaster::find($program_id)->subjects;
    if($result)
      {
        return response()->json([
          "status"        => "success",
          "data"          => new PaperCollection($result),
        ], 200);
      }
      else
      {
        return response()->json([
          "status"        => "failure",
        ], 400);
      }
  }

  public function getExams($program_id)
  {
    //--------Get Distinct Paper id from Program Id-----------------------------------------
    $result = DB::select("select id from subject_master where program_id in ($program_id)");
    //--------------------------------------------------------------------------------------
    $paper_id_array = array();
    //----------------------push paper id in an array----------------------------------------
    foreach($result as $res)
    {
      array_push($paper_id_array,$res->id);
    }
    //---------------------------------------------------------------------------------------

    $result1 = CandTest::whereIn('paper_id',$paper_id_array)->groupBy('paper_id')->get();
    if($result1)
      {
        return response()->json([
          "status"        => "success",
          "data"          => new ExamCollection($result1),
        ], 200);
      }
      else
      {
        return response()->json([
          "status"        => "failure",
        ], 400);
      }
  }

  public function getAllUsers($role)
  {
    $result = User::where('role',$role)->get();
    if($result)
    {
      return response()->json([
        "status"        => "success",
        "data"          =>  $result
      ], 200);
    }
    else
    {
      return response()->json([
        "status"        => "failure",
      ], 400);
    }
  }

  public function getFilteredUsers($role,$instUid)
  {
    $result = User::where('role',$role)->where('uid',$instUid)->get();
    if($result)
    {
      return response()->json([
        "status"        => "success",
        "data"          =>  $result
      ], 200);
    }
    else
    {
      return response()->json([
        "status"        => "failure",
      ], 400);
    }
  }

  public function getFilteredUsersByInstCode($role,$inst_id)
  {
    if($role == 'CHECKER')
    {
      $result = DB::table('users')
      ->join('checker_subject_master', 'users.uid', '=', 'checker_subject_master.uid')
      ->join('subject_master', 'subject_master.id', '=', 'checker_subject_master.paperId')
      ->select(DB::raw("users.uid,users.username,users.name,users.type,users.inst_id,users.mobile,users.email,group_concat(concat(subject_master.id,'-',subject_master.paper_code,'-',subject_master.paper_name)) as subjects,users.origpass"))
      ->where('users.role', '=', $role)
      ->where('users.inst_id','=',$inst_id)
      ->groupBy('users.uid')
      ->paginate(50);
    }
    else if($role == 'PROCTOR')
    {
      $result = DB::table('users')
      ->join('proctor_subject_master', 'users.uid', '=', 'proctor_subject_master.uid')
      ->join('subject_master', 'subject_master.id', '=', 'proctor_subject_master.paperId')
      ->select(DB::raw("users.uid,users.username,users.name,users.type,users.inst_id,users.mobile,users.email,group_concat(concat(subject_master.id,'-',subject_master.paper_code,'-',subject_master.paper_name)) as subjects,users.origpass"))
      ->where('users.role', '=', $role)
      ->where('users.inst_id','=',$inst_id)
      ->groupBy('users.uid')
      ->paginate(50);
    }
    else
    {
      $result = User::where('role',$role)->where('inst_id',$inst_id)->paginate(50);
    }

    if($result)
    {
      return response($result, 200);
    }
    else
    {
      return response()->json([
        "status"        => "failure",
      ], 400);
    }
  }

  public function getPreviewQuestions($paper_id)
  {
    $result1= [];
    $result = QuestionSet::where('paper_id',$paper_id)->orderBy('qnid', 'ASC')->get();
    
    if($result && $result->count() > 0)
    {
      for($i=0;$i<sizeof($result);$i++)
      {
        $result1[$i]['id']        = $i+1;
        $result1[$i]['answered']  = 'unanswered';
        $result1[$i]['exam_id']   = 0;
        $result1[$i]['marks']     = $result[$i]['marks'];
        $result1[$i]['paper_id']  = $paper_id;
        $result1[$i]['program_id']= 0;
        $result1[$i]['qnid']      = $result[$i]['qnid'];
        $result1[$i]['qnid_sr']   = $i+1;
        $result1[$i]['stdanswer'] = null;
        $result1[$i]['question']  = $result[$i];
      }
      return response()->json([
        "status"        =>  "success",
        "data"          =>  $result1
      ], 200);
    }
    else
    {
      return response()->json([
        "status"        => "failure",
      ], 400);
    }
  }

  public function storeUsers($request)
  {
    $role     = $request->regType;
    $name     = $request->controllerName;
    $org      = $request->orgName;
    $email    = $request->email;
    $mobile   = $request->mobile;
    $password = $request->password;

    $current_time 			= Carbon::now();

    try
		{
      if($role == 'EADMIN')
      {
        $user = User::create([
          'username' 						=> $name,
          'inst_id'             => $name,
          'role' 						    => $role,
          'mobile'              => $mobile,
          'email'               => $email,
          'origpass'            => $password,
          'password'            => Hash::make($password),
          'status'              => 'ON',
          'college_name'        => $org,
          'name'                => $name,
          'regi_type'           => $role,
          'verified'            => 'verified',
          'created_at' 				  => $current_time,
        ]);
      }
      else
      {
        $user = User::create([
          'username' 						=> $name,
          'role' 						    => $role,
          'mobile'              => $mobile,
          'email'               => $email,
          'origpass'            => $password,
          'password'            => Hash::make($password),
          'status'              => 'ON',
          'college_name'        => $org,
          'name'                => $name,
          'regi_type'           => $role,
          'verified'            => 'verified',
          'created_at' 				  => $current_time,
        ]);
      }
		}
		catch(\Exception $e)
		{
      return response()->json([
        'status' 		=> 'failure',
        'message'   => 'Problem Inserting User in Database. Probably Duplicate Entry.'
      ],400);
		}

    if($user)
    {
      return response()->json([
        'status' 		=> 'success',
        'message'   => 'User Inserted Successfully.'
      ],200);
    }
    else
    {
      return response()->json([
        'status' 		=> 'failure',
        'message'   => 'Problem Inserting User in Database.'
      ],400);
    }
  }

  public function uploadUsers($request)
  {
    $validator = Validator::make($request->all(), [
      'role'      => 'required',
      'file'      => 'required|max:1024',
    ]);

    $extension = File::extension($request->file->getClientOriginalName());

    if ($validator->fails())
    {
      return response()->json([
        "status"          => "failure",
        "message"         => "Role of User and File for uploading is required with max file size 1 MB",
      ], 400);
    }


    if ($extension == "xlsx") 
    {
      $fileName           = 'users.xlsx';  
      $request->file->move(public_path('assets/tempfiles/'), $fileName);
      $reader             = IOFactory::createReader("Xlsx");
      $spreadsheet        = $reader->load(public_path('assets/tempfiles/').$fileName);
      $current_time 			= Carbon::now();
      $highestRow         = $spreadsheet->getActiveSheet()->getHighestRow();

      for($i=2;$i<=$highestRow;$i++)
      {
        $username       =   $spreadsheet->getActiveSheet()->getCellByColumnAndRow(1, $i)->getValue();
        $role           =   $spreadsheet->getActiveSheet()->getCellByColumnAndRow(2, $i)->getValue();
        $orgName        =   $spreadsheet->getActiveSheet()->getCellByColumnAndRow(3, $i)->getValue();
        $email          =   $spreadsheet->getActiveSheet()->getCellByColumnAndRow(4, $i)->getValue();
        $mobile         =   $spreadsheet->getActiveSheet()->getCellByColumnAndRow(5, $i)->getValue();
        $origpassword   =   $spreadsheet->getActiveSheet()->getCellByColumnAndRow(6, $i)->getValue();
        $password       =   Hash::make($origpassword);
        $region         =   '';

        if($role == 'EADMIN')
        {
          $region = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(7, $i)->getValue();
        }

        try
        {
          if($role == 'EADMIN')
          {
            $user = User::create([
              'username' 						=> $username,
              'inst_id'             => $username,
              'region'              => $region,
              'role' 						    => $role,
              'mobile'              => $mobile,
              'email'               => $email,
              'origpass'            => $origpassword,
              'password'            => $password,
              'status'              => 'ON',
              'college_name'        => $orgName,
              'name'                => $username,
              'regi_type'           => $role,
              'verified'            => 'verified',
              'created_at' 				  => $current_time,
            ]);
          }
          else
          {
            $user = User::create([
              'username' 						=> $username,
              'role' 						    => $role,
              'mobile'              => $mobile,
              'email'               => $email,
              'origpass'            => $origpassword,
              'password'            => $password,  
              'college_name'        => $orgName,
              'name'                => $username,
              'regi_type'           => $role,
              'verified'            => 'verified',
              'created_at' 				  => $current_time,
            ]);
          }
        }
        catch(\Exception $e)
        {
          return response()->json([
            'status' 		=> 'failure',
            'message'   => 'Problem Inserting User in Database.Probably Duplicate Username or Mobile Number. All Users till row number '.$i.' in Excel file are Inserted Successfully',
            'row'       =>  $i
          ],400);
        }
      }
      return response()->json([
        'status' 		=> 'success',
        'message'   => 'Users Uploaded Successfully...',
        'row'       =>  $i
      ],200);
    }
    else 
    {
      return response()->json([
        "status"          => "failure",
        "message"         => "File must be .xlsx only with maximum 1 MB  of Size.",
      ], 400);
    }
  }

  public function deleteUser($id)
  {
    $result = User::find($id)->delete();
    DB::commit();
    return response()->json([
      "status"          => "success",
      "message"         => "User Deleted Successfully.",
    ], 200);
  }

  public function updateUser($id,$request)
  {
    $result = User::find($id);
    $res = $result->update($request->all());
    
    if($request->password != '')
    {
      $result->password = Hash::make($request->password);
      $result->save();
    }

    return response()->json([
      "status"          => "success",
      "message"         => "User Updated Successfully.",
    ], 200);
  }

  public function storeProgram($request)
  {
    $progCode           = $request->progCode;
    $progName           = $request->progName;
    $instId             = $request->instId;
    $current_time 			= Carbon::now();

    $count = ProgramMaster::where('program_code',$progCode)->where('inst_uid',$instId)->get()->count();
    
    if(!$count)
    {
      try
      {
        $prog = ProgramMaster::create([
          'program_code' 	=> $progCode,
          'program_name'  => $progName,
          'inst_uid'      => $instId,
          'created_at' 	  => $current_time,
        ]);

       
        $instProg = InstPrograms::create([
            'program_id'  => $prog->id,
            'inst_uid'    => $instId,
            'created_at' 	  => $current_time,
        ]);
       

        return response()->json([
          'status' 		    => 'success',
          'message'       => 'Program Added Successfully...',
        ],200);
      }
      catch(\Exception $e)
      {
        
        return response()->json([
          'status' 		    => 'failure',
          'message'       => 'Problem Inserting Program in Database.',
        ],400);
      }
    }
    else
    {
      return response()->json([
        'status' 		    => 'failure',
        'message'       => 'This Program Code:'.$progCode.' already exist in database (Already used by your Institute). In order to insert this record please change your program code and try again...' ,
      ],400);
    }
  }

  public function uploadProgram($request)
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

    DB::beginTransaction();
    if ($extension == "xlsx") 
    {
      $fileName           = 'programs.xlsx';  
      $request->file->move(public_path('assets/tempfiles/'), $fileName);
      $reader             = IOFactory::createReader("Xlsx");
      $spreadsheet        = $reader->load(public_path('assets/tempfiles/').$fileName);
      $current_time 			= Carbon::now();
      $highestRow         = $spreadsheet->getActiveSheet()->getHighestRow();

      for($i=2;$i<=$highestRow;$i++)
      {
        $progCode         =   $spreadsheet->getActiveSheet()->getCellByColumnAndRow(1, $i)->getValue();
        $progName         =   $spreadsheet->getActiveSheet()->getCellByColumnAndRow(2, $i)->getValue();
        $instID           =   $spreadsheet->getActiveSheet()->getCellByColumnAndRow(3, $i)->getValue();
      
        $instUid          = User::where('username',$instID)->first()->uid;
  
        try
        {
            $prog = ProgramMaster::create([
              'program_code'    => $progCode,
              'program_name'    => $progName,
              'inst_uid'        => $instUid,
              'created_at' 			=> $current_time,
            ]);

            $instProg = InstPrograms::create([
                'program_id'  => $prog->id,
                'inst_uid'    => $instUid,
                'created_at' 	  => $current_time,
            ]);
        }
        catch(\Exception $e)
        {
          DB::rollback();
          return response()->json([
            'status' 		=> 'failure',
            'message'   => 'Problem Inserting Programs in Database.Probably Duplicate Program Code Entry for your Institute. All Programs till row number '.$i.' in Excel file are Inserted Successfully',
            'row'       =>  $i
          ],400);
        }
        DB::commit();
      }
      return response()->json([
        'status' 		=> 'success',
        'message'   => 'Programs Uploaded Successfully...',
        'row'       =>  $i
      ],200);
    }
    else 
    {
      return response()->json([
        "status"          => "failure",
        "message"         => "File must be .xlsx only with maximum 1 MB  of Size.",
      ], 400);
    }
  }

  public function getByInstPrograms($instUid)
  {
      $inst_uid = $instUid;
      $inst_id  = $this->username;
      $result = User::find($inst_uid)->programs;
      if($result)
      {
        return response()->json([
          "status"        => "success",
          "data"          => new ProgramCollection($result),
        ], 200);
      }
      else
      {
        return response()->json([
          "status"        => "failure",
        ], 400);
      }
  }

  public function getByInstIdPrograms($instId)
  {
      $result = User::where('username',$instId)->first();
      if($result)
      {
        $instUid = $result->uid;
        $result = User::find($instUid)->programs;
        if($result)
        {
          return response()->json([
            "status"        => "success",
            "data"          => $result,
          ], 200);
        }
        else
        {
          return response()->json([
            "status"        => "failure",
          ], 400);
        }
      }
      else
      {
        return response()->json([
          "status"        => "failure",
        ], 400);
      }
  }

  public function storeSubjects($request)
  {
    $paperCode  = $request->paperCode;
    $paperName  = $request->paperName;
    $programId  = $request->programId;
    $instId     = $request->instId;
    $semester   = $request->semester;
    $current_time 			= Carbon::now();

    $count = SubjectMaster::where('paper_code',$paperCode)->where('inst_uid',$instId)->get()->count();

    if(!$count)
    {
      try
      {
        $result = SubjectMaster::create([
          'paper_code'    =>  $paperCode,
          'paper_name'    =>  $paperName,
          'program_id'    =>  $programId,
          'inst_uid' 			=>  $instId,
          'semester'      =>  $semester,
          'created_at'    =>  $current_time
        ]);

        return response()->json([
          'status' 		=> 'success',
          'message'   => 'Subject Added Successfully...',
        ],200);
      }
      catch(\Exception $e)
      {
            return response()->json([
              'status' 		=> 'failure',
              'message'   => 'Problem Inserting Subjects in Database.',
            ],400);
      }
    }
    else
    {
      return response()->json([
        'status' 		=> 'failure',
        'message'   => 'The Paper Code: '.$paperCode.' already Exists in selected institute. Duplicate Entry of Subject Code in same institute is not allowed...',
      ],400);
    }
  }


  public function uploadSubjects($request)
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
      $fileName           = 'subjects.xlsx';  
      $request->file->move(public_path('assets/tempfiles/'), $fileName);
      $reader             = IOFactory::createReader("Xlsx");
      $spreadsheet        = $reader->load(public_path('assets/tempfiles/').$fileName);
      $current_time 			= Carbon::now();
      $highestRow         = $spreadsheet->getActiveSheet()->getHighestRow();

      for($i=2;$i<=$highestRow;$i++)
      {
        $paperCode        =   $spreadsheet->getActiveSheet()->getCellByColumnAndRow(1, $i)->getValue();
        $paperName        =   $spreadsheet->getActiveSheet()->getCellByColumnAndRow(2, $i)->getValue();
        $instCode         =   $spreadsheet->getActiveSheet()->getCellByColumnAndRow(3, $i)->getValue();
        $instId           =   User::where('username',$instCode)->first()->uid;
        $programCode      =   $spreadsheet->getActiveSheet()->getCellByColumnAndRow(4, $i)->getValue();
        $rrr              =   ProgramMaster::where('program_code',$programCode)->where('inst_uid',$instId)->first();
        $programId        =   $rrr->id;
        $semester         =   $spreadsheet->getActiveSheet()->getCellByColumnAndRow(5, $i)->getValue();

        $count = SubjectMaster::where('paper_code',$paperCode)->where('inst_uid',$instId)->get()->count();

        if(!$count)
        {
          try
          {
            $result = SubjectMaster::create([
              'paper_code'    =>  $paperCode,
              'paper_name'    =>  $paperName,
              'program_id'    =>  $programId,
              'inst_uid' 			=>  $instId,
              'semester'      =>  $semester,
              'created_at'    =>  $current_time
            ]);
      
          }
          catch(\Exception $e)
          {
            return response()->json([
              'status' 		=> 'failure',
              'message'   => 'Problem Inserting Subjects in Database. All Subjects till row number '.$i.' in Excel file are Inserted Successfully',
              'row'       =>  $i
            ],400);
          }
        }
        else
        {
          return response()->json([
            'status' 		=> 'failure',
            'message'   => 'Problem Inserting Subjects in Database.Probably Duplicate Entry. All Subjects till row number '.$i.' in Excel file are Inserted Successfully',
            'row'       =>  $i
          ],400);
        }
      }
      return response()->json([
        'status' 		=> 'success',
        'message'   => 'Subjects Uploaded Successfully...',
        'row'       =>  $i
      ],200);
    }
    else 
    {
      return response()->json([
        "status"          => "failure",
        "message"         => "File must be .xlsx only with maximum 1 MB  of Size.",
      ], 400);
    }
  }

  public function getAllSubjects()
  {
    $result = SubjectMaster::all();

    return response()->json([
      'status' 		=> 'success',
      'data'      =>  new PaperCollection($result),
    ],200);
  }

  public function getSubjectsByInstUid($instUid,$mode)
  {
    if($mode == '' || $mode == null)
    {
      $result = SubjectMaster::where('inst_uid',$instUid)->where('paper_code','not like','GENERICTEST')->get();
    }
    else
    {
      $result = SubjectMaster::where('inst_uid',$instUid)->whereIn('exam_mode',['subjective','both'])->where('paper_code','not like','GENERICTEST')->get();
    }

    return response()->json([
      'status' 		=> 'success',
      'data'      =>  new PaperCollection($result),
    ],200);
  }

  public function delSubject($id)
  {
    $result = SubjectMaster::find($id)->delete();

    return response()->json([
      'status' 		=> 'success',
      'message'   => 'Record Deleted Successfully...'
    ],200);
  }

  public function storeStudentUsers($request)
  {
    $enrollno        = $request->username;
    $name            = $request->name;
    $instId          = $request->instId;
    $programId       = $request->programId;
    $semester        = $request->semester;
    $mobile          = $request->mobile;
    $email           = $request->email;
    $password        = $request->password;
    $ph              = $request->ph == 'PH' ? $request->ph : null;
    $current_time 	 = Carbon::now();

    $res = User::where('username',$instId)->first();
    $region = $res->region;
    
    try
    {
      $result = User::create([
        'username'    =>  $enrollno,
        'inst_id'     =>  $instId,
        'region'      =>  $region,
        'course_code' =>  $programId,
        'semester'    =>  $semester,
        'mobile'      =>  $mobile,
        'email'       =>  $email,
        'password'    =>  Hash::make($password),
        'origpass'    =>  $password,
        'role'        =>  'STUDENT',
        'status'      =>  'ON',
        'verified'    =>  'verified',
        'name'        =>  $name,
        'regi_type'   =>  'STUDENT',
        'ph'          =>  $ph,
        'created_at'  =>  $current_time
      ]);
    }
    catch(\Exception $e)
    {
        return response()->json([
          'status' 		=> 'failure',
          'message'   => 'Problem Inserting Student in Database.Probably Duplicate Entry.'
        ],400);
    }
      return response()->json([
        'status' 		=> 'success',
        'message'   => 'Student Added Successfully...',
      ],200);
  }

  public function uploadStudents($request)
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
      $fileName           = 'students.xlsx';  
      $request->file->move(public_path('assets/tempfiles/'), $fileName);
      $reader             = IOFactory::createReader("Xlsx");
      $spreadsheet        = $reader->load(public_path('assets/tempfiles/').$fileName);
      $current_time 			= Carbon::now();
      $highestRow         = $spreadsheet->getActiveSheet()->getHighestRow();

      for($i=2;$i<=$highestRow;$i++)
      {
        $enrollno        = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(1, $i)->getValue();
        $name            = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(2, $i)->getValue();
        $instId          = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(3, $i)->getValue();
        $programId       = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(4, $i)->getValue();
        $semester        = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(5, $i)->getValue();
        $mobile          = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(6, $i)->getValue();
        $email           = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(7, $i)->getValue();
        $password        = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(8, $i)->getValue();
        $ph              = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(9, $i)->getValue();

        $ph              = ($ph == 'PH') ? $ph : null;

        $current_time 	 = Carbon::now();

        $res = User::where('username',$instId)->first();
        $region = $res->region;
        
        try
        {
          $result = User::create([
            'username'    =>  $enrollno,
            'inst_id'     =>  $instId,
            'region'      =>  $region,
            'course_code' =>  $programId,
            'semester'    =>  $semester,
            'mobile'      =>  $mobile,
            'email'       =>  $email,
            'password'    =>  Hash::make($password),
            'origpass'    =>  $password,
            'role'        =>  'STUDENT',
            'status'      =>  'ON',
            'verified'    =>  'verified',
            'name'        =>  $name,
            'regi_type'   =>  'STUDENT',
            'ph'          =>  $ph,
            'created_at'  =>  $current_time
          ]);
        }
        catch(\Exception $e)
        {
            return response()->json([
              'status' 		=> 'failure',
              'message'   => 'Problem Inserting Student in Database.Probably Duplicate Entry.All Students till row number '.$i.' in Excel file are Inserted Successfully',
              'row'       =>  $i
            ],400);
        }
      }
      return response()->json([
        'status' 		=> 'success',
        'message'   => 'Students Uploaded Successfully...',
        'row'       =>  $i
      ],200);
    }
    else 
    {
      return response()->json([
        "status"          => "failure",
        "message"         => "File must be .xlsx only with maximum 1 MB  of Size.",
      ], 400);
    }
  }

  public function uploadStudSubjectMapping($request)
  {
    DB::beginTransaction();

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
      $fileName           = 'studSubjectAlloc.xlsx';  
      $request->file->move(public_path('assets/tempfiles/'), $fileName);
      $reader             = IOFactory::createReader("Xlsx");
      $spreadsheet        = $reader->load(public_path('assets/tempfiles/').$fileName);
      $current_time 			= Carbon::now();
      $highestRow         = $spreadsheet->getActiveSheet()->getHighestRow();
      
      for($i=2;$i<=$highestRow;$i++)
      {
        $enrollno        = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(1, $i)->getValue();
        $instId          = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(2, $i)->getValue();
        $paper_code      = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(3, $i)->getValue();
        $current_time 	 = Carbon::now();


        $res = User::where('username',$enrollno)->where('inst_id',$instId)->first();
        if($res)
        {
          $uid = $res->uid;
        }
        else
        {
          return response()->json([
            'status' 		=> 'failure',
            'message'   => 'Student Enrollment Number on Row '.$i.' in Excel file is not available in Users Table. All rows till this are uploaded successfully',
            'row'       =>  $i
          ],400);
        }
      
        if(Auth::user()->role == 'ADMIN')
        {
          $res1= User::where('username',$instId)->first();
          if(!$res1)
          {
            return response()->json([
              'status' 		=> 'failure',
              'message'   => 'Institute with Institute Code on Row '.$i.' in Excel file is not available in Users Table. All rows till this are uploaded successfully',
              'row'       =>  $i
            ],400);
          }
          $inst_uuid = $res1->uid;
          $res2 = SubjectMaster::where('paper_code',$paper_code)->where('inst_uid',$inst_uuid)->first();
        }
        else if(Auth::user()->role == 'EADMIN')
        {
          $res2 = SubjectMaster::where('paper_code',$paper_code)->where('inst_uid',Auth::user()->uid)->first();
        }

        if($res2)
        {
          $paperId = $res2->id;
          $programId = $res2->program_id;
          $static_assign = $res2->static_assign;
        

          $totalMarks = $res2->marks;
          $totalQuestions=$res2->questions;

          /*$res3 = ProgramMaster::find($programId);
          if(!$res3)
          {
            return response()->json([
              'status' 		=> 'failure',
              'message'   => 'Invalid Program Id for Subject on Row '.$i.'. All rows till this are uploaded successfully',
              'row'       =>  $i
            ],400);
          }*/
          
          //---------------------Check whether Question set contains questions for this Paper ID-----------
          $res4 = TopicMaster::where('paper_id',$paperId)->get();
          if($res4)
          { 
            
            $fetchQuery = '';
            $actualMarks = 0;
            foreach($res4 as $record)
            {
              $topic    = $record->topic;
              $subtopic = $record->subtopic;
              $questType= $record->questType;
              $questMode= $record->questMode;
              $quest    = $record->questions;
              $mrk      = $record->marks;
              $mmarks   = $mrk * $quest;

              $actualMarks = $actualMarks + $mmarks;

              $fetchQuery = $fetchQuery."(SELECT * FROM  question_set WHERE trim(paper_uid)=trim('$paperId') AND topic = '$topic' AND  subtopic =  '$subtopic' AND difficulty_level = '$questType' AND quest_type='$questMode' AND marks = '$mrk' ORDER BY RAND( )  LIMIT $quest) UNION ";
            }
            
            $fetchQuery = rtrim($fetchQuery," UNION ");
            
            try
            {
              $res5 = DB::select($fetchQuery);
            }
            catch(\Exception $e)
            {
              return response()->json([
                    'status' 		=> 'failure',
                    'message'   => 'Problem Selecting Questions from Database.All Allocations till row number '.$i.' in Excel file are Inserted Successfully',
                    'row'       =>  $i
                  ],400);
            }

          
            $res5dummy = $res5;
            if($res5)
            {
              
                $actualQuestcount = sizeof($res5);
                
                if($actualQuestcount != $totalQuestions)
                {
                  return response()->json([
                    'status' 		=> 'failure',
                    'message'   => 'For row '.$i.' the Question set is not properly configured according to Test Master. Number of Questions Mismatch. Total Questions Should Be:'.$totalQuestions.' But '.$actualQuestcount.' found.All rows till this are uploaded successfully. Query:'.$fetchQuery ,
                    'row'       =>  $i
                  ],400);
                }
                if($totalMarks != $actualMarks)
                {
                  return response()->json([
                    'status' 		=> 'failure',
                    'message'   => 'For row '.$i.' the Question set is not properly configured according to Test Master. Topic Wise Marks Mismatch.All rows till this are uploaded successfully',
                    'row'       =>  $i
                  ],400);
                }

                try
                {
                  $result = CandTest::create([
                    'stdid'       =>  $uid,
                    'inst'        =>  $instId,
                    'paper_id'    =>  $paperId,
                    'program_id'  =>  $programId,
                    'created_at'  =>  $current_time 
                  ]);
                  DB::commit();
                }
                catch(\Exception $e)
                {
                  return response()->json([
                    'status' 		=> 'failure',
                    'message'   => 'Problem Inserting Student Subject Allocation in Database.Probably Duplicate Entry.All Allocations till row number '.$i.' in Excel file are Inserted Successfully',
                    'row'       =>  $i
                  ],400);
                }
                
                if($static_assign)
                {
                  //------------------Insert Questions into Candidate Questions----------------------
                  $j = 1;
                  $k = 0;
                  $values = array();
                  foreach ($res5dummy as $question)
                  {
                    $values[$k++] = array(
                      'exam_id' 					=> $result->id,
                      'stdid' 						=> $uid,
                      'inst' 							=> $instId,
                      'paper_id' 					=> $paperId,
                      'program_id' 				=> $programId,
                      'qnid' 							=> $question->qnid,
                      'qtopic' 						=> $question->topic,
                      'qtype' 						=> $question->difficulty_level,
                      'questMode'					=> $question->quest_type,
                      'answered' 					=> 'unanswered',
                      'cans' 							=> $question->coption,
                      'marks' 						=> $question->marks,
                      'ip' 								=> request()->ip(),
                      'entry_on' 					=> $current_time,
                      'qnid_sr' 					=> $j++
                    );
                  }

                  try
                  {
                    $inserted = DB::table('cand_questions')->insert($values);
                    DB::commit();
                    $values = null;
                  }
                  catch(\Exception $e)
                  {
                    return response()->json([
                     'status' 		=> 'failure',
                      'message'   => 'Problem Inserting Student Questions in Database.All Records till row number '.$i.' in Excel file are Inserted Successfully',
                      'row'       =>  $i
                    ],400);
                  }
                 
                  
                }
                //--------------------------------------------------------------------------
            }
            else
            {
              return response()->json([
                'status' 		=> 'failure',
                'message'   => 'Problem Inserting Student Questions in Database.All Records till row number '.$i.' in Excel file are Inserted Successfully. Query:'.$fetchQuery,
                'row'       =>  $i
              ],400);
            }
          }
          else
          {
            return response()->json([
              'status' 		=> 'failure',
              'message'   => 'Topic entry for Subject on Row '.$i.'. is not done.All rows till this are uploaded successfully',
              'row'       =>  $i
            ],400);
          }
          //-----------------------------------------------------------------------------------------------
        }
        else
        {
          return response()->json([
            'status' 		=> 'failure',
            'message'   => 'Subject with Paper Code on Row '.$i.' in Excel file is not available in Subject Master Table. All rows till this are uploaded successfully',
            'row'       =>  $i
          ],400);
        }
      }
      return response()->json([
        'status' 		=> 'success',
        'message'   => 'Student Subject Allocation Uploaded Successfully...',
      ],200);
    }
    else 
    {
      return response()->json([
        "status"          => "failure",
        "message"         => "File must be .xlsx only with maximum 1 MB  of Size.",
      ], 400);
    }
  }

  public function getAllExams()
  {
    $exams 	= CandTest::all();
		if($exams)
		{
			return new ExamCollection($exams);
		}
		else
		{
			return json_encode([
				'status' => 'failure'
			],200);
		}
  }

  public function getFilteredExams($instId)
  {
    $exams 	= CandTest::where('inst',$instId)->paginate(50);
		if($exams)
		{
			return new ExamCollection($exams);
		}
		else
		{
			return json_encode([
				'status' => 'failure'
			],200);
		}
  }

  public function delExam($id)
  {
    $result = CandTest::find($id)->delete();

    $result = CandQuestion::where('exam_id',$id)->delete();

    return response()->json([
      'status' 		=> 'success',
      'message'   => 'Student Subject Allocation Deleted Successfully...',
    ],200);
  }

  public function storeTopic($request)
  {
    $paperId        = $request->paperId;
    $topic          = $request->topic;
    $subTopic       = $request->subTopic;
    $questType      = $request->questType;
    $questions      = $request->questions;
    $marks          = $request->marks;
    $questMode      = $request->questMode;

    if($subTopic == '' || $subTopic == NULL)
    {
      $subTopic = 0;
    }

    $current_time   = Carbon::now();

    $values = [
      'paper_id'    => $paperId,
      'topic'       => $topic,
      'subtopic'    => $subTopic,
      'questMode'   => $questMode,
      'questType'   => $questType,
      'questions'   => $questions,
      'marks'       => $marks,
      'created_at'  => $current_time,
    ];

    $result         = TopicMaster::create($values);

    return response()->json([
      'status' 		=> 'success',
      'message'   => 'Topic Added Successfully...',
    ],200);
  }

  public function storeTopicUpload($request)
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
      $fileName           = 'topicUpload.xlsx';  
      $request->file->move(public_path('assets/tempfiles/'), $fileName);
      $reader             = IOFactory::createReader("Xlsx");
      $spreadsheet        = $reader->load(public_path('assets/tempfiles/').$fileName);
      $current_time 			= Carbon::now();
      $highestRow         = $spreadsheet->getActiveSheet()->getHighestRow();
      //--------------Get Topic Sub Topic Array and check with excel topic subtopic--------------
      $topicArray         = array();
      $subtopicArray      = array();

      $paperCodeOld       = '';
           
      //-----------------------------------------------------------------------------------------

      for($i=2;$i<=$highestRow;$i++)
      {
        $paperCode        = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(1, $i)->getValue();

        if($paperCodeOld != $paperCode)
        {
          $rrr            = QuestionSet::select(DB::raw('group_concat(distinct topic) as topic, group_concat(distinct subtopic) as subtopic'))->where('paper_id',$paperCode)->groupBy('paper_id')->first();

          $topicArray     = explode(',',$rrr->topic);
          $subtopicArray  = explode(',',$rrr->subtopic);

          $paperCodeOld   = $paperCode;
        }

        $instCode         = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(2, $i)->getValue();
        $instId           = User::where('username',$instCode)->where('role','EADMIN')->first()->uid;
        $topic            = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(3, $i)->getValue();

        if (!in_array($topic, $topicArray))
        {
          return response()->json([
            'status' 		=> 'failure',
            'message'   => 'Invalid Topic Number on row'.$i,
            'row'       =>  $i
          ],400);
        }

        $subTopic         = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(4, $i)->getValue();

        if (!in_array($subTopic, $subtopicArray))
        {
          return response()->json([
            'status' 		=> 'failure',
            'message'   => 'Invalid Subotopic Number on row'.$i,
            'row'       =>  $i
          ],400);
        }

        $questMode        = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(5, $i)->getValue();
        $questType        = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(6, $i)->getValue();
        $questions        = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(7, $i)->getValue();
        $marks            = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(8, $i)->getValue();

        if($subTopic == '' || $subTopic == NULL)
        {
          $subTopic = 0;
        }

        $current_time 	 = Carbon::now();

        $res = SubjectMaster::where('paper_code',$paperCode)->where('inst_uid',$instId)->first();
        if($res)
        {
          $paperId = $res->id;
          try
          {
            $result         = TopicMaster::create([
              'paper_id'    => $paperId,
              'topic'       => $topic,
              'subtopic'    => $subTopic,
              'questMode'   => $questMode,
              'questType'   => $questType,
              'questions'   => $questions,
              'marks'       => $marks,
              'created_at'  => $current_time,
            ]); 
          }
          catch(\Exception $e)
          {
            return response()->json([
              'status' 		=> 'failure',
              'message'   => 'Problem Inserting Topic Data in Database.Probably Duplicate Entry.All Topics Data till row number '.$i.' in Excel file are Inserted Successfully',
              'row'       =>  $i
            ],400);
          }
        }
        else
        {
          return response()->json([
            'status' 		=> 'failure',
            'message'   => 'Problem Inserting Topic Data in Database on row '.$i,
          ],400);
        }
      }
      return response()->json([
        'status' 		=> 'success',
        'message'   => 'Topic Data Uploaded Successfully...',
      ],200);
    }
    else 
    {
      return response()->json([
        "status"          => "failure",
        "message"         => "File must be .xlsx only with maximum 1 MB  of Size.",
      ], 400);
    }
  }

  public function getTopicDataSingle($paperId)
  {
    $result = TopicMaster::where('paper_id',$paperId)->get();

    if($result)
      {
        return response()->json([
          "status"        => "success",
          "data"          => new TopicCollection($result),
        ], 200);
      }
      else
      {
        return response()->json([
          "status"        => "failure",
        ], 400);
      }    
  }

  public function delTopicData($id)
  {
    $result = TopicMaster::find($id)->delete();

    return response()->json([
      "status"        => "success",
      "message"       => "Topic Data Deleted Successfully...",
    ], 200);
  }

  public function updateTestSubjects($id,$request)
  {
    $fromDate     = new \DateTime($request->from_date);
    $toDate       = new \DateTime($request->to_date);
    
    $result = SubjectMaster::find($id)->update([
      'exam_name' => $request->exam_name,
      'marks'     => $request->marks,
      'questions' => $request->questions,
      'durations' => $request->durations,
      'slot'      => $request->slot,
      'from_date' => $fromDate->format('Y-m-d H:i:s.u'),
      'to_date'   => $toDate->format('Y-m-d H:i:s.u'),
      'exam_mode' => $request->testMode
    ]);
    if($result)
    {
      return response()->json([
        "status"        => "success",
        "message"       => "Test Data updated Successfully...",
      ], 200);
    }
    else
    {
      return response()->json([
        "status"        => "failure",
        "message"       => "Problem updating Test Data...",
      ], 400);
    }
  }

  public function uploadTestsSubjects($request)
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
      $fileName           = 'testsUpload.xlsx';  

      $tz_from            = $request->timeZone; // Local Timezone
      $tz_to              = 'UTC'; // UTC Time Zone

      $request->file->move(public_path('assets/tempfiles/'), $fileName);
      $reader             = IOFactory::createReader("Xlsx");
      $spreadsheet        = $reader->load(public_path('assets/tempfiles/').$fileName);
      $current_time 			= Carbon::now();
      $highestRow         = $spreadsheet->getActiveSheet()->getHighestRow();

      for($i=2;$i<=$highestRow;$i++)
      {
        $paperCode  = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(1, $i)->getValue();
        $instId     = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(2, $i)->getValue();
        $instUid    = User::where('username',$instId)->where('role','EADMIN')->first()->uid;
        $exam_name  = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(3, $i)->getValue();
        $marks      = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(4, $i)->getValue();
        $questions  = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(5, $i)->getValue();
        $durations  = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(6, $i)->getValue();
        $slot       = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(7, $i)->getValue();
        //------------------------Convert Date from Local to UTC Format--------------------------------------
        $from_date  = new \DateTime($spreadsheet->getActiveSheet()->getCellByColumnAndRow(8, $i)->getValue(), new \DateTimeZone($tz_from));
        $from_date->setTimezone(new \DateTimeZone($tz_to));

        $to_date    = new \DateTime($spreadsheet->getActiveSheet()->getCellByColumnAndRow(9, $i)->getValue(), new \DateTimeZone($tz_from));
        $to_date->setTimezone(new \DateTimeZone($tz_to));
        //--------------------------------------------------------------------------------------------------
        $mode       = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(10, $i)->getValue();
        $current_time 	  = Carbon::now();
        
          $result = SubjectMaster::where('paper_code','like',$paperCode)->where('inst_uid',$instUid)->update([
            'exam_name' => $exam_name,
            'marks'     => $marks,
            'questions' => $questions,
            'durations' => $durations,
            'slot'      => $slot,
            'from_date' => $from_date->format("Y-m-d H:i:s.u"),
            'to_date'   => $to_date->format("Y-m-d H:i:s.u"),
            'exam_mode' => $mode
          ]);
        
        
      }
      return response()->json([
        'status' 		=> 'success',
        'message'   => 'Test Data Uploaded Successfully...',
      ],200);
    }
    else 
    {
      return response()->json([
        "status"          => "failure",
        "message"         => "File must be .xlsx only with maximum 1 MB  of Size.",
      ], 400);
    }
  }

  public function clearTestsSubjects($id)
  {
    $result = SubjectMaster::find($id)->update([
      'exam_name' => NULL,
      'marks' => 0,
      'questions'=> 0,
      'durations' => 0,
      'from_date' => NULL,
      'to_date' => NULL,
    ]);

    return response()->json([
      "status"          => "success",
      "message"         => "Data Cleared Successfully.",
    ], 200);
  }

  public function updateConfigSubject($id,$request)
  {
    $result       = SubjectMaster::find($id);
    $exam_mode    = $result->exam_mode;
    $score_view   = $result->score_view;

    if($exam_mode == 'subjective' || $exam_mode == 'both')
    {
      $score_view       =   0;
    }

    $result->score_view         =   $score_view ? $score_view : '0';
    $result->review_question    =   $request->review_question ? $request->review_question : '0';
    $result->proctoring         =   $request->proctoring ? $request->proctoring : '0';
    $result->photo_capture      =   $request->photo_capture ? $request->photo_capture : '0';
    $result->capture_interval   =   $request->capture_interval ? $request->capture_interval : '0';
    $result->negative_marking   =   $request->negative_marking ? $request->negative_marking : '0';
    $result->negative_marks     =   $request->negative_marks ? $request->negative_marks : '0';
    $result->time_remaining_reminder  =   $request->time_remaining_reminder;
    $result->exam_switch        =   $request->exam_switch;
    $result->exam_switch_alerts =   $request->exam_switch_alerts ? $request->exam_switch_alerts : '99999';
    $result->option_shuffle     =   $request->option_shuffle ? $request->option_shuffle : '0';
    $result->question_marks     =   $request->question_marks ? $request->question_marks :  '0';
    $result->ph_time            =   $request->ph_time ? $request->ph_time : '0';
    $result->static_assign      =   $request->static_assign ? $request->static_assign : '0';
    $result->questwisetimer     =   $request->questwisetimer ? $request->questwisetimer : '0';
    $result->secperquest        =   $request->secperquest ? $request->secperquest : '0';

    $result->save();

    return response()->json([
      "status"          => "success",
      "message"         => "Exam Configuration Uploaded Successfully.",
    ], 200);
  }

  public function uploadProgInst($request)
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
      $fileName           = 'InstProgramAllocation.xlsx';  

      $request->file->move(public_path('assets/tempfiles/'), $fileName);
      $reader             = IOFactory::createReader("Xlsx");
      $spreadsheet        = $reader->load(public_path('assets/tempfiles/').$fileName);
      $current_time 			= Carbon::now();
      $highestRow         = $spreadsheet->getActiveSheet()->getHighestRow();

      for($i=2;$i<=$highestRow;$i++)
      {
        $progCode   = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(1, $i)->getValue();
        $program_id = ProgramMaster::where('program_code',$progCode)->first()->id;
        $instCode   = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(2, $i)->getValue();
        $inst_uid   = User::where('username',$instCode)->first()->uid;
        
        $current_time 	  = Carbon::now();
        
        try
        {
          $result = InstPrograms::create([
            'program_id' => $program_id,
            'inst_uid'     => $inst_uid,
            'created_at'  => $current_time,
          ]);
        }
        catch(\Exception $e)
        {
          return response()->json([
            'status' 		=> 'failure',
            'message'   => 'Problem Inserting Program Institute Mapping in Database.Probably Duplicate Entry.All Program Institute Mappings till row number '.$i.' in Excel file are Inserted Successfully',
            'row'       =>  $i
          ],400);
        }
      }
      return response()->json([
        'status' 		=> 'success',
        'message'   => 'Program Institute Mapping Uploaded Successfully...',
      ],200);
    }
    else 
    {
      return response()->json([
        "status"          => "failure",
        "message"         => "File must be .xlsx only with maximum 1 MB  of Size.",
      ], 400);
    }
  }

  function getAllProgInsts()
  {
    $result = InstPrograms::all();

    if($result)
    {
      return response()->json([
        "status"        => "success",
        "data"          => new InstProgramCollection($result),
      ], 200);
    }
    else
    {
      return response()->json([
        "status"        => "failure",
      ], 400);
    }
  }

  public function delProgInst($id)
  {
    $result = InstPrograms::find($id)->delete();

    return response()->json([
      "status"            => "success",
      "message"           => 'Record Deleted Successfully...',
    ], 200);
  }

  
  public function getSubjectById($id)
  {
    $result = SubjectMaster::find($id);
    if($result)
    {
      return response()->json([
        "status"        => "success",
        "data"          => new PaperResource($result),
      ], 200);
    }
    else
    {
      return response()->json([
        "status"        => "failure",
      ], 400);
    }
  }

  public function specificationCompare()
  {
    $add ='';
    if(Auth::user()->role == 'EADMIN')
    {
      $ress = SubjectMaster::select('id')->where('inst_uid',Auth::user()->uid)->get();
      $str ='';
      foreach($ress as $res)
      {
        $str = $str .$res->id.',';
      }
      $str = rtrim($str,',');
      $add = "and final.paper_uid in ($str)";
    }

    $result = DB::select("SELECT * FROM
    (SELECT topic_data.topic, topic_data.paper_uid,topic_data.paper_code,topic_data.questMode ,topic_data.questType, topic_data.marks, CASE WHEN topic_data.expected IS NULL THEN 0 ELSE topic_data.expected END AS expected, CASE WHEN question_data.actual IS NULL THEN 0 ELSE question_data.actual END AS actual  FROM
    
    (SELECT topic_master.topic, topic_master.paper_uid,topic_master.paper_code, topic_master.questMode,topic_master.questType, topic_master.marks, SUM(topic_master.questions) as expected
    FROM
    (SELECT subject_master.paper_code, subject_master.id as paper_uid,topic_master.* FROM subject_master INNER JOIN topic_master on subject_master.id = topic_master.paper_id) topic_master
    GROUP BY
    topic_master.topic, topic_master.paper_uid,topic_master.paper_code,topic_master.questMode ,topic_master.questType, topic_master.marks) topic_data
    
    LEFT JOIN 
    
    (SELECT question_set.topic, question_set.paper_uid,question_set.paper_id, question_set.quest_type,question_set.difficulty_level, question_set.marks, COUNT(*) as actual FROM question_set GROUP BY question_set.topic, question_set.paper_uid,question_set.paper_id,question_set.quest_type ,question_set.difficulty_level,question_set.marks) question_data
    
    ON topic_data.topic = question_data.topic AND topic_data.paper_uid=question_data.paper_uid AND topic_data.paper_code = question_data.paper_id AND topic_data.questMode = question_data.quest_type AND topic_data.questType = question_data.difficulty_level AND topic_data.marks = question_data.marks) final

    WHERE final.expected > final.actual $add
    ORDER BY final.paper_uid");


    if($result)
    {
      return response()->json([
        "status"        => "success",
        "data"          => $result,
      ], 200);
    }
    else
    {
      return response()->json([
        "status"            => "success",
        "message"           => 'No Data Found',
        "data"              =>  [],
      ], 200);
    }

  }

  public function examReportCount($inst_id)
  {
      $result = User::where('username',$inst_id)->first();
      $instUid = $result->uid;

      $results = InstPrograms::where('inst_uid',$instUid)->get();
      $programArray = array();
      foreach($results as $result)
      {
        array_push($programArray,$result->program_id);
      }

      $data = array();
      $ress = SubjectMaster::whereIn('program_id',$programArray)->get();
      $i = 0;

      foreach($ress as $res)
      {
        $data[$i++] = [
          'id'                  =>  $res->id,
          'from_date'           =>  $res->from_date ? Carbon::createFromFormat('Y-m-d H:i:s.u', $res->from_date, 'UTC')->getPreciseTimestamp(3) : '',
          'paper_code'          =>  $res->paper_code,
          'paper_name'          =>  $res->paper_name,
          'marks'               =>  $res->marks,
          'questions'           =>  $res->questions,
          'duration'            =>  $res->durations,
          'allStudents'         =>  $this->getAllocatedStudentsCount($res->id,'all'),
          'overStudents'        =>  $this->getAllocatedStudentsCount($res->id,'over'),
          'inprogressStudents'  =>  $this->getAllocatedStudentsCount($res->id,'inprogress'),
          'unattendStudents'    =>  $this->getAllocatedStudentsCount($res->id,'unattend'),
          'exam'                =>  $this->getExam($res->paper_code),
        ];
      }
      return response()->json([
        "status"        => "success",
        "data"          => $data,
      ], 200);
  }

  public function getAllocatedStudentsCount($subjectId,$str)
  {
    if($str == 'all')
    {
      $count = CandTest::where('paper_id',$subjectId)->count();
      return $count;
    }
    else if($str=='over')
    {
      $count = CandTest::where('paper_id',$subjectId)->where('status','over')->count();
      return $count;
    }
    else if($str=='inprogress')
    {
      $count = CandTest::where('paper_id',$subjectId)->where('status','inprogress')->count();
      return $count;
    }
    else if($str=='unattend')
    {
      $count = CandTest::where('paper_id',$subjectId)->whereNull('status')->count();
      return $count;
    }
  }

  public function getExam($paper_code)
  {
    $result = SubjectMaster::where('paper_code',$paper_code)->first();
    if($result)
    {
      $paper_id = $result->id;
      $result1 = CandTest::where('paper_id',$paper_id)->first();
      if($result1)
      {
        return new ExamResource($result1);
      }
      else
      {
        return [];
      }
    }
    else
    {
      return [];
    }
  }


  public function examByPaperIdAndType(Request $request)
  {
    $paper_id     = $request->paper_id;
    $type         = $request->type;
    $instId       = $request->instId;
    $instName     = '';

    if(Auth::user()->role !== 'EADMIN')
    {
      $res = User::where('username',$instId)->where('role','EADMIN')->first();
      if($res)
      {
        $instName = $instId.'-'.$res->college_name;
      }
    }
    else
    {
      $instName = $instId.'-'.Auth::user()->college_name;
    }
  
    if($type == 'notattend')
    {
      $result = CandTest::where('paper_id',$paper_id)->whereNull('status')->get();
    }
    else if($type == 'all')
    {
      $result = CandTest::where('paper_id',$paper_id)->get();
    }
    else
    {
      $result = CandTest::where('paper_id',$paper_id)->where('status',$type)->get();
    }

    return response()->json([
      "status"        => "success",
      "data"          => new ExamCollection($result),
      "instName"      => $instName,
    ], 200);

  }

  public function specificationMatch()
  {
    $add ='';
    if(Auth::user()->role == 'EADMIN')
    {
      $ress = SubjectMaster::select('id')->where('inst_uid',Auth::user()->uid)->get();
      $str ='';
      foreach($ress as $res)
      {
        $str = $str .$res->id.',';
      }
      $str = rtrim($str,',');

      $add = "and final.paper_uid in ($str)";
    }

    $result = DB::select("SELECT * FROM
    (SELECT topic_data.topic, topic_data.paper_uid,topic_data.paper_code,topic_data.questMode ,topic_data.questType, topic_data.marks, CASE WHEN topic_data.expected IS NULL THEN 0 ELSE topic_data.expected END AS expected, CASE WHEN question_data.actual IS NULL THEN 0 ELSE question_data.actual END AS actual  FROM
    
    (SELECT topic_master.topic, topic_master.paper_uid,topic_master.paper_code, topic_master.questMode,topic_master.questType, topic_master.marks, SUM(topic_master.questions) as expected
    FROM
    (SELECT subject_master.paper_code, subject_master.id as paper_uid,topic_master.* FROM subject_master INNER JOIN topic_master on subject_master.id = topic_master.paper_id) topic_master
    GROUP BY
    topic_master.topic, topic_master.paper_uid,topic_master.paper_code,topic_master.questMode ,topic_master.questType, topic_master.marks) topic_data
    
    LEFT JOIN 
    
    (SELECT question_set.topic, question_set.paper_uid,question_set.paper_id, question_set.quest_type,question_set.difficulty_level, question_set.marks, COUNT(*) as actual FROM question_set GROUP BY question_set.topic, question_set.paper_uid,question_set.paper_id,question_set.quest_type ,question_set.difficulty_level,question_set.marks) question_data
    
    ON topic_data.topic = question_data.topic AND topic_data.paper_uid=question_data.paper_uid AND topic_data.paper_code = question_data.paper_id AND topic_data.questMode = question_data.quest_type AND topic_data.questType = question_data.difficulty_level AND topic_data.marks = question_data.marks) final
    WHERE final.expected <= final.actual $add
    ORDER BY final.paper_uid");


    if($result)
    {
      return response()->json([
        "status"        => "success",
        "data"          => $result,
      ], 200);
    }
    else
    {
      return response()->json([
        "status"            => "success",
        "message"           => 'No Data Found',
        "data"              =>  [],
      ], 200);
    }

  }

  public function storeQuestion($request)
  {
    $subjectId              = $request->subjectId;
    $subjectCode            = SubjectMaster::find($subjectId)->paper_code;
    $topic                  = $request->topic;
    $subtopic               = ($request->subtopic == null) ? 0 : $request->subtopic;
    $difficultyLevel        = $request->difficultyLevel;
    $marks                  = $request->marks;
    $questType              = $request->questType;
    $correctoption          = $request->correctoption;
    $question               = $request->question;
    $optiona                = $request->optiona;
    $optionb                = $request->optionb;
    $optionc                = $request->optionc;
    $optiond                = $request->optiond;
    $correctAnswer          = '';

    $qfilepath              = '';
    $a1filepath             = '';
    $a2filepath             = '';
    $a3filepath             = '';
    $a4filepath             = '';


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
                $image->move(public_path('data/files'), $new_name);
                $path=public_path('data/files').'/'.$new_name;
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
      if($request->a1)
      {
            $part = rand(100000,999999);
            $validation = Validator::make($request->all(), ['a1' => 'required|mimes:jpeg,jpg']);
            $path = $request->file('a1')->getRealPath();

            if($validation->passes())
            {
                $image = $request->file('a1');
                $new_name = 'O_'.$subjectId.'_'.$part.'.' . $image->getClientOriginalExtension();
                $image->move(public_path('data/files'), $new_name);
                $path=public_path('data/files').'/'.$new_name;
                $a1filepath = $new_name;
            }
            else
            {
              return response()->json([
                "status"            => "failure",
                "message"           => 'Option A Image must be jpeg or jpg',
              ], 400);
            }
      }
      $new_name='';
      if($request->a2)
      {
            $part = rand(100000,999999);
            $validation = Validator::make($request->all(), ['a2' => 'required|mimes:jpeg,jpg']);
            $path = $request->file('a2')->getRealPath();

            if($validation->passes())
            {
                $image = $request->file('a2');
                $new_name = 'O_'.$subjectId.'_'.$part.'.' . $image->getClientOriginalExtension();
                $image->move(public_path('data/files'), $new_name);
                $path=public_path('data/files').'/'.$new_name;
                $a2filepath = $new_name;
            }
            else
            {
              return response()->json([
                "status"            => "failure",
                "message"           => 'Option B Image must be jpeg or jpg',
              ], 400);
            }
      }
      $new_name='';
      if($request->a3)
      {
            $part = rand(100000,999999);
            $validation = Validator::make($request->all(), ['a3' => 'required|mimes:jpeg,jpg']);
            $path = $request->file('a3')->getRealPath();

            if($validation->passes())
            {
                $image = $request->file('a3');
                $new_name = 'O_'.$subjectId.'_'.$part.'.' . $image->getClientOriginalExtension();
                $image->move(public_path('data/files'), $new_name);
                $path=public_path('data/files').'/'.$new_name;
                $a3filepath = $new_name;
            }
            else
            {
              return response()->json([
                "status"            => "failure",
                "message"           => 'Option C Image must be jpeg or jpg',
              ], 400);
            }
      }
      $new_name='';
      if($request->a4)
      {
            $part = rand(100000,999999);
            $validation = Validator::make($request->all(), ['a4' => 'required|mimes:jpeg,jpg']);
            $path = $request->file('a4')->getRealPath();

            if($validation->passes())
            {
                $image = $request->file('a4');
                $new_name = 'O_'.$subjectId.'_'.$part.'.' . $image->getClientOriginalExtension();
                $image->move(public_path('data/files'), $new_name);
                $path=public_path('data/files').'/'.$new_name;
                $a4filepath = $new_name;
            }
            else
            {
              return response()->json([
                "status"            => "failure",
                "message"           => 'Option D Image must be jpeg or jpg',
              ], 400);
            }
      }
      if($correctoption == 'optiona')
      {
        if($request->a1)
        {
          $correctAnswer = $a1filepath.':$:optiona';
        }
        else
        {
          $correctAnswer = $optiona.':$:optiona';
        }
      }
      else if($correctoption == 'optionb')
      {
        if($request->a2)
        {
          $correctAnswer = $a2filepath.':$:optionb';
        }
        else
        {
          $correctAnswer = $optionb.':$:optionb';
        }
      }
      else if($correctoption == 'optionc')
      {
        if($request->a3)
        {
          $correctAnswer = $a3filepath.':$:optionc';
        }
        else
        {
          $correctAnswer = $optionc.':$:optionc';
        }
      }
      else if($correctoption == 'optiond')
      {
        if($request->a4)
        {
          $correctAnswer = $a4filepath.':$:optiond';
        }
        else
        {
          $correctAnswer = $optiond.':$:optiond';
        }
      }


      if($questType == 'N' || $questType == 'N1')
      {
        $optiona = $optiona.':$:optiona';
        $optionb = $optionb.':$:optionb';
        $optionc = $optionc.':$:optionc';
        $optiond = $optiond.':$:optiond';
      }
      else if($questType == 'N2' || $questType == 'N3')
      {
        $a1filepath = $a1filepath.':$:optiona';
        $a2filepath = $a2filepath.':$:optionb';
        $a3filepath = $a3filepath.':$:optionc';
        $a4filepath = $a4filepath.':$:optiond';
      }

      $values = [
        'paper_uid'       => $subjectId,
        'paper_id'        => $subjectCode,
        'question'        => str_replace('&lt;','<',str_replace('&gt;','>',str_replace('amp;','',$question))),
        'topic'           => $topic,
        'subtopic'        => $subtopic,
        'qu_fig'          => $qfilepath,
        'figure'          => $questType,
        'optiona'         => str_replace('&lt;','<',str_replace('&gt;','>',str_replace('amp;','',$optiona))),
        'a1'              => $a1filepath,
        'optionb'         => str_replace('&lt;','<',str_replace('&gt;','>',str_replace('amp;','',$optionb))),
        'a2'              => $a2filepath,
        'optionc'         => str_replace('&lt;','<',str_replace('&gt;','>',str_replace('amp;','',$optionc))),
        'a3'              => $a3filepath,
        'optiond'         => str_replace('&lt;','<',str_replace('&gt;','>',str_replace('amp;','',$optiond))),
        'a4'              => $a4filepath,
        'correctanswer'   => str_replace('&lt;','<',str_replace('&gt;','>',str_replace('amp;','',$correctAnswer))),
        'coption'         => $correctoption,
        'marks'           => $marks,
        'difficulty_level'=> $difficultyLevel
      ];
    
      $result         = QuestionSet::create($values); 

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

  public function uploadQuestion($request)
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
      $fileName           = 'QuestionBank.xlsx';  

      $request->file->move(public_path('assets/tempfiles/'), $fileName);
      $reader             = IOFactory::createReader("Xlsx");
      $spreadsheet        = $reader->load(public_path('assets/tempfiles/').$fileName);
      $current_time 			= Carbon::now();
      $highestRow         = $spreadsheet->getActiveSheet()->getHighestRow();
      $values             = [];
    
      for($i=2;$i<=$highestRow;$i++)
      {
        $correctAnswer    = '';
        $instId           = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(1, $i)->getValue();
        $paper_code       = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(2, $i)->getValue();
        $question         = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(3, $i)->getValue();
        $topic            = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(4, $i)->getValue();
        $subtopic         = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(5, $i)->getValue();
        $optiona          = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(6, $i)->getValue();
        $optionb          = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(7, $i)->getValue();
        $optionc          = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(8, $i)->getValue();
        $optiond          = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(9, $i)->getValue();
        $correctoption   = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(10, $i)->getValue();
        $marks            = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(11, $i)->getValue();
        $diff_level       = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(12, $i)->getValue();
        $inst_uid         = User::where('username',$instId)->first()->uid;
       
        try
        {
          $paper_id         = SubjectMaster::where('paper_code',$paper_code)->where('inst_uid',$inst_uid)->first()->id;
        }
        catch(\Exception $e)
        {
          DB::rollback();
          return response()->json([
            "status"  => "failure",
            "message" => "All rows upto ".($i-1)." are uploaded Successfully. Problem finding Papaer code:".$paper_code
          ], 400);
        }


        $optiona = $optiona.':$:optiona';
        $optionb = $optionb.':$:optionb';
        $optionc = $optionc.':$:optionc';
        $optiond = $optiond.':$:optiond';

        if($correctoption == 'optiona')
        {
            $correctAnswer = $optiona.':$:optiona';
        }
        else if($correctoption == 'optionb')
        {
            $correctAnswer = $optionb.':$:optionb';
        }
        else if($correctoption == 'optionc')
        {
            $correctAnswer = $optionc.':$:optionc';
        }
        else if($correctoption == 'optiond')
        {
            $correctAnswer = $optiond.':$:optiond';
        }
        
        $values = [
          'paper_uid'       => $paper_id,
          'paper_id'        => $paper_code,
          'question'        => $question,
          'topic'           => $topic,
          'subtopic'        => $subtopic,
          'figure'          => 'N',
          'optiona'         => $optiona,
          'optionb'         => $optionb,
          'optionc'         => $optionc,
          'optiond'         => $optiond,
          'correctanswer'   => $correctAnswer,
          'coption'         => $correctoption,
          'marks'           => $marks,
          'difficulty_level'=> $diff_level
        ];
        
        try 
        {
          $result         = QuestionSet::create($values); 
        }
        catch(\Exception $e)
        {
          DB::rollback();
          return response()->json([
            "status"  => "failure",
            "message" => "All rows upto ".($i-1)." are uploaded Successfully.Problem Uploading specified Row. Error:".$e->getMessage()
          ], 400);
        }


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
  
    $result = QuestionSet::whereIn('paper_uid',$array)->where('quest_type','O')->orderBy('created_at')->paginate(50);

    if($result)
    {
      return new QuestionCollection($result);
      /*return response()->json([
        "status"            => "success",
        "data"              => new QuestionCollection($result),
      ], 200);*/
    }
    else
    {
      return response()->json([
        "status"            => "failure",
        "message"           => 'Unable to fetch Questions...',
      ], 400);
    }
  }

  public function deleteQuestion($qnid)
  {
    $result = QuestionSet::find($qnid)->delete();

    return response()->json([
      "status"            => "success",
      "message"              => "Record Deleted successfully...",
    ], 200);    
  }


  public function getQuestion($qnid)
  {
    $result = QuestionSet::find($qnid);

    if($result)
    {
      return response()->json([
        "status"                => "success",
        "data"                  => $result,
      ], 200); 
    }
    else
    {
      return response()->json([
        "status"            => "failure",
        "message"           => "Question Does not Exist...",
      ], 400);    
    }
  }

  public function updateQuestion($qnid,$request)
  {
    
    

    $subjectId              = $request->subjectId;
    $subjectCode            = SubjectMaster::find($subjectId)->paper_code;
    $topic                  = $request->topic;
    $subtopic               = ($request->subtopic == null) ? 0 : $request->subtopic;
    $difficultyLevel        = $request->difficultyLevel;
    $marks                  = $request->marks;
    $questType              = $request->questType;
    $correctoption          = $request->correctoption;
    $question               = $request->question;
    $optiona                = $request->optiona;
    $optionb                = $request->optionb;
    $optionc                = $request->optionc;
    $optiond                = $request->optiond;
    $correctAnswer          = '';
    $imgChange              = explode(',',$request->imgChange);
    $values                 = [];
    
    $origQustion            = QuestionSet::find($qnid);

    
    //dd($subjectId.':'.$subjectId.':'.$topic.':'.$subtopic.':'.$difficultyLevel.':'.$marks.':'.$questType.':'.$correctoption.':'.$question.':'.$optiona.':'.$optionb.':'.$optionc.':'.$optiond);
  
    

    $qfilepath              = '';
    $a1filepath             = '';
    $a2filepath             = '';
    $a3filepath             = '';
    $a4filepath             = '';


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
                $image->move(public_path('data/files'), $new_name);
                $path=public_path('data/files').'/'.$new_name;
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
      $new_name='';
      if(in_array("a1",$imgChange))
      {
            $validation = Validator::make($request->all(), ['a1' => 'required|mimes:jpeg,jpg']);
            $path = $request->file('a1')->getRealPath();

            if($validation->passes())
            {
                $image = $request->file('a1');
                $new_name = explode(':$:',$origQustion->a1)[0];
                if($new_name == '')
                {
                  $part = rand(100000,999999);
                  $new_name = 'O_'.$subjectId.'_'.$part.'.' . $image->getClientOriginalExtension();
                }
                $image->move(public_path('data/files'), $new_name);
                $path=public_path('data/files').'/'.$new_name;
                $a1filepath = $new_name;

                $request->a1 = $a1filepath;
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
        $request->a1 = explode(':$:',$origQustion->a1)[0];
      }
      $new_name='';
      if(in_array("a2",$imgChange))
      {
            $validation = Validator::make($request->all(), ['a2' => 'required|mimes:jpeg,jpg']);
            $path = $request->file('a2')->getRealPath();

            if($validation->passes())
            {
                $image = $request->file('a2');
                $new_name = explode(':$:',$origQustion->a2)[0];
                if($new_name == '')
                {
                  $part = rand(100000,999999);
                  $new_name = 'O_'.$subjectId.'_'.$part.'.' . $image->getClientOriginalExtension();
                }
                $image->move(public_path('data/files'), $new_name);
                $path=public_path('data/files').'/'.$new_name;
                $a2filepath = $new_name;

                $request->a2 = $a2filepath;
            }
            else
            {
              return response()->json([
                "status"            => "failure",
                "message"           => 'Option B Image must be jpeg or jpg',
              ], 400);
            }
      }
      else
      {
        $request->a2 = explode(':$:',$origQustion->a2)[0];
      }

      $new_name='';
      if(in_array("a3",$imgChange))
      {
            $validation = Validator::make($request->all(), ['a3' => 'required|mimes:jpeg,jpg']);
            $path = $request->file('a3')->getRealPath();

            if($validation->passes())
            {
                $image = $request->file('a3');
                $new_name = explode(':$:',$origQustion->a3)[0];
                if($new_name == '')
                {
                  $part = rand(100000,999999);
                  $new_name = 'O_'.$subjectId.'_'.$part.'.' . $image->getClientOriginalExtension();
                }
                $image->move(public_path('data/files'), $new_name);
                $path=public_path('data/files').'/'.$new_name;
                $a3filepath = $new_name;

                $request->a3 = $a3filepath;
            }
            else
            {
              return response()->json([
                "status"            => "failure",
                "message"           => 'Option C Image must be jpeg or jpg',
              ], 400);
            }
      }
      else
      {
        $request->a3 = explode(':$:',$origQustion->a3)[0];
      }

      $new_name='';
      if(in_array("a4",$imgChange))
      {
            $validation = Validator::make($request->all(), ['a4' => 'required|mimes:jpeg,jpg']);
            $path = $request->file('a4')->getRealPath();

            if($validation->passes())
            {
                $image = $request->file('a4');
                $new_name = explode(':$:',$origQustion->a4)[0];
                if($new_name == '')
                {
                  $part = rand(100000,999999);
                  $new_name = 'O_'.$subjectId.'_'.$part.'.' . $image->getClientOriginalExtension();
                }
                $image->move(public_path('data/files'), $new_name);
                $path=public_path('data/files').'/'.$new_name;
                $a4filepath = $new_name;

                $request->a4 = $a4filepath;
            }
            else
            {
              return response()->json([
                "status"            => "failure",
                "message"           => 'Option D Image must be jpeg or jpg',
              ], 400);
            }
      }
      else
      {
        $request->a4 = explode(':$:',$origQustion->a4)[0];
      }

      //dd($request->qufig.':'.$request->a1.':'.$request->a2.':'.$request->a3.':'.$request->a4);

      if($correctoption == 'optiona')
      {
        if($request->a1)
        {
          $correctAnswer = explode(':$:',$request->a1)[0].':$:optiona';
        }
        else
        {
          $correctAnswer = $optiona.':$:optiona';
        }
      }
      else if($correctoption == 'optionb')
      {
        if($request->a2)
        {
          $correctAnswer = explode(':$:',$request->a2)[0].':$:optionb';
        }
        else
        {
          $correctAnswer = $optionb.':$:optionb';
        }
      }
      else if($correctoption == 'optionc')
      {
        if($request->a3)
        {
          $correctAnswer = explode(':$:',$request->a3)[0].':$:optionc';
        }
        else
        {
          $correctAnswer = $optionc.':$:optionc';
        }
      }
      else if($correctoption == 'optiond')
      {
        if($request->a4)
        {
          $correctAnswer = explode(':$:',$request->a4)[0].':$:optiond';
        }
        else
        {
          $correctAnswer = $optiond.':$:optiond';
        }
      }
  
      if($questType == 'N' || $questType == 'N1')
      {
        $optiona = $optiona.':$:optiona';
        $optionb = $optionb.':$:optionb';
        $optionc = $optionc.':$:optionc';
        $optiond = $optiond.':$:optiond';
      }
      else if($questType == 'N2' || $questType == 'N3')
      {
        $a1filepath = $a1filepath.':$:optiona';
        $a2filepath = $a2filepath.':$:optionb';
        $a3filepath = $a3filepath.':$:optionc';
        $a4filepath = $a4filepath.':$:optiond';
      }

      $values = [
        'paper_uid'       => $subjectId,
        'paper_id'        => $subjectCode,
        'question'        => str_replace('&lt;','<',str_replace('&gt;','>',str_replace('amp;','',$question))),
        'topic'           => $topic,
        'subtopic'        => $subtopic,
        'figure'          => $questType,
        'optiona'         => str_replace('&lt;','<',str_replace('&gt;','>',str_replace('amp;','',$optiona))),
        'optionb'         => str_replace('&lt;','<',str_replace('&gt;','>',str_replace('amp;','',$optionb))),
        'optionc'         => str_replace('&lt;','<',str_replace('&gt;','>',str_replace('amp;','',$optionc))),
        'optiond'         => str_replace('&lt;','<',str_replace('&gt;','>',str_replace('amp;','',$optiond))),
        'correctanswer'   => str_replace('&lt;','<',str_replace('&gt;','>',str_replace('amp;','',$correctAnswer))),
        'coption'         => $correctoption,
        'marks'           => $marks,
        'difficulty_level'=> $difficultyLevel,
        'moderator'       => Auth::user()->uid,
        'updated_at'      => Carbon::now(),
      ];

      if(in_array("qufig",$imgChange))
      {
        $values['qu_fig']   = $qfilepath;
      }
      if(in_array("a1",$imgChange))
      {
        $values['a1']       = $a1filepath;
      }
      if(in_array("a2",$imgChange))
      {
        $values['a2']       = $a2filepath;
      }
      if(in_array("a3",$imgChange))
      {
        $values['a3']       = $a3filepath;
      }
      if(in_array("a4",$imgChange))
      {
        $values['a4']       = $a4filepath;
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

  public function updateProgram($id,$request)
  {
    $result = ProgramMaster::find($id);
  
    if($result)
    {
      $result->program_code = $request->progCode;
      $result->program_name = $request->progName;
      $result->inst_uid     = $request->instId;
      $result->updated_at   = Carbon::now();

      $result->save();

      return response()->json([
        "status"            => "success",
        "message"           => 'Program Data updated Successfully...',
      ], 200);
    }
    else
    {
      return response()->json([
        "status"            => "failure",
        "message"           => 'Invalid Program Id...',
      ], 400);
    }

  }

  public function updateSubjectMaster($id,$request)
  {
    $result = SubjectMaster::find($id);

    if($result)
    {
      $result->paper_code = $request->paperCode;
      $result->paper_name = $request->paperName;
      $result->program_id = $request->programId;
      $result->inst_uid   = $request->instId;
      $result->semester   = $request->semester;

      $result->save();

      return response()->json([
        "status"            => "success",
        "message"           => 'Subject Master Data updated Successfully...',
      ], 200);
    }
    else
    {
      return response()->json([
        "status"            => "failure",
        "message"           => 'Invalid Subject Id...',
      ], 400);
    }

  }

  public function getGenericConfig($request)
  {
    //--Find this ID in subjects list if not present then create subject with name GENERICTEST------
    $result = SubjectMaster::where('paper_code',$request->paperCode)->where('inst_uid',Auth::user()->uid)->first();
    if(!$result)
    {
      $value = [
        'paper_code' => $request->paperCode,
        'paper_name' => $request->paperName,
        'program_id' => '0',
        'inst_uid'   => Auth::user()->uid,
        'semester'   => '0',
      ];
      $res = SubjectMaster::create($value);

      $result = SubjectMaster::where('paper_code',$request->paperCode)->where('inst_uid',Auth::user()->uid)->first();

      return response()->json([
        "status"            => "success",
        "data"              => new PaperResource($result),
      ], 200);
    }
    else
    {
      return response()->json([
        "status"            => "success",
        "data"              => new PaperResource($result),
      ], 200);
    }
    //-----------------------------------------------------------------------------------------
  }

  public function updateGenericConfig($id,$request)
  {
    $paperCode = $id;
    $result = SubjectMaster::where('paper_code',$paperCode)->where('inst_uid',Auth::user()->uid)->first();

    $exam_mode          =   $result->exam_mode;

    $score_view         =   $request->score_view ? $request->score_view : '0';
    $review_question    =   $request->review_question ? $request->review_question : '0';
    $proctoring         =   $request->proctoring;
    $photo_capture      =   $request->photo_capture ? $request->photo_capture : '0';
    $capture_interval   =   $request->capture_interval ? $request->capture_interval : '0';
    $negative_marking   =   $request->negative_marking ? $request->negative_marking : '0';
    $negative_marks     =   $request->negative_marks;
    $time_remaining_reminder  =   $request->time_remaining_reminder ? $request->time_remaining_reminder : '0';
    $exam_switch        =   $request->exam_switch ? $request->exam_switch : '0';
    $exam_switch_alerts =   $request->exam_switch_alerts ? $request->exam_switch_alerts : '99999';
    $option_shuffle     =   $request->option_shuffle ? $request->option_shuffle : '0';
    $question_marks     =   $request->question_marks ? $request->question_marks : '0';
    $ph_time            =   $request->ph_time;
    $static_assign      =   $request->static_assign;
    $questwisetimer     =   $request->questwisetimer ? $request->questwisetimer :'0';
    $secperquest        =   $request->secperquest ? $request->secperquest :'0';

    $res = SubjectMaster :: where('inst_uid',Auth::user()->uid)->update([
      'score_view'          => $score_view,
      'review_question'     => $review_question,
      'proctoring'          => $proctoring,
      'photo_capture'       => $photo_capture,
      'capture_interval'    => $capture_interval,
      'negative_marking'    => $negative_marking, 
      'negative_marks'      => $negative_marks ? $negative_marks : '0',
      'time_remaining_reminder' => $time_remaining_reminder,
      'exam_switch'         => $exam_switch,
      'exam_switch_alerts'  => $exam_switch_alerts,
      'option_shuffle'      => $option_shuffle,
      'question_marks'      => $question_marks,
      'ph_time'             => $ph_time,
      'static_assign'       => $static_assign,
      'questwisetimer'      => $questwisetimer,
      'secperquest'         => $secperquest
    ]);

    $res = SubjectMaster :: where('inst_uid',Auth::user()->uid)->whereIn('exam_mode',['subjective','both'])->update([
      'score_view'          => '0',
    ]);

    return response()->json([
      "status"          => "success",
      "message"         => "Exam Configuration Updated Successfully.",
    ], 200);
  }

  public function getExamsByEnrollno($enrollno)
  {
    $result = User::where('username',$enrollno)->where('role','STUDENT')->where('inst_id',Auth::user()->username)->first();

    $exams 	= User::find($result->uid)->exams;
		if($exams)
		{
			return new ExamCollection($exams);
		}
		else
		{
			return json_encode([
				'status' => 'failure'
			],200);
		}
  }

  public function getExamsByEnrollnoInstId($enrollno,$instId)
  {
    $result = User::where('username',$enrollno)->where('role','STUDENT')->where('inst_id',$instId)->first();
    $exams 	= User::find($result->uid)->exams;
		if($exams)
		{
			return new ExamCollection($exams);
		}
		else
		{
			return json_encode([
				'status' => 'failure'
			],200);
		}
  }

  public function getExamLog($enrollno,$paperId,$instId)
  {
    $result = User::where('username',$enrollno)->where('inst_id',$instId)->first();
    if($result)
    {
      $stdid = $result->uid;
       //----------Find Exam Id of this student---------------------------------------
       $result1 = CandTest::where('stdid',$stdid)->where('paper_id',$paperId)->where('inst',$instId)->first();
       //------------------------------------------------------------------------------
       if($result1)
       {
          $examId = $result1->id;

          $result2 = CandQuestion::where('exam_id',$examId)->get();
          if($result2)
          {
            return json_encode([
              'status'  => 'success',
              'data'    => new AnswerCollection($result2),
            ],200);
          }
          else
          {
            return json_encode([
              'status'  => 'failure',
              'message' => 'No Questions found for this Candidate...',
            ],400);
          }
       }
       else
       {
          return json_encode([
            'status'  => 'failure',
            'message' => 'No Exam Data for this Student Found...',
          ],400);
       }
    }
    else
    {
      return json_encode([
				'status'  => 'failure',
        'message' => 'Invalid Username Data...',
			],400);
    }
  }

  public function proctorByEnrollno($enrollno,$paperId)
  {
    $result = User::where('username',$enrollno)->where('inst_id',Auth::user()->username)->first();
    if($result)
    {
      $stdid = $result->uid;
      //----------Find Exam Id of this student---------------------------------------
       $result1 = CandTest::where('stdid',$stdid)->where('paper_id',$paperId)->where('inst',Auth::user()->username)->first();
      //------------------------------------------------------------------------------
       if($result1)
       {
          $examId = $result1->id;
          $result2= ProctorSnaps::where('examid',$examId)->get();
          return json_encode([
            'status'  => 'success',
            'data'    => new ProctorSnapCollection($result2),
          ],200);
       }
       else
       {
        return json_encode([
          'status'  => 'failure',
          'message' => 'No Proctoring Data found for this Candidate...',
        ],400);
       }
    }
    else
    {
      return json_encode([
        'status'  => 'failure',
        'message' => 'Invalid User Data...'
      ],400);
    }
  }

  public function getSubjectByDate($date)
  {
    if(Auth::user()->role == 'EADMIN')
    {
      $inst_uid = Auth::user()->uid;

      $result = SubjectMaster::where('inst_uid',$inst_uid)->where('from_date','LIKE','%'.$date.'%')->get();
      if($result)
      {
        return json_encode([
          'status'  => 'success',
          'data'    => $result,
        ],200);
      }
      else
      {
        return json_encode([
          'status'  => 'failure',
          'data'    => $result,
        ],200);
      }
    }
    else if(Auth::user()->role == 'ADMIN')
    {
      $res    = User::where('username',$inst)->where('role','EADMIN')->first();
      $inst_uid = $res->uid;
      $result = SubjectMaster::where('inst_uid',$inst_uid)->where('from_date','LIKE','%'.$date.'%')->get();

      return json_encode([
        'status'  => 'success',
        'data'    => $result,
      ],200);
    }
  }

  public function getSubjectByDateInst($date,$inst)
  {
      $res    = User::where('username',$inst)->where('role','EADMIN')->first();
      $inst_uid = $res->uid;
      $result = SubjectMaster::where('inst_uid',$inst_uid)->where('from_date','LIKE','%'.$date.'%')->get();

      return json_encode([
        'status'  => 'success',
        'data'    => $result,
      ],200);
  }

  public function examReportCountByDate($request)
  {
    $subject  = $request->subject;
    $date     = $request->date;

    $result   = CandTest::where('paper_id',$subject)->get();

    return json_encode([
      'status'  => 'success',
      'data'    => new ExamCollection($result),
    ],200);
  }

  public function examReportCountDatewise($date,$subject,$slot,$inst_id)
  {
      $result = User::where('username',$inst_id)->first();
      $instUid = $result->uid;

      $results = InstPrograms::where('inst_uid',$instUid)->get();
      $programArray = array();
      foreach($results as $result)
      {
        array_push($programArray,$result->program_id);
      }

      $data = array();
      if($date == 'all' && $subject =='all' && $slot == 'all') // 000
      {
        $ress = SubjectMaster::whereIn('program_id',$programArray)->get();
      }
      else if($date == 'all' && $subject =='all' && $slot != 'all') // 001
      {
        $ress = SubjectMaster::whereIn('program_id',$programArray)->where('slot',$slot)->get();
      }
      else if($date == 'all' && $subject != 'all' && $slot == 'all') // 010
      {
        $ress = SubjectMaster::whereIn('program_id',$programArray)->where('id',$subject)->get();
      }
      else if($date == 'all' && $subject != 'all' && $slot != 'all') // 011
      {
        $ress = SubjectMaster::whereIn('program_id',$programArray)->where('id',$subject)->where('slot',$slot)->get();
      }
      else if($date != 'all' && $subject == 'all' && $slot == 'all') // 100
      {
        $ress = SubjectMaster::whereIn('program_id',$programArray)->where('from_date','LIKE','%'.$date.'%')->get();
      }
      else if($date != 'all' && $subject == 'all' && $slot != 'all') // 101
      {
        $ress = SubjectMaster::whereIn('program_id',$programArray)->where('from_date','LIKE','%'.$date.'%')->where('slot',$slot)->get();
      }
      else if($date != 'all' && $subject != 'all' && $slot == 'all') // 110
      {
        $ress = SubjectMaster::whereIn('program_id',$programArray)->where('from_date','LIKE','%'.$date.'%')->where('id',$subject)->get();
      }
      else if($date != 'all' && $subject!= 'all' && $slot != 'all') // 111
      {
        $ress = SubjectMaster::whereIn('program_id',$programArray)->where('from_date','LIKE','%'.$date.'%')->where('id',$subject)->where('slot',$slot)->get(); 
      }
      
      $i = 0;

      foreach($ress as $res)
      {
        $data[$i++] = [
          'id'                  =>  $res->id,
          'from_date'           =>  $res->from_date ? Carbon::createFromFormat('Y-m-d H:i:s.u', $res->from_date, 'UTC')->getPreciseTimestamp(3) : '',
          'paper_code'          =>  $res->paper_code,
          'paper_name'          =>  $res->paper_name,
          'marks'               =>  $res->marks,
          'questions'           =>  $res->questions,
          'duration'            =>  $res->durations,
          'slot'                =>  $res->slot,
          'allStudents'         =>  $this->getAllocatedStudentsCount($res->id,'all'),
          'overStudents'        =>  $this->getAllocatedStudentsCount($res->id,'over'),
          'inprogressStudents'  =>  $this->getAllocatedStudentsCount($res->id,'inprogress'),
          'unattendStudents'    =>  $this->getAllocatedStudentsCount($res->id,'unattend'),
          'exam'                =>  $this->getExam($res->paper_code),
        ];
      }
      return response()->json([
        "status"        => "success",
        "data"          => $data,
      ], 200);
  }

  public function studSubAlloc($request)
  {
    $paperId = $request->paperId;
    $instUid = $request->instUid;

    $result = User::where('uid',$instUid)->where('role','EADMIN')->first();
    $inst   = $result->username;


    $exams 	= DB::table("cand_test")->select(DB::raw("stdid,inst,paper_id,program_id"))->where('inst',$inst)->where('paper_id',$paperId)->get();

		if($exams)
		{
      return json_encode([
				'status' => 'success',
        'data'   => new CustomExamReportCollection($exams),
			],200);
		}
		else
		{
			return json_encode([
				'status' => 'failure'
			],400);
		}
  }

  public function clearSessionMulitiple($users,$instUid)
  {
      $date     = new Carbon('2001-01-01 01:01:01');

      $rres     = User::whereIn('username',$users)->where('inst_id',$instUid)->select(DB::raw('group_concat(uid) as uidd'))->first();
      $result   = OauthAccessToken::whereIn('user_id',explode(',',$rres->uidd))->update(['revoked' => 1]);
      $result1  = Session::whereIn('uid',explode(',',$rres->uidd))->where('endtime',NULL)->update(['endtime' => $date]);

      if($result1)
      {
        return response()->json([
          "status" => "success",
          "message" => "Session Cleared Successfully...",
        ], 200);
      }
      else
      {
        return response()->json([
          "status" => "failure",
          "message" => "Session is already cleared for these Users...",
        ], 200);
      }
  }

  public function getAutoEndExamCount($request)
  {
    $date     = $request->date;
    $slot     = $request->slot != ''    ? $request->slot : 'all';
    $paperId  = $request->paperId != '' ? $request->paperId : 'all';

    $instID   = $request->instId;
    $rres = User::where('username',$instID)->where('role','EADMIN')->first();
    $instUid = $rres->uid;

    $cnt = 0;

    if($slot == 'all' && $paperId == 'all')
    {
      //----------find all papersId of given institute on given date-----------------------------
        $result = DB::select("select GROUP_CONCAT(id) as id from subject_master where from_date like '%$date%' and inst_uid like '$instUid' group by inst_uid");

        if($result)
        {
          $paperId = $result[0]->id;

          $result1= DB::select("select count(*) as cnt from cand_test where paper_id in($paperId) and status='inprogress'");
          if($result1)
          {
            $cnt  = $result1[0]->cnt;
          }

          return response()->json([
            "status"  => "success",
            "cnt"     => $cnt,
            'type'    => '1',
          ], 200);
        }
        else
        {
          return response()->json([
            "status"  => "success",
            "cnt"     => 0,
            'type'    => '1',
          ], 200);
        }
        
      //-----------------------------------------------------------------------------------------
    }
    else if($slot == 'all' && $paperId != 'all')
    {
        $result1= DB::select("select count(*) as cnt from cand_test where paper_id in($paperId) and status='inprogress'");

        if($result1)
        {
          $cnt  = $result1[0]->cnt;
        }

        return response()->json([
          "status"  => "success",
          "cnt"     => $cnt,
          'type'    => '2',
        ], 200);
    }
    elseif($slot != 'all' && $paperId == 'all')
    {
      //----------find all papersId of given institute on given date and slot-----------------------------
      $result = DB::select("select GROUP_CONCAT(id) as id from subject_master where from_date like '%$date%' and slot='$slot' and inst_uid like '$instUid' group by inst_uid");

      if($result)
      {
        $paperId = $result[0]->id;
      }
      
      $result1= DB::select("select count(*) as cnt from cand_test where paper_id in($paperId) and status='inprogress'");
      if($result1)
      {
        $cnt  = $result1[0]->cnt;
      }

      return response()->json([
        "status"  => "success",
        "cnt"     => $cnt,
        'type'    => '3',
      ], 200);
    //-----------------------------------------------------------------------------------------
    }
    else if($slot != 'all' && $paperId != 'all')
    {
        //----------find all papersId of given institute on given date and slot-----------------------------
        $result = DB::select("select GROUP_CONCAT(id) as id from subject_master where from_date like '%$date%' and slot='$slot' and id='$paperId' and inst_uid like '$instUid' group by inst_uid");

        if($result)
        {
          $paperId = $result[0]->id;
        }
        else
        {
          return response()->json([
            "status"  => "success",
            "cnt"     => 0,
            'type'    => '4',
          ], 200);
        }
        
        $result1= DB::select("select count(*) as cnt from cand_test where paper_id in($paperId) and status='inprogress'");
        if($result1)
        {
          $cnt  = $result1[0]->cnt;
        }
        else
        {
          return response()->json([
            "status"  => "success",
            "cnt"     => 0,
            'type'    => '4',
          ], 200);
        }

        return response()->json([
          "status"  => "success",
          "cnt"     => $cnt,
          'type'    => '4',
        ], 200);
        //--------------------------------------------------------------------------------------
    }
  }

  public function autoEndExam($date,$request)
  {
    $slot     = $request->slot != ''    ? $request->slot : 'all';
    $paperId  = $request->paperId != '' ? $request->paperId : 'all';
    
    $instID   = $request->instId;
    $rres = User::where('username',$instID)->where('role','EADMIN')->first();
    $instUid = $rres->uid;
    DB::beginTransaction();
    
    if($slot == 'all' && $paperId == 'all')
    {
      //----------find all papersId of given institute on given date---------------
        $results = DB::select("select group_concat(id) as id from subject_master where from_date like '%$date%' and inst_uid like '$instUid' group by inst_uid");

        try
        {
          $paperId = explode(',',$results[0]->id);
          $paperIdStr = $results[0]->id;

          $results2 = CandTest::whereIn('paper_id',$paperId)->where('status','inprogress')->get();

          foreach($results2 as $resultt2)
          {
            $id = $resultt2->id;
              $cqnid='';$wqnid='';$uqnid='';$marksobt=0;
              //----------get value of cqnid,wqnid,uqnid--------------------------------
                $result = DB::select("SELECT GROUP_CONCAT(qnid) as cqnid,sum(marks) as marksobt FROM `cand_questions` where exam_id='$id' and answered in('answered','answeredandreview') and trim(stdanswer)=trim(cans)");
                if($result)
                {
                  $cqnid 		= $result[0]->cqnid;
                  $marksobt 	= $result[0]->marksobt;
                }

                $result1 = DB::select("SELECT GROUP_CONCAT(qnid) as wqnid FROM `cand_questions` where exam_id='$id' and answered in('answered','answeredandreview') and trim(stdanswer)!=trim(cans)");
                if($result1)
                {
                  $wqnid = $result1[0]->wqnid;
                }

                $result2 = DB::select("SELECT GROUP_CONCAT(qnid) as uqnid FROM `cand_questions` where exam_id='$id' and answered in('unanswered','unansweredandreview')");
                if($result2)
                {
                  $uqnid = $result2[0]->uqnid;
                }
              //------------------------------------------------------------------------

              //-------------------Update Exam Resource with End Exam Records-----------
              $result3 = DB::table('cand_test')->where('id',$id)->update([
                'cqnid' 			=> 	$cqnid,
                'wqnid' 			=> 	$wqnid,
                'uqnid' 			=> 	$uqnid,
                'end_on' 			=>	Carbon::now(),
                'end_by'			=>	$this->uid,
                'status'			=>	'over',
                'marksobt'		=>	$marksobt,
                'updated_at'	=>	Carbon::now(),
              ]);
              //------------------------------------------------------------------------
          }
          DB::commit();
          return response()->json([
            "status"  => "success",
          ], 200);
        }
        catch(\Exception $e)
        {
          DB::rollback();
          return response()->json([
            "status"  => "failure",
          ], 200);
        }
        
      //-------------------------------------------------------------------
    }
    else if($slot == 'all' && $paperId != 'all')
    {
      $paperIdStr = $paperId;
      $paperId = explode(',',$paperId);
      
      $results2 = CandTest::whereIn('paper_id',$paperId)->where('status','inprogress')->get();

      foreach($results2 as $resultt2)
      {
        $id = $resultt2->id;
          $cqnid='';$wqnid='';$uqnid='';$marksobt=0;
          //----------get value of cqnid,wqnid,uqnid--------------------------------
            $result = DB::select("SELECT GROUP_CONCAT(qnid) as cqnid,sum(marks) as marksobt FROM `cand_questions` where exam_id='$id' and answered in('answered','answeredandreview') and trim(stdanswer)=trim(cans)");
            if($result)
            {
              $cqnid 		= $result[0]->cqnid;
              $marksobt 	= $result[0]->marksobt;
            }

            $result1 = DB::select("SELECT GROUP_CONCAT(qnid) as wqnid FROM `cand_questions` where exam_id='$id' and answered in('answered','answeredandreview') and trim(stdanswer)!=trim(cans)");
            if($result1)
            {
              $wqnid = $result1[0]->wqnid;
            }

            $result2 = DB::select("SELECT GROUP_CONCAT(qnid) as uqnid FROM `cand_questions` where exam_id='$id' and answered in('unanswered','unansweredandreview')");
            if($result2)
            {
              $uqnid = $result2[0]->uqnid;
            }
          //------------------------------------------------------------------------
          //-------------------Update Exam Resource with End Exam Records-----------
          $result3 = DB::table('cand_test')->where('id',$id)->update([
            'cqnid' 			=> 	$cqnid,
            'wqnid' 			=> 	$wqnid,
            'uqnid' 			=> 	$uqnid,
            'end_on' 			=>	Carbon::now(),
            'end_by'			=>	$this->uid,
            'status'			=>	'over',
            'marksobt'		=>	$marksobt,
            'updated_at'	=>	Carbon::now(),
          ]);
          
          //------------------------------------------------------------------------
      }

      DB::commit();
        return response()->json([
          "status"  => "success",
        ], 200);
    }
    elseif($slot != 'all' && $paperId == 'all')
    {
      //----------find all papersId of given institute on given date and slot-------
      $results = DB::select("select GROUP_CONCAT(id) as id from subject_master where from_date like '%$date%' and slot='$slot' and inst_uid like '$instUid' group by inst_uid");

      try
        {
          $paperId = explode(',',$results[0]->id);
          $paperIdStr = $results[0]->id;

          $results2 = CandTest::whereIn('paper_id',$paperId)->where('status','inprogress')->get();

          foreach($results2 as $resultt2)
          {
            $id = $resultt2->id;
              $cqnid='';$wqnid='';$uqnid='';$marksobt=0;
              //----------get value of cqnid,wqnid,uqnid--------------------------------
                $result = DB::select("SELECT GROUP_CONCAT(qnid) as cqnid,sum(marks) as marksobt FROM `cand_questions` where exam_id='$id' and answered in('answered','answeredandreview') and trim(stdanswer)=trim(cans)");
                if($result)
                {
                  $cqnid 		= $result[0]->cqnid;
                  $marksobt 	= $result[0]->marksobt;
                }

                $result1 = DB::select("SELECT GROUP_CONCAT(qnid) as wqnid FROM `cand_questions` where exam_id='$id' and answered in('answered','answeredandreview') and trim(stdanswer)!=trim(cans)");
                if($result1)
                {
                  $wqnid = $result1[0]->wqnid;
                }

                $result2 = DB::select("SELECT GROUP_CONCAT(qnid) as uqnid FROM `cand_questions` where exam_id='$id' and answered in('unanswered','unansweredandreview')");
                if($result2)
                {
                  $uqnid = $result2[0]->uqnid;
                }
              //------------------------------------------------------------------------
                
              //-------------------Update Exam Resource with End Exam Records-----------
              $result3 = DB::table('cand_test')->where('id',$id)->update([
                'cqnid' 			=> 	$cqnid,
                'wqnid' 			=> 	$wqnid,
                'uqnid' 			=> 	$uqnid,
                'end_on' 			=>	Carbon::now(),
                'end_by'			=>	$this->uid,
                'status'			=>	'over',
                'marksobt'		=>	$marksobt,
                'updated_at'	=>	Carbon::now(),
              ]);
              //------------------------------------------------------------------------
          }
          DB::commit();
          return response()->json([
            "status"  => "success",
          ], 200);
        }
        catch(\Exception $e)
        {
          DB::rollback();
          return response()->json([
            "status"  => "failure",
          ], 200);
        }
      //-------------------------------------------------------------------
      
    //----------------------------------------------------------------
    }
    else if($slot != 'all' && $paperId != 'all')
    {
        //----------find all papersId of given institute on given date and slot------
        $results = DB::select("select GROUP_CONCAT(id) as id from subject_master where from_date like '%$date%' and slot='$slot' and id='$paperId' and inst_uid like '$instUid' group by inst_uid");

        try
        {
          $paperId = explode(',',$results[0]->id);
          $paperIdStr = $results[0]->id;

          $results2 = CandTest::whereIn('paper_id',$paperId)->where('status','inprogress')->get();

          foreach($results2 as $resultt2)
          {
            $id = $resultt2->id;
              $cqnid='';$wqnid='';$uqnid='';$marksobt=0;
              //----------get value of cqnid,wqnid,uqnid--------------------------------
                $result = DB::select("SELECT GROUP_CONCAT(qnid) as cqnid,sum(marks) as marksobt FROM `cand_questions` where exam_id='$id' and answered in('answered','answeredandreview') and trim(stdanswer)=trim(cans)");
                if($result)
                {
                  $cqnid 		= $result[0]->cqnid;
                  $marksobt 	= $result[0]->marksobt;
                }

                $result1 = DB::select("SELECT GROUP_CONCAT(qnid) as wqnid FROM `cand_questions` where exam_id='$id' and answered in('answered','answeredandreview') and trim(stdanswer)!=trim(cans)");
                if($result1)
                {
                  $wqnid = $result1[0]->wqnid;
                }

                $result2 = DB::select("SELECT GROUP_CONCAT(qnid) as uqnid FROM `cand_questions` where exam_id='$id' and answered in('unanswered','unansweredandreview')");
                if($result2)
                {
                  $uqnid = $result2[0]->uqnid;
                }
              //------------------------------------------------------------------------
                
              //-------------------Update Exam Resource with End Exam Records-----------
              $result3 = DB::table('cand_test')->where('id',$id)->update([
                'cqnid' 			=> 	$cqnid,
                'wqnid' 			=> 	$wqnid,
                'uqnid' 			=> 	$uqnid,
                'end_on' 			=>	Carbon::now(),
                'end_by'			=>	$this->uid,
                'status'			=>	'over',
                'marksobt'		=>	$marksobt,
                'updated_at'	=>	Carbon::now(),
              ]);
              //------------------------------------------------------------------------
          }
          DB::commit();
          return response()->json([
            "status"  => "success",
          ], 200);
        }
        catch(\Exception $e)
        {
          DB::rollback();
          return response()->json([
            "status"  => "failure",
          ], 200);
        }
        //---------------------------------------------------------------------------
    }
  }

  public function getActiveExamCount($request)
  {
    $date = $request->date;
    $paperId = [];

    $result = DB::select("select GROUP_CONCAT(id) as id from subject_master where from_date like '%$date%'");

    if($result)
    {
      $paperId = explode(',',$result[0]->id);
    }

    $result1 = CandTest::whereIn('paper_id',$paperId)->where('status','inprogress')->get();

    $count = $result1->count();

    return response()->json([
      "status"  => "success",
      "data"    => $count,
    ], 200);
  }

  public function examReportCountDateInstWise($date,$slot)
  {
    $institutes = User::where('role','EADMIN')->get();
    $data = [];
    $i = 0;

    foreach($institutes as $institute)
    {
      $data[$i++] = [
        'instCode'            =>  $institute->username,
        'instName'            =>  $institute->college_name,
        'allStudents'         =>  $this->getInstStudentsCount($institute->uid,$date,$slot,'all'),
        'overStudents'        =>  $this->getInstStudentsCount($institute->uid,$date,$slot,'over'),
        'inprogressStudents'  =>  $this->getInstStudentsCount($institute->uid,$date,$slot,'inprogress'),
        'unattendStudents'    =>  $this->getInstStudentsCount($institute->uid,$date,$slot,'unattend'),
      ];
    }

    return response()->json([
      "status"  => "success",
      "data"    => $data,
    ], 200);
  }

  public function getInstStudentsCount($inst,$date,$slot,$str)
  {
    $result = null;
    if($str == 'all')
    {
      if($slot == '')
      {
        $result = DB::select("select group_concat(id) as subjectList from subject_master where inst_uid='$inst' and from_date like '%$date%'");

        if($result)
        {
          $array = explode(',',$result[0]->subjectList);
          $count = CandTest::whereIn('paper_id',$array)->count();
          return $count;
        }
        else
        {
          return 0;
        }
      }
      else
      {
        $result = DB::select("select group_concat(id) as subjectList from subject_master where inst_uid='$inst' and from_date like '%$date%' and slot='$slot'");

        if($result)
        {
          $array = explode(',',$result[0]->subjectList);
          $count = CandTest::whereIn('paper_id',$array)->count();
          return $count;
        }
        else
        {
          return 0;
        }
      }
    }
    else if($str=='over')
    {
      if($slot == '')
      {
        $result = DB::select("select group_concat(id) as subjectList from subject_master where inst_uid='$inst' and from_date like '%$date%'");

        if($result)
        {
          $array = explode(',',$result[0]->subjectList);
          $count = CandTest::whereIn('paper_id',$array)->where('status','over')->count();
          return $count;
        }
        else
        {
          return 0;
        }
      }
      else
      {
        $result = DB::select("select group_concat(id) as subjectList from subject_master where inst_uid='$inst' and from_date like '%$date%' and slot='$slot'");

        if($result)
        {
          $array = explode(',',$result[0]->subjectList);
          $count = CandTest::whereIn('paper_id',$array)->where('status','over')->count();
          return $count;
        }
        else
        {
          return 0;
        }
      }
    }
    else if($str=='inprogress')
    {
      if($slot == '')
      {
        $result = DB::select("select group_concat(id) as subjectList from subject_master where inst_uid='$inst' and from_date like '%$date%'");

        if($result)
        {
          $array = explode(',',$result[0]->subjectList);
          $count = CandTest::whereIn('paper_id',$array)->where('status','inprogress')->count();
          return $count;
        }
        else
        {
          return 0;
        }
      }
      else
      {
        $result = DB::select("select group_concat(id) as subjectList from subject_master where inst_uid='$inst' and from_date like '%$date%' and slot='$slot'");

        if($result)
        {
          $array = explode(',',$result[0]->subjectList);
          $count = CandTest::whereIn('paper_id',$array)->where('status','inprogress')->count();
          return $count;
        }
        else
        {
          return 0;
        }
      }
    }
    else if($str=='unattend')
    {
      if($slot == '')
      {
        $result = DB::select("select group_concat(id) as subjectList from subject_master where inst_uid='$inst' and from_date like '%$date%'");
        if($result)
        {
          $list = (string) $result[0]->subjectList;
          $rrr = DB::select("SELECT * FROM `cand_test` WHERE `paper_id` IN ($list) and (status='' or status is null)");
          $count = count($rrr);
          return $count;
        }
        else
        {
          return 0;
        }
      }
      else
      {
        $result = DB::select("select group_concat(id) as subjectList from subject_master where inst_uid='$inst' and from_date like '%$date%' and slot='$slot'");

        if($result)
        {
          $list = (string) $result[0]->subjectList;
          $rrr = DB::select("SELECT * FROM `cand_test` WHERE `paper_id` IN ($list) and (status='' or status is null)");
          $count = count($rrr);
          return $count;
        }
        else
        {
          return 0;
        }
      }
    }
  }

  public function storeStudSubjectMapping($request)
  {
    
    $enrollno         = $request->enrollno;
    $paperId          = $request->paperId;
    $instId           = $request->instId;
    $current_time 			= Carbon::now();

    

    if($instId == null && Auth::user()->role=='EADMIN')
    {
      $instId = Auth::user()->username;
    }
  
    $result           = User::where('username',$enrollno)->where('inst_id',$instId)->first();
    $stdid            = $result->uid;

    $result           = SubjectMaster::where('id',$paperId)->first();
    $programId        = $result->program_id;
    $static_assign    = $result->static_assign;

    DB::beginTransaction();

    try
    {
      
      $result = CandTest::create([
                      'stdid'       =>  $stdid,
                      'inst'        =>  $instId,
                      'paper_id'    =>  $paperId,
                      'program_id'  =>  $programId,
                      'created_at'  =>  $current_time 
      ]);
      $totalQuest = 0;
      if($static_assign)
      {
        $res4             = TopicMaster::where('paper_id',$paperId)->get();
        if($res4)
        { 
                
                $fetchQuery = '';
                $actualMarks = 0;
                $totalQuest = 0;
                foreach($res4 as $record)
                {
                  $topic    = $record->topic;
                  $subtopic = $record->subtopic;
                  $questType= $record->questType;
                  $questMode= $record->questMode;
                  $quest    = $record->questions;
                  $mrk      = $record->marks;
                  $mmarks   = $mrk * $quest;

                  $totalQuest = $totalQuest + $record->questions;

                  $actualMarks = $actualMarks + $mmarks;

                  $fetchQuery = $fetchQuery."(SELECT * FROM  question_set WHERE trim(paper_uid)=trim('$paperId') AND topic = '$topic' AND  subtopic =  '$subtopic' AND difficulty_level = '$questType' AND quest_type='$questMode' AND marks = '$mrk' ORDER BY RAND( )  LIMIT $quest) UNION ";
                }
        }
                
        $fetchQuery = rtrim($fetchQuery," UNION ");
        $res5 = DB::select($fetchQuery);
        
        if(sizeof($res5) != $totalQuest)
        {
          return response()->json([
            "status"  => "failure1".$e->getMessage()
          ], 400);
        }

        $res5dummy = $res5;
        if($res5)
        {
          //------------------Insert Questions into Candidate Questions----------------------
          $j = 1;
          $k = 0;
          $values = array();
          foreach ($res5dummy as $question)
          {
            $values[$k++] = array(
              'exam_id' 					=> $result->id,
              'stdid' 						=> $stdid,
              'inst' 							=> $instId,
              'paper_id' 					=> $paperId,
              'program_id' 				=> $programId,
              'qnid' 							=> $question->qnid,
              'qtopic' 						=> $question->topic,
              'qtype' 						=> $question->difficulty_level,
              'questMode'					=> $question->quest_type,
              'answered' 					=> 'unanswered',
              'cans' 							=> $question->coption,
              'marks' 						=> $question->marks,
              'ip' 								=> request()->ip(),
              'entry_on' 					=> $current_time,
              'qnid_sr' 					=> $j++
            );
          }

          try
          {
            $inserted = DB::table('cand_questions')->insert($values);
            $values = null;
          }
          catch(\Exception $e)
          {
            DB::rollback();
            return response()->json([
              "status"  => "failure2".$e->getMessage()
            ], 400);
          }
        }
        DB::commit();
        return response()->json([
          "status"  => "success"
        ], 200);
      }
      DB::commit();
      return response()->json([
        "status"  => "success"
      ], 200);
    }
    catch(\Exception $e)
    {

      DB::rollback();
      return response()->json([
        "status"  => "failure3".$e->getMessage()
      ], 400);
    }
  }


  public function clearResponse($qnidSr,$examId)
  {
    $result = CandQuestion::where('qnid_sr',$qnidSr)->where('exam_id',$examId)->first();

    $id  = $result->id;

    $res   = $result->update([
      'answered' => 'unanswered',
      'stdanswer'=> ''
    ]);

    $result = DB::statement("insert into cand_questions_copy select * from cand_questions where id='$id'");

    $result2 = CandQuestion::where('exam_id',$examId)->get();
    if($result2)
    {
      return json_encode([
              'status'  => 'success',
              'data'    => new AnswerCollection($result2),
      ],200);
    }
  }

}
?>