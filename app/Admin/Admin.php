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

}
?>
