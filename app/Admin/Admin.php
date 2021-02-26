<?php
namespace App\Admin;
use App\Models\User;
use App\Models\StudentExam;
use App\Http\Resources\ExamCollection;
use App\Models\CandTest;
use App\Models\Elapsed;
use App\Models\QuestionSet;
use App\Models\CandQuestion;
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
use App\Http\Resources\PaperCollection;
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
    $result = ProgramMaster::find($program_id)->exams;
    if($result)
      {
        return response()->json([
          "status"        => "success",
          "data"          => new ExamCollection($result),
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
        $instUid          =   $spreadsheet->getActiveSheet()->getCellByColumnAndRow(3, $i)->getValue();
  
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
        $programId        =   $spreadsheet->getActiveSheet()->getCellByColumnAndRow(3, $i)->getValue();
        $instId           =   $spreadsheet->getActiveSheet()->getCellByColumnAndRow(4, $i)->getValue();
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
      'data'      =>  $result
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

  public function uploadStudSubjectMapping($request)
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
        $uid = $res->uid;
        
        $res = SubjectMaster::where('paper_code',$paper_code)->first();
        $paperId = $res->id;
        $programId = $res->program_id;

        try
        {
          $result = CandTest::create([
            'stdid'       =>  $uid,
            'inst'        =>  $instId,
            'paper_id'    =>  $paperId,
            'program_id'  =>  $programId,
            'created_at'  =>  $current_time 
          ]);
        }
        catch(\Exception $e)
        {
          return response()->json([
            'status' 		=> 'failure',
            'message'   => 'Problem Inserting Student Subject Allocation in Database.Probably Duplicate Entry.All Allocations till row number '.$i.' in Excel file are Inserted Successfully',
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
}
?>
