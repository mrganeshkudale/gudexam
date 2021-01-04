<?php

namespace App\Http\Resources;
use Auth;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\QuestionSet;

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
             'exam_id'      =>  $single->exam_id,
             'paper_id'     =>  $single->paper_id,
             'program_id'   =>  $single->program_id,
             'answered'     =>  $single->answered,
             'stdanswer'    =>  $single->stdanswer,
             'qnid_sr'      =>  $single->qnid_sr,
             'marks'        =>  $single->marks,
             'question'     =>  QuestionSet::where('qnid',$single->qnid)->first(),
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
