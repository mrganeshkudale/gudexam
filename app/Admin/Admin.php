<?php
namespace App\Admin;
use App\Models\User;
use App\Models\StudentExam;
use App\Models\CandTest;
use App\Models\Elapsed;
use App\Models\QuestionSet;
use App\Models\CandQuestion;
use App\Models\SubjectMaster;
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

  public function clearSession($enrollNo)
  {
    $result   = User::where('username',$enrollNo)->first();
    if($result)
    {
      $uid      = $result->uid;
      $date     = new Carbon('2001-01-01 01:01:01');

      $result1  = Session::where('uid',$uid)->orderBy('starttime','DESC')->first()->update(['endtime' => $date]);

      if($result1)
      {
        return response()->json([
          "status" => "success",
        ], 200);
      }
      else
      {
        return response()->json([
          "status" => "failure",
        ], 400);
      }
    }
    else
    {
      return response()->json([
        "status" => "failure",
      ], 400);
    }
  }

  public function getUserDetails($username)
  {
    $result   = User::where('username',$username)->first();
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

}
?>
