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

  public function deleteCandQuestions($stdid,$paper_id)
	{
		$questions = CandQuestion::where('stdid',$stdid)->where('paper_id',$paper_id);
    $questions->delete();

    return response()->json([
      "status"          =>  "success",
    ], 204);
	}

  public function deleteCandidateTest($stdid,$paper_id,$inst)
	{
		$candtest = CandTest::where('stdid',$stdid)->where('paper_id',$paper_id)->where('inst',$inst);
    $candtest->delete();

    return response()->json([
      "status"          =>  "success",
    ], 204);
	}

  public function deleteElapsed($stdid,$paper_id,$inst)
	{
		$elapsed = Elapsed::where('stdid',$stdid)->where('paper_id',$paper_id)->where('inst',$inst);
    $elapsed->delete();

    return response()->json([
      "status"          =>  "success",
    ], 204);
	}

}
?>
