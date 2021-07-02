<?php
namespace App\Student;
use App\Models\User;
use App\Http\Resources\ExamCollection;
use App\Http\Resources\AnswerCollection;
use App\Models\TopicMaster;
use App\Models\CandQuestion;
use App\Models\CandTest;
use App\Models\SubjectMaster;
use App\Models\ExamSession;
use App\Models\ProctorSnaps;
use App\Models\ProctorSnapDetails;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Validator;

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
	$this->uid 			= $arr->uid;
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
		$continueexam =0;
		$exam = CandTest::find($exam_id);

		if($exam)
		{
			DB::beginTransaction();
			$paper_id 	= $exam->paper_id;
			//-----------Validate Paper time with current time before exam start-------
			$subject_master 	= SubjectMaster::find($paper_id);
			$from_time 			= $subject_master->from_time;
			$to_time 			= $subject_master->to_time;
			$static_assign 		= $subject_master->static_assign;


			$from_date 			= $subject_master->from_date.' '.$from_time;
			$to_date 			= $subject_master->to_date.' '.$to_time;

			$today 				= 	date('Y-m-d H:i:s');
			$fromDate			=	date('Y-m-d H:i:s', strtotime($from_date));
			$toDate				=	date('Y-m-d H:i:s', strtotime($to_date));
			$toDay 				=	date('Y-m-d H:i:s', strtotime($today));
			if (($toDay >= $fromDate) && ($toDay <= $toDate))
			{
				//----------------------------Insert Answers in CandQuestion-------
				$insertcount=0;
				$current_time 		= 	Carbon::now();
				$res5 = null;
				$totalQuest = 0;
				if(!$static_assign)
				{
					//---------------------Get Questions from Question Set----------------------
					$questions = TopicMaster::where('paper_id',$paper_id)->get();
					if($questions)
					{
				  		$fetchQuery = '';
					  	$actualMarks = 0;
						foreach($questions as $record)
						{
							$topic    = $record->topic;
							$subtopic = $record->subtopic;
							$quest    = $record->questions;
							$questMode= $record->questMode;
							$questType= $record->questType;
							$mrk      = $record->marks;
							$mmarks   = $mrk * $quest;

							$totalQuest = $totalQuest + $record->questions;

							$actualMarks = $actualMarks + $mmarks;

							$fetchQuery = $fetchQuery."(SELECT * FROM  question_set WHERE trim(paper_uid)=trim('$paper_id') AND  topic = '$topic' AND  subtopic =  '$subtopic' AND difficulty_level = '$questType' AND quest_type='$questMode' AND marks = '$mrk' ORDER BY RAND( )  LIMIT $quest) UNION ";
						}

						$fetchQuery = rtrim($fetchQuery," UNION ");

						try
						{
							$res5 = DB::select($fetchQuery);

							if(sizeof($res5) != $totalQuest)
							{
								return response()->json([
									"status"  => "failure1"
								], 400);
							}
						}
						catch(\Exception $e)
					  	{
							return response()->json([
							  'status' 		=> 'failure'.$e->getMessage(),
							],400);
						}
					}
					//-------------Insert Question in Cand Question Table--------------
					$insertcount=DB::table('cand_questions')->where('stdid',$this->uid)->where('paper_id',$paper_id)->where('inst',$this->inst)->count();

					if(!$insertcount)
					{
						$i=1;
						$values = [];
						foreach($res5 as $question)
						{
							array_push($values, array(
								'exam_id' 					=> $exam_id,
								'stdid' 					=> $this->uid,
								'inst' 						=> $this->inst,
								'paper_id' 					=> $paper_id,
								'program_id' 				=> $this->getProgramId($paper_id),
								'qnid' 						=> $question->qnid,
								'qtopic' 					=> $question->topic,
								'qtype' 					=> $question->difficulty_level,
								'questMode'					=> $question->quest_type,
								'answered' 					=> 'unanswered',
								'cans' 						=> $question->coption,
								'marks' 					=> $question->marks,
								'ip' 						=> request()->ip(),
								'entry_on' 					=> $current_time,
								'qnid_sr' 					=> $i++
							));
						}
						$inserted = DB::table('cand_questions')->insert($values);
						DB::commit();
						//-----------------------Update Exam status in CandTest-----------
						$minutes = $this->getDuration($paper_id);
						try
						{
							$exam->update([
								'starttime' 		=> 	Carbon::now(),
								'endtime'			=>	Carbon::now()->addMinutes((integer)$minutes),
								'entry_on' 			=> 	Carbon::now(),
								'examip'			=>	request()->ip(),
								'pa'				=>	'P',
								'continueexam'		=> '1',
								'status'			=>	'inprogress',
								'updated_at'		=> 	Carbon::now()
							]);
							DB::commit();
						}
						catch(\Exception $e)
						{
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
						$continueexam = $exam->continueexam;
						$continueexam = $continueexam + 1;
						try
						{
							$exam->update([
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
					//-----------------------Update Exam status in CandTest-----------
					$minutes = $this->getDuration($paper_id);
					try
					{
						$continueexam 	= $exam->continueexam;
						if($continueexam == 0)
						{
							$exam->starttime  			= 	Carbon::now();
							$exam->endtime  			= 	Carbon::now()->addMinutes((integer)$minutes);
							$exam->entry_on  			= 	Carbon::now();
							$exam->examip				=	request()->ip();
							$exam->pa					=	'P';
							$exam->continueexam			= 	'1';
							$exam->status				=	'inprogress';
							$exam->updated_at			= 	Carbon::now();
						}
						else
						{
							$exam->continueexam			= 	$exam->continue_exam + 1;
							$exam->examip				=	request()->ip();
						}
						$exam->save();
						DB::commit();
						return json_encode([
							'status' 				=> 'success',
						],200);
					}
					catch(\Exception $e)
					{
						DB::rollBack();
						return response()->json([
							'status' 		=> 'failure',
						],400);
					}
					//----------------------------------------------------------------
				}
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
		$answers = DB::table('cand_questions')->where('exam_id',$id)->get();
		if($answers)
		{
			return json_encode([
				'status' => 'success',
				'data' => new AnswerCollection($answers)
			],200);
		}
		else
		{
			return json_encode([
				'status' => 'failure'
			],400);
		}
	}

	public function getAnswer($id)
	{
		$answers = DB::table('cand_questions')->where('id',$id)->get();
		if($answers)
		{
			return json_encode([
				'status' => 'success',
				'data' => new AnswerCollection($answers)
			],200);
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

		$examId      = $request->answer_by;

		$curQuestion = $request->curQuestion;


		$rrr = CandTest::where('id',$examId)->update([
			'curQuestion' => $curQuestion,
		]);

		$results->save();

		$result = DB::statement("insert into cand_questions_copy select * from cand_questions where id='$id'");

		if($rrr)
		{
			return json_encode([
				'status'						=> 'success',
			],200);
		}
		else
		{
			return json_encode([
				'status'						=> 'failure',
			],200);
		}

	}

	public function updateReview(Request $request,$id)
	{
		$results = CandQuestion::where('id',$id)->first();
		$results->answered = $request->answered;
		$results->save();

		$result = DB::statement("insert into cand_questions_copy select * from cand_questions where id='$id'");

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

		$result1 = CandTest::select("status")->where('id',$exam_id)->first();


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
					$actualElapsedTime = $result->elapsed_time + $heartbeattime;
				//----------------------------------------------------------------------

				//-----------------Create new Exam Session Entry------------------------
				$values = array(
					'exam_id' 							=> $exam_id,
					'session_start_time'		=> Carbon::now()->subSeconds($heartbeattime+2),
					'last_update_time'			=> Carbon::now(),
					'session_state' 			=> 'active',
					'elapsed_time' 				=> $actualElapsedTime,
					'created_at'				=> Carbon::now()
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
								'examStatus'			=> $result1->status,
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
				'session_start_time'				=> Carbon::now()->subSeconds($heartbeattime+2),
				'last_update_time'					=> Carbon::now(),
				'session_state' 					=> 'active',
				'elapsed_time' 						=> $heartbeattime+2,
				'created_at'						=> Carbon::now()
			);
			try
			{
				$inserted = DB::table('exam_session')->insert($values);
				$actualElapsedTime = $heartbeattime+2;
			}
			catch(\Exception $e)
			{
				DB::rollBack();
				return response()->json([
							'status' 		=> 'failure',
							'examStatus'			=> $result1->status,
						],400);
			}
			//------------------------------------------------------------------------
		}
		DB::commit();
		//--------------------------------------------------------------------------
		return response()->json([
					'status' 				=> 'success',
					'elapsedTime'			=> $actualElapsedTime,
					'examStatus'			=> $result1->status,
				],200);
	}

	public function getExamSession($exam_id)
	{
		$result = DB::table('exam_session')->select("elapsed_time")->where('exam_id',$exam_id)->where('session_state','active')->orderBy('session_start_time', 'desc')->first();

		$result1 = DB::table('cand_test')->select("status")->where('id',$exam_id)->first();

		if($result)
		{
			return response()->json([
						'status' 				=> 'success',
						'elapsedTime'			=> $result->elapsed_time,
						'examStatus'			=> $result1->status,
					],200);
		}
		else
		{
			return response()->json([
						'status' 				=> 'success',
						'elapsedTime'			=> 0,
						'examStatus'			=> $result1->status,
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
					'switchedcount' 		=> $count
				],200);
	}

	public function storeSnapshot($examid,$image)
	{
		$path  = public_path().'/snapshots/'.$examid.'_'.Carbon::now()->timestamp.'.jpg';
		$image = str_replace('data:image/jpeg;base64,', '', $image);
    	$image = str_replace(' ', '+', $image);
		$image = base64_decode($image);

		\File::put($path, $image);

		$values = array(
			'examid' 							=> $examid,
			'path'								=> $path,
			'created_at'						=> Carbon::now()
		);

		$inserted = DB::table('proctor_snaps')->insertGetId($values);
		if($inserted)
		{
			return response()->json([
				'status' 				=> 'success',
				'snapid'				=>  $inserted
			],200);
		}
		else
		{
			return response()->json([
				'status' 				=> 'failure'
			],400);
		}
	}

	public function storeSnapshotDetails($examid,$snapid,$agerange,$beard,$eyeglasses,$eyesopen,$gender,$mustache,$smile,$sunglasses)
	{
		$values = array(
			'examid' 							=> $examid,
			'snapid'							=> $snapid,
			'agerange'							=> $agerange,
			'beared'							=> $beard,
			'eyeglasses'						=> $eyeglasses,
			'eyesopen'							=> $eyesopen,
			'gender'							=> $gender,
			'mustache'							=> $mustache,
			'smile'								=> $smile,
			'sunglasses'						=> $sunglasses,
			'created_at'						=> Carbon::now()
		);

		$inserted = ProctorSnapDetails::create($values);
		if($inserted)
		{
			return response()->json([
				'status' 				=> 'success'
			],200);
		}
		else
		{
			return response()->json([
				'status' 				=> 'failure'
			],400);
		}
	}

	public function updateCurQuestion($id,$request)
	{
		$examId = $id;
		$curQuestion = $request->curQuestion;


		$result = CandTest::where('id',$examId)->update([
			'curQuestion' => $curQuestion
		]);

		return response()->json([
			'status' 				=> 'success'
		],200);
	}

	public function updateSubjectiveAnswer($request,$id)
	{
		$answer 			= $request->stdanswer;
		$answered 			= $request->answered;
		$current_time 		= Carbon::now();
		$answer_by 			= $request->answer_by;
		$ip 				= request()->ip();
		$allowImgUp			= $request->allowImgUp;

		$result = CandQuestion::find($id);

		if($allowImgUp == 'Y')
		{
			if(($result->answerImage == '' || $result->answerImage == null) && (trim($answer) == '') && ($answer == ''))
			{
				return json_encode([
					'status'						=> 'failure'
				],400);
			}
		}

		$result->answered 	= $answered;
		$result->stdanswer	= $answer;
		$result->answer_by	= $answer_by;
		$result->answer_on	= $current_time;
		$result->ip			= $ip;

		$result->save();

		$rrr = DB::statement("insert into cand_questions_copy select * from cand_questions where id='$id'");
		if($rrr)
		{
			return json_encode([
				'status'						=> 'success',
			],200);
		}
		else
		{
			return json_encode([
				'status'						=> 'failure',
			],400);
		}

	}

	public function uploadAnswerImage($id,$request)
	{
		//--------------------------------------------------------------
		$result 			= CandQuestion::where('id',$id)->first();
		$answerImgStr 		= $result->answerImage;
		$answerImg 			= explode(',',$result->answerImage);

		if(sizeof($answerImg) >= 5)
		{
			return json_encode([
				'status'						=> 'failure',
			],400);
		}
		//--------------------------------------------------------------
		if($request->file)
		{
				$part 			= rand(100000,999999);
				$validation 	= Validator::make($request->all(), ['file' => 'required|mimes:jpeg,jpg,pdf,doc,docx,xls,xlsx,ppt,pptx|max:5120']);
				$path = $request->file('file')->getRealPath();

				if($validation->passes())
				{
					$image 		= $request->file('file');
					$new_name 	= 'Answer_'.$id.'_'.$part.'.'.$image->getClientOriginalExtension();
					$image->move(public_path('answers'), $new_name);
					$path		=public_path('answers').'/'.$new_name;
					$ansfilepath= $new_name;

					$answer 			= 'answers/'.$new_name;
					$answered 			= $request->answered;
					$current_time 		= Carbon::now();
					$answer_by 			= $request->answer_by;
					$ip 				= request()->ip();

					$url 				= Config::get('constants.PROJURL');

					$answerImgStr 		= trim($answerImgStr.','.$answer,',');

					$result->answerImage = $answerImgStr;
					$result->ip 		 = $ip;
					$result->answer_by   = $answer_by;
					$result->answer_on 	 = $current_time;

					$result->save();

					$answerImg 			= explode(',',$answerImgStr);

					for($i=0;$i< sizeof($answerImg);$i++)
					{
						$answerImg[$i] = $url.'/'.$answerImg[$i];
					}

					if($result)
					{
						return json_encode([
							'status'						=> 'success',
							'path'							=> implode(',',$answerImg),
							'pathIcon'						=> $url.'/assets/images',
						],200);
					}
					else
					{
						return json_encode([
							'status'						=> 'failure',
						],400);
					}
				}
				else
				{
					return response()->json([
						"status"            => "failure",
						"message"           => 'Answer Document must be among jpeg,jpg,doc,docx,xls,xlsx,pdf,ppt,pptx with max 5MB size.',
					], 400);
				}
		}
	}

	public function removeAnswerImage($request,$id)
	{
		$filePath 		= substr($request->filePath, strrpos($request->filePath, '/') + 1);
		$index    		= null;
		$result 		= CandQuestion::find($id);
		$url 			= Config::get('constants.PROJURL');

		$answerImage 	= explode(',',$result->answerImage!==null ? $result->answerImage : '');


		for($i = 0;$i< sizeof($answerImage);$i++)
		{
			if (strpos($answerImage[$i], $filePath) !== false)
			{
				$index = $i;
			}
		}

		if($index >= 0)
		{
			array_splice($answerImage,$index,1);
		}

		if(sizeof($answerImage) > 0 )
		{
			$result->answerImage = implode(',',$answerImage);
		}
		else
		{
			$result->answerImage = '';
		}

		$result->save();

		return json_encode([
			'status'						=> 'success',
			'path'							=> implode(',',$answerImage),
			'pathIcon'						=> $url.'/assets/images',
		],200);
	}

	public function getExamSwitchCount($id)
	{
		$result = CandTest::find($id);

		if($result)
		{
			return json_encode([
				'status'						=> 'success',
				'count'							=> $result->switched
			],200);
		}
		else
		{
			return json_encode([
				'status'						=> 'failure',
			],400);
		}
	}
}
?>
