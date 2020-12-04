<?php
namespace App\Student;
use App\Models\User;
use App\Models\Session;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Validator;
use Auth;

class Student
{
	private $stdid;
  private $region;
	private $inst;
  private $course;
  private $semester;
  private $paper_codes;
  private $mobile;

  public function __construct($arr)
	{
    $this->stdid        = $arr->username;
    $this->region       = $arr->region;
    $this->inst         = $arr->inst_id;
    $this->course       = $arr->course_code;
    $this->semester     = $arr->semester;
    $this->mobile       = $arr->mobile;
	}

	public function getDuration($paper_code)
  {
    $result = DB::table("test")->select("durations")->where('paper_code',$paper_code)->first();
		if($result)
		{
			return $result->durations;
		}
		else
		{
			return 0;
		}
  }
  public function getStudUsername()
  {
    return $this->stdid;
  }
  public function getStudRegion()
  {
    return $this->region;
  }
  public function getStudInst()
  {
    return $this->inst;
  }
  public function getStudCourse()
  {
    return $this->course;
  }
  public function getStudSemester()
  {
    return $this->semester;
  }
  public function getStudMobile()
  {
    return $this->mobile;
  }
  public function getStudentsPaperList()
  {
    $results = DB::select("SELECT group_concat(paper_code) as list FROM `student_exams` where username='$this->stdid' and inst='$this->inst' limit 1");
    if($results)
    {
      foreach($results as $result)
      {
        $this->paper_codes = $result->list;
      }
      return $this->paper_codes;
    }
    else
    {
      return '';
    }
  }

  public function getAllocatedSubjectCount()
  {
    $result = DB::select("SELECT id FROM `student_exams` where username='$this->stdid' and inst='$this->inst'");
    if($result)
    {
      return count($result);
    }
    else
    {
      return 0;
    }
  }

  public function getExamCompleatedCount()
  {
    $result = DB::select("SELECT stdid FROM `cand_test` where status='over' and stdid='$this->stdid' and inst='$this->inst'");
    if($result)
    {
      return count($result);
    }
    else
    {
      return 0;
    }
  }

  public function getExamOngoingCount()
  {
    $result = DB::select("SELECT stdid FROM `cand_test` where status='inprogress' and stdid='$this->stdid' and inst='$this->inst'");
    if($result)
    {
      return count($result);
    }
    else
    {
      return 0;
    }
  }

  public function getExamExpiredCount()
  {
    $result = DB::select("SELECT stdid FROM `cand_test` where status='expired' and stdid='$this->stdid' and inst='$this->inst'");
    if($result)
    {
      return count($result);
    }
    else
    {
      return 0;
    }
  }

  public function getExamYetNotGivenCount()
  {
    $cnt  = ($this->getAllocatedSubjectCount() - $this->getExamCompleatedCount() - $this->getExamOngoingCount() - $this->getExamExpiredCount());
    if($cnt >= 0)
    {
      return $cnt;
    }
    else
    {
      return 0;
    }
  }

  public function getStudentExamData()
  {
    $data=array();

    $exam_name='';
    $exam_marks='';
    $exam_tot_questions=0;
    $exam_duration=0;
    $conduction_date='';
    $conduction_time='';
    $stud_exam_start_time='';
    $stud_exam_end_time='';
    $current_exam_status='';
    $stud_actual_start='';
    $stud_actual_end='';


    $results = DB::select("SELECT distinct paper_code FROM `student_exams` where username='$this->stdid' and inst='$this->inst'");
    if($results)
    {
      foreach($results as $result)
      {
        $results0 = DB::select("SELECT paper_code,exam_name,marks,questions,durations,from_date,from_time FROM `test` where paper_code='$result->paper_code' limit 1");
        if($results0)
        {
          foreach($results0 as $result0)
          {
            $exam_name           = $result0->exam_name;
            $exam_marks          = $result0->marks;
            $exam_tot_questions  = $result0->questions;
            $exam_duration       = $result0->durations;
            $conduction_date     = $result0->from_date;
            $conduction_time     = $result0->from_time;
          }
        }

        $results1 = DB::select("SELECT stdid,inst,paper_code,course,starttime,endtime,status,entry_on,end_on FROM `cand_test` where stdid='$this->stdid' and inst='$this->inst' and paper_code='$result->paper_code' limit 1");
        if($results1)
        {
          foreach($results1 as $result1)
          {
            $stud_exam_start_time = $result1->starttime;
            $stud_exam_end_time   = $result1->endtime;
            $current_exam_status  = $result1->status;
            $stud_actual_start    = $result1->entry_on;
            $stud_actual_end      = $result1->end_on;
          }
        }

        array_push($data,[
          'paper_code'          => $result->paper_code,
          'stdid'               => $this->stdid,
          'exam_name'           => $exam_name,
          'exam_marks'          => $exam_marks,
          'exam_tot_questions'  => $exam_tot_questions,
          'exam_duration'       => $exam_duration,
          'conduction_date'     => $conduction_date,
          'conduction_time'     => $conduction_time,
          'stud_exam_start_time'=> $stud_exam_start_time,
          'stud_exam_end_time'  => $stud_exam_end_time,
          'stud_actual_start'   => $stud_actual_start,
          'stud_actual_end'     => $stud_actual_end,
          'current_exam_status' => $current_exam_status
        ]);
        //----------------clear data of variables for next iteration------------
        $exam_name='';
        $exam_marks='';
        $exam_tot_questions=0;
        $exam_duration=0;
        $conduction_date='';
        $conduction_time='';
        $stud_exam_start_time='';
        $stud_exam_end_time='';
        $current_exam_status='';
        $stud_actual_start='';
        $stud_actual_end='';
        //----------------------------------------------------------------------
      }
    }
    return $data;
  }

	public function getInstructions($paper_code)
	{
		$validator = Validator::make(['paper_code' => $paper_code], ['paper_code' => 'required']);
		if ($validator->fails())
		{
			return json_encode([
					'status'						=>  'failure',
					'message' 					=> 	$validator->errors()->first(),
			],400);
    }

		$results = DB::select("SELECT paper_code,exam_name,marks,questions,durations,instructions,negative_marking FROM `test` where paper_code='$paper_code' limit 1");
		if($results)
		{
			foreach($results as $result)
			{
				return json_encode([
						'status'						=>  'success',
						'paper_code' 				=> 	$result->paper_code,
						'exam_name' 				=> 	$result->exam_name,
						'marks'							=>	$result->marks,
						'questions'					=>	$result->questions,
						'duration'					=>	$result->durations,
						'instructions'			=>	$result->instructions,
						'negative_marking'	=>	$result->negative_marking,
				],200);
			}
		}
		else
		{
				return json_encode([
						'status'						=>  'failure',
						'paper_code' 				=> 	'Invalid Paper Code',
				],400);
		}
	}

	public function startexam($paper_code,$flag)
	{
			$validator = Validator::make(['paper_code' => $paper_code, 'flag' => $flag],
			['paper_code' => 'required','flag' => 'required|numeric|min:0|max:1']);
			if ($validator->fails())
			{
				return json_encode([
						'status'						=>  'failure',
						'message' 					=> 	$validator->errors()->first(),
				],400);
			}

			$current_time 		= 	Carbon::now();
			$elapsedtime 			= 	0;
			//---------------------Get Questions from Question Set----------------------
			if($flag==1)
			{
				$questions = DB::table('question_set')->where('paper_code',$paper_code)->get();
			}
			else
			{
				$questions = DB::table('question_set')->where('paper_code',$paper_code)->get();
			}
	    $tot_questions = $questions->count();
			//--------------------------------------------------------------------------

			//----------------------Insert Question in Cand Question Table--------------
			$insertcount=DB::table('cand_questions')->where('stdid',$this->stdid)->where('paper_code',$paper_code)->where('inst',$this->inst)->count();

			if(!$insertcount)
	    {
	    		$i=1;
		    	foreach($questions as $question)
		    	{
							$values = array(
								'stdid' 						=> $this->stdid,
								'inst' 							=> $this->inst,
								'paper_code' 				=> $paper_code,
								'course' 						=> $this->course,
								'qnid' 							=> $question->qnid,
								'qtopic' 						=> $question->topic,
								'qtype' 						=> $question->difficulty_level,
								'answered' 					=> 'unanswered',
								'cans' 							=> $question->coption,
								'marks' 						=> $question->marks,
								'ip' 								=> request()->ip(),
								'entry_on' 					=> $current_time,
								'qnid_sr' 					=> $i++
							);

		    			$inserted = DB::table('cand_questions')->insert($values);
					}

					//------------Enter In Cand Test--------------------------------------
							$minutes = $this->getDuration($paper_code);
				    	$values = array(
								'stdid' 					=> $this->stdid,
								'inst' 						=> $this->inst,
								'paper_code' 			=> $paper_code,
								'course' 					=> $this->course,
								'starttime' 			=> Carbon::now(),
								'endtime' 				=> Carbon::now()->addMinutes((integer)$minutes),
								'status' 					=> 'inprogress',
								'entry_on' 				=> $current_time,
								'examip' 					=> request()->ip(),
								'pa' 							=> 'P'
							);

				    	$inserted = DB::table('cand_test')->insert($values);
				  //--------------------------------------------------------------------

					//-----------------Enter in Elapsed Table-----------------------------
							$values = array(
								'stdid' 					=> $this->stdid,
								'inst' 						=> $this->inst,
								'paper_code' 			=> $paper_code,
								'elapsedTime' 		=> '0',
								'created_at' 			=> $current_time
							);
							$inserted = DB::table('elapsed')->insert($values);
					//--------------------------------------------------------------------
			}
			else
	    {
	    		//---------Continue Test by Getting Current Elapsed Time--------------
	    		$elapsed=DB::table('elapsed')
					->where('paper_code',$paper_code)
					->where('stdid', $this->stdid)
					->where('inst' , $this->inst)
					->first();
	    		if($elapsed)
	    		{
	    				$elapsedtime		=		$elapsed->elapsedTime;
	    		}
					//--------------------------------------------------------------------

					//-------------Update continue test in cand_test----------------------
					$results = DB::table('cand_test')->where('paper_code',$paper_code)
					->where('stdid',$this->stdid)->where('inst',$this->inst)->update(['continueexam' => 'Y']);
					//--------------------------------------------------------------------
	    }
			//----------------------------Elapsed Time Calculation--------------------
				$duration 				= $this->getDuration($paper_code);
				$total_time				=	$duration*60;
				$remaining_time		=	$total_time-$elapsedtime;
			//------------------------------------------------------------------------

			//-------------------------------Get First Question Data------------------
					$question=DB::select("select * from question_set where qnid in(select qnid from cand_questions where qnid_sr='1' and stdid='$this->stdid' and inst='$this->inst' and paper_code='$paper_code')");
			//------------------------------------------------------------------------
			return json_encode([
				'stdid' 						=> $this->stdid,
				'paper_code'				=> $paper_code,
				'inst_id'						=> $this->inst,
				'total_questions'		=> $tot_questions,
				'exam_duration'			=> $duration,
				'total_time'				=> $total_time,
				'remaining_time'		=> $remaining_time,
				'qnid_sr'						=> '1',
				'flag'							=> $flag,
				'questionData'			=> $question[0]
			],200);
	}

	public function getQuestion($paper_code,$old_qnid_sr,$next_qnid_sr,$timer,$flag)
	{
		$validator = Validator::make(['paper_code' => $paper_code,'old_qnid_sr' => $old_qnid_sr,'next_qnid_sr' => $next_qnid_sr,'timer' => $timer,'flag' => $flag],
		[
			'paper_code' => 'required',
			'old_qnid_sr' => 'required|numeric',
			'next_qnid_sr' => 'required|numeric',
			'timer' => 'required|numeric',
			'flag' => 'required|numeric|min:0|max:1'
		]);
		if ($validator->fails())
		{
			return json_encode([
					'status'						=>  'failure',
					'message' 					=> 	$validator->errors()->first(),
			],400);
		}
		//----------------Get Status of Previous Question---------------------------
		$old_question_status 	= $this->getQuestionStatus($paper_code,$old_qnid_sr);
		//--------------------------------------------------------------------------

		//----------------Fetch new Question Data-----------------------------------
		$question=DB::select("select * from question_set where qnid in(select qnid from cand_questions where qnid_sr='$next_qnid_sr' and stdid='$this->stdid' and inst='$this->inst' and paper_code='$paper_code')");
		//--------------------------------------------------------------------------

		//---------------Update Timer in Elapsed Table------------------------------
		$duration 					= $this->getDuration($paper_code);
		$elapsedtime				=	$duration - $timer;


		$elapsed=DB::table('elapsed')
		->where('paper_code',$paper_code)
		->where('stdid', $this->stdid)
		->where('inst' , $this->inst)
		->update(['elapsedTime' => $elapsedtime]);

		//--------------------------------------------------------------------------

		return json_encode([
			'old_qnid_status'		=> $old_question_status,
			'stdid' 						=> $this->stdid,
			'paper_code'				=> $paper_code,
			'inst_id'						=> $this->inst,
			'remaining_time'		=> $timer*60,
			'qnid_sr'						=> $next_qnid_sr,
			'flag'							=> $flag,
			'questionData'			=> $question[0]
		],200);
	}

	public function getQuestionStatus($paper_code,$qnid_sr)
	{
		$question = DB::table('cand_questions')->select('answered')->where('paper_code',$paper_code)
		->where('stdid',$this->stdid)->where('qnid_sr',$qnid_sr)->first();
		if($question)
		{
			return $question->answered;
		}
	}

	public function markUnmarkReview($paper_code,$qnid_sr,$quest_review,$flag)
	{
		$validator = Validator::make(['paper_code' => $paper_code,'qnid_sr' => $qnid_sr,'question_review' => $quest_review,'flag' => $flag],
		[
			'paper_code' => 'required',
			'qnid_sr' => 'required|numeric',
			'question_review' => 'required|numeric|min:0|max:1',
			'flag' => 'required|numeric|min:0|max:1'
		]);
		if ($validator->fails())
		{
			return json_encode([
					'status'						=>  'failure',
					'message' 					=> 	$validator->errors()->first(),
			],400);
		}

		//---------------------------Get Status of Question-------------------------
		$status = '';
		$result = DB::table('cand_questions')->select('answered')->where('paper_code',$paper_code)
		->where('stdid',$this->stdid)->where('qnid_sr',$qnid_sr)->first();
		if($result)
		{
			$status = $result->answered;
		}
		//--------------------------------------------------------------------------

		//--As per value of $quest_review update new value to $status---------------
		if($quest_review)
		{
			if($status == 'unanswered')
			{
				$status = 'unansweredandreview';
			}
			else if($status == 'answered')
			{
				$status = 'answeredandreview';
			}
		}
		else
		{
			if($status == 'unansweredandreview')
			{
				$status = 'unanswered';
			}
			else if($status == 'answeredandreview')
			{
				$status = 'answered';
			}
		}
		//--------------------------------------------------------------------------

		//-----------------Update new status ---------------------------------------
		$result = DB::table('cand_questions')->where('paper_code',$paper_code)
		->where('stdid',$this->stdid)->where('qnid_sr',$qnid_sr)->update(['answered' => $status]);
		//--------------------------------------------------------------------------

		return json_encode([
				'status'   	=> 'success',
				'paper_code'=> $paper_code,
				'stdid'			=> $this->stdid,
				'qnid_sr'		=> $qnid_sr,
				'newstatus' => $status
		],200);
	}

	public function saveAnswer($paper_code,$qnid_sr,$answer,$timer,$flag)
	{
		$validator = Validator::make(['paper_code' => $paper_code,'qnid_sr' => $qnid_sr,'answer' => $answer,'timer' => $timer,'flag' => $flag],
		[
			'paper_code' => 'required',
			'qnid_sr' => 'required',
			'answer' => 'required',
			'timer' => 'required|numeric',
			'flag' => 'required|numeric|min:0|max:1'
		]);
		if ($validator->fails())
		{
			return json_encode([
					'status'						=>  'failure',
					'message' 					=> 	$validator->errors()->first(),
			],400);
		}

		$current_time 		= 	Carbon::now();
		//---------------------------Get Status of Question-------------------------
		$status = '';
		$marks 	= 0;
		$result = DB::table('cand_questions')->select(['answered','cans','marks'])->where('paper_code',$paper_code)
		->where('stdid',$this->stdid)->where('qnid_sr',$qnid_sr)->first();
		if($result)
		{
			$status = $result->answered;
		}
		//--------------------------------------------------------------------------

		//------------------------Fetch optioncode from answer----------------------
			$option = trim(explode(':$:',$answer)[1]);

			if(trim($result->cans) == trim($option))
			{
				$marks = $result->marks;
			}
		//--------------------------------------------------------------------------

		//------------------Calculate new status------------------------------------
		if($status	==	'unanswered')
		{
			$status = 'answered';
		}
		else if($status == 'unansweredandreview')
		{
			$status = 'answeredandreview';
		}
		else if($status == 'answeredandreview')
		{
			$status = 'answeredandreview';
		}
		else if($status == 'answered')
		{
			$status = 'answered';
		}
		//--------------------------------------------------------------------------

		//-----------------Save Answer and status in Candidate Questions Table------
		$result = DB::table('cand_questions')->where('paper_code',$paper_code)
		->where('stdid',$this->stdid)->where('qnid_sr',$qnid_sr)->update(['answered' => $status, 'stdanswer' => $option, 'answer_on' => $current_time, 'answer_by' => $this->stdid, 'obtmarks' => $marks]);
		//--------------------------------------------------------------------------

		//-------------------Update Elapsed Table-----------------------------------
		$duration 				= $this->getDuration($paper_code);
		$total_time				=	$duration*60;
		$remaining_time		=	$timer*60;
		$elapsedtime			= $total_time - $remaining_time;

		$elapsed=DB::table('elapsed')
		->where('paper_code',$paper_code)
		->where('stdid', $this->stdid)
		->where('inst' , $this->inst)
		->update(['elapsedTime' => $elapsedtime, 'updated_at' => $current_time]);

		//--------------------------------------------------------------------------
		return json_encode([
				'status'   					=> 'success',
				'paper_code'				=> $paper_code,
				'stdid'							=> $this->stdid,
				'qnid_sr'						=> $qnid_sr,
				'qnid_sr_status'		=> $status,
				'timer' 						=> $timer
		],200);
	}

	public function endExam($paper_code,$timer,$flag)
	{
		$validator = Validator::make(['paper_code' => $paper_code,'timer' => $timer,'flag' => $flag],
		[
			'paper_code' => 'required',
			'timer' => 'required|numeric',
			'flag' => 'required|numeric|min:0|max:1'
		]);
		if ($validator->fails())
		{
			return json_encode([
					'status'						=>  'failure',
					'message' 					=> 	$validator->errors()->first(),
			],400);
		}

		$current_time 		= 	Carbon::now();
		$marks = 0;
		//--------Parse through Cand Questions and change status of Questions-------
		$results = DB::table('cand_questions')->where('paper_code',$paper_code)
		->where('stdid',$this->stdid)->where('answered','answeredandreview')->update(['answered' => 'answered']);

		$results = DB::table('cand_questions')->where('paper_code',$paper_code)
		->where('stdid',$this->stdid)->where('answered','unansweredandreview')->update(['answered' => 'unanswered']);
		//--------------------------------------------------------------------------

		//----------------------------get sum of marks for correct questions--------
		$results = DB::select("select sum(marks) as marks from cand_questions where paper_code='$paper_code' and stdid='$this->stdid' and stdanswer=cans");

		if($results)
		{
			$marks = $results[0]->marks;
		}
		else
		{
			$marks = 0;
		}
		//--------------------------------------------------------------------------
		//----get correct answer qnid, wrong answer qnid and unanswered qnid--------
		$results = DB::select("SELECT group_concat(qnid) as cqnid FROM `cand_questions` where paper_code='$paper_code' and stdid='$this->stdid' and inst='$this->inst' and stdanswer=cans");
		$cqnid= $results[0]->cqnid;

		$results = DB::select("SELECT group_concat(qnid) as wqnid FROM `cand_questions` where paper_code='$paper_code' and stdid='$this->stdid' and inst='$this->inst' and answered='answered' and stdanswer!=cans");
		$wqnid= $results[0]->wqnid;

		$results = DB::select("SELECT group_concat(qnid) as uqnid FROM `cand_questions` where paper_code='$paper_code' and stdid='$this->stdid' and inst='$this->inst' and answered='unanswered'");
		$uqnid= $results[0]->uqnid;

		//--------------------------------------------------------------------------
		//dd($cqnid.':'.$wqnid.':'.$uqnid.':'.'over'.':'.$current_time.':'.$this->stdid.':'.$marks);
		//--------------Update to Cand Test table-----------------------------------
		$results = DB::table('cand_test')->where('paper_code',$paper_code)
		->where('stdid',$this->stdid)->where('inst',$this->inst)->update(['cqnid' => $cqnid, 'wqnid' => $wqnid, 'uqnid' => $uqnid, 'status' => 'over', 'end_on' => $current_time, 'end_by' => $this->stdid, 'marksobt' => $marks]);
		//--------------------------------------------------------------------------

		//------------------------------update elapsed------------------------------
		$duration 				= $this->getDuration($paper_code);
		$total_time				=	$duration*60;
		$remaining_time		=	$timer*60;
		$elapsedtime			= $total_time - $remaining_time;

		$elapsed=DB::table('elapsed')
		->where('paper_code',$paper_code)
		->where('stdid', $this->stdid)
		->where('inst' , $this->inst)
		->update(['elapsedTime' => $elapsedtime, 'updated_at' => $current_time]);
		//--------------------------------------------------------------------------

		return json_encode([
				'status'   					=> 'success',
				'paper_code'				=> $paper_code,
				'stdid'							=> $this->stdid,
		],200);
	}

	public function preEndExam($paper_code)
	{
		$validator = Validator::make(['paper_code' => $paper_code],
		[
			'paper_code' => 'required',
		]);
		if ($validator->fails())
		{
			return json_encode([
					'status'						=>  'failure',
					'message' 					=> 	$validator->errors()->first(),
			],400);
		}

		$answered 					= $this->answeredQuestCount($paper_code,$this->stdid,$this->inst);
		$unanswered					= $this->unansweredQuestCount($paper_code,$this->stdid,$this->inst);
		$reviewunanswered 	= $this->reviewunansweredQuestCount($paper_code,$this->stdid,$this->inst);
		$reviewanswered 		= $this->reviewansweredQuestCount($paper_code,$this->stdid,$this->inst);

		return json_encode([
				'status'   						=> 'success',
				'paper_code'					=> $paper_code,
				'stdid'								=> $this->stdid,
				'answered'				 		=> $answered,
				'unanswered'					=> $unanswered,
				'reviewunanswered'		=> $reviewunanswered,
				'reviewanswered'			=> $reviewanswered,
		],200);
	}

	public function answeredQuestCount($paper_code,$stdid,$inst)
	{
		$cnt = DB::table('cand_questions')->where('stdid',$stdid)->where('paper_code',$paper_code)->where('inst',$inst)->where('answered','answered')->count();
		return $cnt;
	}

	public function unansweredQuestCount($paper_code,$stdid,$inst)
	{
		$cnt = DB::table('cand_questions')->where('stdid',$stdid)->where('paper_code',$paper_code)->where('inst',$inst)->where('answered','unanswered')->count();
		return $cnt;
	}

	public function reviewunansweredQuestCount($paper_code,$stdid,$inst)
	{
		$cnt = DB::table('cand_questions')->where('stdid',$stdid)->where('paper_code',$paper_code)->where('inst',$inst)->where('answered','reviewandunanswered')->count();
		return $cnt;
	}

	public function reviewansweredQuestCount($paper_code,$stdid,$inst)
	{
		$cnt = DB::table('cand_questions')->where('stdid',$stdid)->where('paper_code',$paper_code)->where('inst',$inst)->where('answered','reviewandanswered')->count();
		return $cnt;
	}
}
