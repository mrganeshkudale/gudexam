<?php

namespace App\Http\Resources;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\QuestionSet;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\CandTest;
use App\Models\SubjectMaster;
use App\Models\ProgramMaster;
use Carbon\Carbon;
use App\Http\Resources\ExamResource;
use App\Models\CandQuestionsCopy;
use App\Http\Resources\AnswerCopyCollection;


class AnswerCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
     public function toArray($request)
     {
         $arr = [];
         $i=0;
         foreach($this->collection as $single)
         {
           $arr[$i++] = [
             'id'           =>  $single->id,
             'qnid'         =>  $single->qnid,
             'question'     =>  DB::table('question_set')->where('qnid',$single->qnid)->first(),
             'student'      =>  DB::table('users')->where('uid',$single->stdid)->first(),
             'institute'    =>  DB::table('users')->where('username',$single->inst)->first(),
             'exam_id'      =>  $single->exam_id,
             'examData'     =>  new ExamResource(CandTest::find($single->exam_id)),
             'paper_id'     =>  $single->paper_id,
             'subject'      =>  SubjectMaster::find($single->paper_id),
             'program_id'   =>  $single->program_id,
             'program'      =>  ProgramMaster::find($single->program_id),
             'answered'     =>  $single->answered,
             'stdanswer'    =>  $single->stdanswer,
             'qnid_sr'      =>  $single->qnid_sr,
             'marks'        =>  $single->marks,
             'obtmarks'        =>  $single->obtmarks,
             'obtmarks1'        =>  $single->obtmarks1,
             'questMode'    =>  $single->questMode,
             'answerImage'  =>  $single->answerImage,
             
             'entry_on'     =>  $single->entry_on != '' ? Carbon::createFromFormat('Y-m-d H:i:s.u', $single->entry_on, 'UTC')->getPreciseTimestamp(3) : '',

             'answer_on'    =>  $single->answer_on != '' ? Carbon::createFromFormat('Y-m-d H:i:s.u', $single->answer_on, 'UTC')->getPreciseTimestamp(3) : '',

             'cans'         =>  $single->cans,
             'ip'           =>  $single->ip,
             'ansChangeLog' =>  new AnswerCopyCollection(CandQuestionsCopy::where('id',$single->id)->orderBy('entry_on','DESC')->get()),
           ];
         }
         return $arr;
     }

     public function with($request)
     {
       return [
         'status'       				=>	'success',
         'uid'       					  =>	Auth::user()->uid,
         'username'       			=>	Auth::user()->username,
         'role'       					=>	Auth::user()->role,
         'inst_id'       			  =>	Auth::user()->inst_id,
         'program_code'   			=>	Auth::user()->course_code,
         'semester'   					=>	Auth::user()->semester,
       ];
     }
}
