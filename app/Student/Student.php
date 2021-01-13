<?php
namespace App\Student;
use App\Models\User;
use App\Http\Resources\ExamCollection;
use App\Http\Resources\AnswerCollection;
use App\Models\QuestionSet;
use App\Models\CandQuestion;
use App\Models\CandTest;
use App\Models\SubjectMaster;
use App\Models\Session;
use App\Models\ExamSession;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Validator;
use Auth;

class Student
{
	private $uid;
	private $stdid;
  private $region;
	private $inst;
  private $course;
  private $semester;
  private $paper_codes;
  private $mobile;

  public function __construct($arr)
	{
		$this->uid 					= $arr->uid;
    $this->stdid        = $arr->username;
    $this->region       = $arr->region;
    $this->inst         = $arr->inst_id;
    $this->course       = $arr->course_code;
    $this->semester     = $arr->semester;
    $this->mobile       = $arr->mobile;
	}

	public function getDuration($paper_id)
  {
    $result = DB::table("subject_master")->select("durations")->where('id',$paper_id)->first();
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

	public function getExams()
	{
		$exams 	= User::find($this->uid)->exams;
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

	public function startExam($exam_id)
	{
		//-------------Get Data from Paper Resource (Subject Master)---------------
			$elapsed =0 ;
			$continueexam =0;
			$exam = CandTest::find($exam_id);
			if($exam)
			{
				$paper_id = $exam->paper_id;
				$stdid 		= $exam->stdid;
				if($stdid != Auth::user()->uid)
				{
					return json_encode([
						'status' => 'failure'
					],401);
				}
		//-----------Validate Paper time with current time before exam start-------
				$subject_master = SubjectMaster::find($paper_id);
				$from_time = $subject_master->from_time;
				$to_time = $subject_master->to_time;

				$from_date = $subject_master->from_date.' '.$from_time;
				$to_date = $subject_master->to_date.' '.$to_time;

				$today = date('Y-m-d H:i:s');
				$fromDate=date('Y-m-d H:i:s', strtotime($from_date));
				$toDate=date('Y-m-d H:i:s', strtotime($to_date));
				$toDay =date('Y-m-d H:i:s', strtotime($today));
				if (($toDay >= $fromDate) && ($toDay <= $toDate))
				{
						//----------------------------Insert Answers in CandQuestion-------
						$insertcount=0;
						$cnt = CandTest::where('paper_id',$paper_id)->where('stdid',$this->uid)->count();
						if(!$cnt)
						{
							return json_encode([
									'status'						=>  'failure'
							],400);
						}
						$current_time 		= 	Carbon::now();
						//---------------------Get Questions from Question Set----------------------
						$questions = DB::table('question_set')->where('paper_id',$paper_id)->get();
						$tot_questions = $questions->count();
						//--------------------------------------------------------------------------
						//----------------------Insert Question in Cand Question Table--------------
						$insertcount=DB::table('cand_questions')->where('stdid',$this->uid)->where('paper_id',$paper_id)->where('inst',$this->inst)->count();


						DB::beginTransaction();

								if(!$insertcount)
								{
										$i=1;
										foreach($questions as $question)
										{
												$values = array(
													'exam_id' 					=> $exam_id,
													'stdid' 						=> $this->uid,
													'inst' 							=> $this->inst,
													'paper_id' 					=> $paper_id,
													'program_id' 				=> $this->getProgramId($paper_id),
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

												try
												{
													$inserted = DB::table('cand_questions')->insert($values);
												}
												catch(\Exception $e)
									      {
													DB::rollBack();
													return response()->json([
								                'status' 		=> 'failure',
								              ],400);
												}
										}
									//-----------------------Update Exam status in CandTest-----------
										$minutes = $this->getDuration($paper_id);
										try
										{
												$result = CandTest::where('id',$exam_id)->update([
													'starttime' 		=> 	Carbon::now(),
													'endtime'				=>	Carbon::now()->addMinutes((integer)$minutes),
													'entry_on' 			=> 	Carbon::now(),
													'examip'				=>	request()->ip(),
													'pa'						=>	'P',
													'status'				=>	'inprogress',
													'updated_at'		=> 	Carbon::now()
												]);
										}
										catch(\Exception $e)
										{
											DB::rollBack();
											return response()->json([
														'status' 		=> 'failure',
													],400);
										}
									//----------------------------------------------------------------
									$elapsed = 0;
								}
								else
								{
										$candQuestionsExisting=1;
										$result = CandTest::select(['continueexam'])->where('id',$exam_id)->first();

										$continueexam = $result->continueexam;
										$continueexam = $continueexam + 1;
										try
										{
												$result = CandTest::where('id',$exam_id)->update([
													'continueexam'	=> 	$continueexam,
													'updated_at'		=> 	Carbon::now()
												]);
										}
										catch(\Exception $e)
										{
												DB::rollBack();
												return response()->json([
																'status' 		=> 'failure',
															],400);
										}
								}


						DB::commit();

						return json_encode([
							'status' 				=> 'success',
						],200);
						//-----------------------------------------------------------------
				}
				else
				{
					return json_encode([
						'status' => 'failure'
					],401);
				}
		//-------------------------------------------------------------------------
			}
			else
			{
				return json_encode([
					'status' => 'failure'
				],400);
			}
		//-------------------------------------------------------------------------
	}

	public function endExam($id)
	{
			$cqnid='';$wqnid='';$uqnid='';$marksobt=0;
			//----------get value of cqnid,wqnid,uqnid--------------------------------
				$result = DB::select("SELECT GROUP_CONCAT(qnid) as cqnid,sum(marks) as marksobt FROM `cand_questions` where exam_id='$id' and answered in('answered','reviewandanswered') and trim(stdanswer)=trim(cans)");
				if($result)
				{
					$cqnid 		= $result[0]->cqnid;
					$marksobt = $result[0]->marksobt;
				}

				$result1 = DB::select("SELECT GROUP_CONCAT(qnid) as wqnid FROM `cand_questions` where exam_id='$id' and answered in('answered','reviewandanswered') and trim(stdanswer)!=trim(cans)");
				if($result1)
				{
					$wqnid = $result1[0]->wqnid;
				}

				$result2 = DB::select("SELECT GROUP_CONCAT(qnid) as uqnid FROM `cand_questions` where exam_id='$id' and answered in('unanswered','reviewandunanswered')");
				if($result2)
				{
					$uqnid = $result2[0]->uqnid;
				}
			//------------------------------------------------------------------------

			//-------------------Update Exam Resource with End Exam Records-----------
			$results = DB::table('cand_test')->where('id',$id)->update([
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
			return json_encode([
				'status' => 'success'
			],200);
	}

	public function getAnswers($id)
	{
		$answers = CandQuestion::where('exam_id',$id)->get();
		if($answers)
		{
			return new AnswerCollection($answers);
		}
		else
		{
			return json_encode([
				'status' => 'failure'
			],400);
		}
	}

	public function updateAnswer(Request $request,$id)
	{
		$results = CandQuestion::where('id',$id)->first();
		$results->answered = $request->answered;
		$results->stdanswer = $request->stdanswer;
		$results->answer_by = $request->answer_by;
		$results->answer_on = Carbon::now();

		$results->save();

		return json_encode([
			'status'						=> 'success',
		],200);
	}

	public function updateReview(Request $request,$id)
	{
		$results = CandQuestion::where('id',$id)->first();
		$results->answered = $request->answered;
		$results->save();

		return json_encode([
			'status'						=> 'success',
		],200);
	}



	public function getProgramId($paper_id)
	{
		$programData = SubjectMaster::select("program_id")->where('id',$paper_id)->first();
		if($programData)
		{
			return $programData->program_id;
		}
		else
		{
			return 0;
		}
	}

	public function updateExamSession($exam_id)
	{
		//-----------------Get Heart Beat Time from config--------------------------
		$heartbeattime = Config::get('constants.HEARTBEATTIME');
		$actualElapsedTime = 0;
		//--------------------------------------------------------------------------

		//--------Search for entry in ExamSession with given exam_id----------------
		$result = ExamSession::where('exam_id',$exam_id)->where('session_state','active')->first();
		DB::beginTransaction();
		if($result)
		{
			//-----check Time difference between now and last_update_time-------------
			$heartbeatdiff = Carbon::now()->diffInSeconds($result->last_update_time);
			if($heartbeatdiff > $heartbeattime)
			{
				//-----------convert first active record to over state------------------
					$result->session_state = 'over';
					$result->updated_at = Carbon::now();
					$result->save();
				//----------------------------------------------------------------------

				//---------------Calculate New Elapsed Time-----------------------------
					$actualElapsedTime = $result->elapsed_time + 2;
				//----------------------------------------------------------------------

				//-----------------Create new Exam Session Entry------------------------
				$values = array(
					'exam_id' 							=> $exam_id,
					'session_start_time'		=> Carbon::now()->subSeconds(2),
					'last_update_time'			=> Carbon::now(),
					'session_state' 				=> 'active',
					'elapsed_time' 					=> $actualElapsedTime,
					'created_at'						=> Carbon::now()
				);
				try
				{
					$inserted = DB::table('exam_session')->insert($values);
				}
				catch(\Exception $e)
				{
					DB::rollBack();
					return response()->json([
								'status' 		=> 'failure',
							],400);
				}
				//----------------------------------------------------------------------
			}
			else
			{
				$cumulativeTime=0;
				//---------------Calculate New Elapsed Time-----------------------------
					$cumulativeResult = DB::select("select elapsed_time from exam_session where exam_id='$exam_id' and session_state='over' order by session_start_time DESC");
					if($cumulativeResult)
					{
						$cumulativeTime = $cumulativeResult[0]->elapsed_time;
					}
				//----------------------------------------------------------------------

				//------------------Update Exam Session --------------------------------
				$result->last_update_time = Carbon::now();
				$diff = Carbon::now()->diffInSeconds($result->session_start_time);
				$result->elapsed_time = $diff + $cumulativeTime;
				$result->updated_at = Carbon::now();
				$result->save();
				$actualElapsedTime = $diff + $cumulativeTime;
				//----------------------------------------------------------------------
			}
			//------------------------------------------------------------------------
		}
		else
		{
			//---------------create ExamSession Entry---------------------------------
			$values = array(
				'exam_id' 							=> $exam_id,
				'session_start_time'		=> Carbon::now()->subSeconds(2),
				'last_update_time'			=> Carbon::now(),
				'session_state' 				=> 'active',
				'elapsed_time' 					=> 2,
				'created_at'						=> Carbon::now()
			);
			try
			{
				$inserted = DB::table('exam_session')->insert($values);
				$actualElapsedTime = 2;
			}
			catch(\Exception $e)
			{
				DB::rollBack();
				return response()->json([
							'status' 		=> 'failure',
						],400);
			}
			//------------------------------------------------------------------------
		}
		DB::commit();
		//--------------------------------------------------------------------------
		return response()->json([
					'status' 				=> 'success',
					'elapsedTime'		=> $actualElapsedTime
				],200);
	}

	public function getExamSession($exam_id)
	{
		$result = ExamSession::select("elapsed_time")->where('exam_id',$exam_id)->where('session_state','active')->orderBy('session_start_time', 'desc')->first();

		if($result)
		{
			return response()->json([
						'status' 				=> 'success',
						'elapsedTime'		=> $result->elapsed_time
					],200);
		}
		else
		{
			return response()->json([
						'status' 				=> 'success',
						'elapsedTime'		=> 0
					],200);
		}
	}

	public function windowSwitchExam($id)
	{
		$result = CandTest::where('id',$id)->first();
		$count = $result->switched + 1;
		$result->switched = $count;
		$result->save();

		return response()->json([
					'status' 				=> 'success',
					'switchedcount' => $count
				],200);
	}
}
?>
