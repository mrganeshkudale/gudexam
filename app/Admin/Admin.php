<?php
namespace App\Admin;
use App\Models\User;
use App\Models\StudentExam;
use App\Models\CandTest;
use App\Models\Elapsed;
use App\Models\QuestionSet;
use App\Models\CandQuestion;
use App\Models\SubjectMaster;
use App\Models\HeaderFooterText;
use App\Models\Session;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Validator;
use Auth;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\InstituteResource;

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
}
?>
