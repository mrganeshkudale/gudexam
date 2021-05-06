<?php

namespace App\Http\Resources;
use Auth;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\QuestionSet;
use App\Models\User;
use App\Models\CandTest;
use App\Models\SubjectMaster;
use App\Models\ProgramMaster;
use Carbon\Carbon;
use App\Http\Resources\ExamResource;
use App\Models\CandQuestionsCopy;


class AnswerCopyCollection extends ResourceCollection
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
             'question'     =>  QuestionSet::where('qnid',$single->qnid)->first(),
             'student'      =>  User::find($single->stdid),
             'institute'    =>  User::where('username',$single->inst)->first(),
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
             
             'entry_on'     =>  $single->entry_on != '' ? Carbon::createFromFormat('Y-m-d H:i:s.u', $single->entry_on, 'UTC')->getPreciseTimestamp(3) : '',

             'answer_on'    =>  $single->answer_on != '' ? Carbon::createFromFormat('Y-m-d H:i:s.u', $single->answer_on, 'UTC')->getPreciseTimestamp(3) : '',

             'cans'         =>  $single->cans,
             'ip'           =>  $single->ip,
           ];
         }
         return $arr;
    }
}
