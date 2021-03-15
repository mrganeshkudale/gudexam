<?php
namespace App\Admin;
use App\Models\User;
use App\Models\StudentExam;
use App\Http\Resources\ExamCollection;
use App\Http\Resources\InstProgramCollection;
use App\Http\Resources\PaperCollection;
use App\Http\Resources\TopicCollection;
use App\Http\Resources\PaperResource;
use App\Http\Resources\ExamResource;
use App\Models\CandTest;
use App\Models\TopicMaster;
use App\Models\Elapsed;
use App\Models\QuestionSet;
use App\Models\CandQuestion;
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

      $result1  = Session::where('uid',$uid)->where('endtime',NULL)->orderBy('starttime','DESC')->first();

      if($result1)
      {
        $result1->endtime =  $date;
        $result1->save();
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
    if($flag == '1')
    {
      $result   = User::where('username',$username)->first();
    }
    else
    {
      $result   = User::where('username',$username)->where('inst_id',$instId)->first();
    }

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
        "message"        => "Invalid Institute Id",
      ], 400);
    }
  }


  public function getAllPrograms()
  {
      $result = ProgramMaster::all();
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

  public function getPreviewQuestions($paper_id)
  {
    $result1= [];
    $result = QuestionSet::where('paper_id',$paper_id)->orderBy('qnid', 'ASC')->get();
    if($result)
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
              'status'              => 'ON',
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

    return response()->json([
      "status"          => "success",
      "message"         => "User Deleted Successfully.",
    ], 200);
  }

  public function updateUser($id,$request)
  {
    $result = User::find($id)->update($request->all());
    
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
    
    try
    {
      $user = ProgramMaster::create([
        'program_code' 	=> $progCode,
        'program_name'  => $progName,
        'inst_uid'      => $instId,
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
        'message'       => 'Problem Inserting Program in Database.Probably Duplicate',
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
            $user = ProgramMaster::create([
              'program_code'    => $progCode,
              'program_name'    => $progName,
              'inst_uid'        => $instUid,
              'created_at' 			=> $current_time,
            ]);
        }
        catch(\Exception $e)
        {
          return response()->json([
            'status' 		=> 'failure',
            'message'   => 'Problem Inserting Programs in Database.Probably Duplicate Entry. All Programs till row number '.$i.' in Excel file are Inserted Successfully',
            'row'       =>  $i
          ],400);
        }
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
            'message'   => 'Problem Inserting Subjects in Database.Probably Duplicate Entry',
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
        $rrr              =   ProgramMaster::where('program_code',$programCode)->first();
        $programId        =   $rrr->id;
        $semester         =   $spreadsheet->getActiveSheet()->getCellByColumnAndRow(5, $i)->getValue();
  
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

        $res = User::where('username',$enrollno)->first();
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
      /*
        $res1= User::where('username',$instId)->first();
        if(!$res1)
        {
          return response()->json([
            'status' 		=> 'failure',
            'message'   => 'Institute with Institute Code on Row '.$i.' in Excel file is not available in Users Table. All rows till this are uploaded successfully',
            'row'       =>  $i
          ],400);
        }
        */
        $res2 = SubjectMaster::where('paper_code',$paper_code)->first();
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
              
              $quest    = $record->questions;
              $mrk      = $record->marks;
              $mmarks   = $mrk * $quest;

              $actualMarks = $actualMarks + $mmarks;

              $fetchQuery = $fetchQuery."(SELECT * FROM  question_set WHERE trim(paper_id)=trim('$paper_code') AND topic = '$topic' AND  subtopic =  '$subtopic' AND difficulty_level = '$questType' AND marks = '$mrk' ORDER BY RAND( )  LIMIT $quest) UNION ";
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

  public function delExam($id)
  {
    $result = CandTest::find($id)->delete();

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

    if($subTopic == '' || $subTopic == NULL)
    {
      $subTopic = 0;
    }

    $current_time   = Carbon::now();

    $values = [
      'paper_id'    => $paperId,
      'topic'       => $topic,
      'subtopic'    => $subTopic,
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

      for($i=2;$i<=$highestRow;$i++)
      {
        $paperCode      = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(1, $i)->getValue();
        $topic          = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(2, $i)->getValue();
        $subTopic       = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(3, $i)->getValue();
        $questType      = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(4, $i)->getValue();
        $questions      = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(5, $i)->getValue();
        $marks          = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(6, $i)->getValue();

        if($subTopic == '' || $subTopic == NULL)
        {
          $subTopic = 0;
        }

        $current_time 	 = Carbon::now();

        $res = SubjectMaster::where('paper_code',$paperCode)->first();
        if($res)
        {
          $paperId = $res->id;
          try
          {
            $result         = TopicMaster::create([
              'paper_id'    => $paperId,
              'topic'       => $topic,
              'subtopic'    => $subTopic,
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
      'from_date' => $fromDate->format('Y-m-d H:i:s.u'),
      'to_date'   => $toDate->format('Y-m-d H:i:s.u'),
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
        $exam_name  = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(2, $i)->getValue();
        $marks      = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(3, $i)->getValue();
        $questions  = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(4, $i)->getValue();
        $durations  = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(5, $i)->getValue();
        //------------------------Convert Date from Local to UTC Format--------------------------------------
        $from_date  = new \DateTime($spreadsheet->getActiveSheet()->getCellByColumnAndRow(6, $i)->getValue(), new \DateTimeZone($tz_from));
        $from_date->setTimezone(new \DateTimeZone($tz_to));

        $to_date    = new \DateTime($spreadsheet->getActiveSheet()->getCellByColumnAndRow(7, $i)->getValue(), new \DateTimeZone($tz_from));
        $to_date->setTimezone(new \DateTimeZone($tz_to));
        //--------------------------------------------------------------------------------------------------
        $current_time 	  = Carbon::now();
        
        try
        {
          $result = SubjectMaster::where('paper_code',$paperCode)->update([
            'exam_name' => $exam_name,
            'marks'     => $marks,
            'questions' => $questions,
            'durations' => $durations,
            'from_date' => $from_date->format("Y-m-d H:i:s.u"),
            'to_date'   => $to_date->format("Y-m-d H:i:s.u"),
          ]);
        }
        catch(\Exception $e)
        {
          return response()->json([
            'status' 		=> 'failure',
            'message'   => 'Problem Inserting Test Data in Database.All Tests Data till row number '.$i.' in Excel file are Inserted Successfully',
            'row'       =>  $i
          ],400);
        }
        
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
    $result = SubjectMaster::find($id)->first();

    $result->score_view         =   $request->score_view;
    $result->review_question    =   $request->review_question;
    $result->proctoring         =   $request->proctoring;
    $result->photo_capture      =   $request->photo_capture;
    $result->capture_interval   =   $request->capture_interval;
    $result->negative_marking   =   $request->negative_marking;
    $result->negative_marks     =   $request->negative_marks;
    $result->time_remaining_reminder  =   $request->time_remaining_reminder;
    $result->exam_switch_alerts =   $request->exam_switch_alerts;
    $result->option_shuffle     =   $request->option_shuffle;
    $result->question_marks     =   $request->question_marks;
    $result->ph_time            =   $request->ph_time;
    $result->static_assign      =   $request->static_assign;

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
    $result = DB::select("SELECT * FROM
    (SELECT topic_data.topic, topic_data.paper_code, topic_data.questType, topic_data.marks, CASE WHEN topic_data.expected IS NULL THEN 0 ELSE topic_data.expected END AS expected, CASE WHEN question_data.actual IS NULL THEN 0 ELSE question_data.actual END AS actual  FROM
    
    (SELECT topic_master.topic, topic_master.paper_code, topic_master.questType, topic_master.marks, SUM(topic_master.questions) as expected
    FROM
    (SELECT subject_master.paper_code, topic_master.* FROM subject_master INNER JOIN topic_master on subject_master.id = topic_master.paper_id) topic_master
    GROUP BY
    topic_master.topic, topic_master.paper_code, topic_master.questType, topic_master.marks) topic_data
    
    LEFT JOIN 
    
    (SELECT question_set.topic, question_set.paper_id, question_set.difficulty_level, question_set.marks, COUNT(*) as actual FROM question_set GROUP BY question_set.topic, question_set.paper_id, question_set.difficulty_level, question_set.marks) question_data
    
    ON topic_data.topic = question_data.topic AND topic_data.paper_code = question_data.paper_id AND topic_data.questType = question_data.difficulty_level AND topic_data.marks = question_data.marks) final
    WHERE final.expected > final.actual
    ORDER BY final.paper_code");


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
          'from_date'           =>  $res->from_date,
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
  
    if($type == 'notattend')
    {
      $result = CandTest::where('paper_id',$paper_id)->whereNull('status')->get();
    }
    else
    {
      $result = CandTest::where('paper_id',$paper_id)->where('status',$type)->get();
    }

    return response()->json([
      "status"        => "success",
      "data"          => new ExamCollection($result),
    ], 200);

  }

  public function specificationMatch()
  {
    $result = DB::select("SELECT * FROM
    (SELECT topic_data.topic, topic_data.paper_code, topic_data.questType, topic_data.marks, CASE WHEN topic_data.expected IS NULL THEN 0 ELSE topic_data.expected END AS expected, CASE WHEN question_data.actual IS NULL THEN 0 ELSE question_data.actual END AS actual  FROM
    
    (SELECT topic_master.topic, topic_master.paper_code, topic_master.questType, topic_master.marks, SUM(topic_master.questions) as expected
    FROM
    (SELECT subject_master.paper_code, topic_master.* FROM subject_master INNER JOIN topic_master on subject_master.id = topic_master.paper_id) topic_master
    GROUP BY
    topic_master.topic, topic_master.paper_code, topic_master.questType, topic_master.marks) topic_data
    
    LEFT JOIN 
    
    (SELECT question_set.topic, question_set.paper_id, question_set.difficulty_level, question_set.marks, COUNT(*) as actual FROM question_set GROUP BY question_set.topic, question_set.paper_id, question_set.difficulty_level, question_set.marks) question_data
    
    ON topic_data.topic = question_data.topic AND topic_data.paper_code = question_data.paper_id AND topic_data.questType = question_data.difficulty_level AND topic_data.marks = question_data.marks) final
    WHERE final.expected <= final.actual
    ORDER BY final.paper_code");


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

}
?>