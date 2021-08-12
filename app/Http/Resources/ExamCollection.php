<?php

namespace App\Http\Resources;
use Illuminate\Support\Facades\Auth;
use App\Models\SubjectMaster;
use App\Models\User;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\PaperResource;
use App\Http\Resources\UserResource;
use Carbon\Carbon;

class ExamCollection extends ResourceCollection
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
        $str='';
        foreach($this->collection as $single)
        {
          $str=$str.$single->paper_id.',';
          $arr[$i++] = [
            'id'          =>  $single->id,
            'stdid'       =>  new UserResource(User::find($single->stdid)),
            'examstatus'  =>  $single->status,
            'curQuestion' =>  $single->curQuestion,
            'starttime'   =>  $single->starttime ? Carbon::createFromFormat('Y-m-d H:i:s.u', $single->starttime, 'UTC')->getPreciseTimestamp(3) : '',
            'endtime'     =>  $single->endtime ? Carbon::createFromFormat('Y-m-d H:i:s.u', $single->endtime, 'UTC')->getPreciseTimestamp(3) : '',
            'startedon'   =>  $single->entry_on ? Carbon::createFromFormat('Y-m-d H:i:s.u', $single->entry_on, 'UTC')->getPreciseTimestamp(3) : '',
            'endon'       =>  $single->end_on ? Carbon::createFromFormat('Y-m-d H:i:s.u', $single->end_on, 'UTC')->getPreciseTimestamp(3) : '',
            'switched'    =>  $single->switched,
            'now'         =>  round(microtime(true) * 1000),
            'paper'       =>  new PaperResource(SubjectMaster::find($single->paper_id)),
            'marksobt'    =>  $single->marksobt,
            'paper_checking_status' => $single->paper_checking,
            'result'      => $single->result,
            'answerFile'    => $single->answerFile,
          ];
        }
        return $arr;
    }

    public function with($request)
    {
      return [
        'status'       				=>	'success',
        'uid'       					=>	Auth::user()->uid,
        'username'       			=>	Auth::user()->username,
        'role'       					=>	Auth::user()->role,
        'inst_id'       			=>	Auth::user()->inst_id,
        'program_code'   			=>	Auth::user()->course_code,
        'semester'   					=>	Auth::user()->semester,
      ];
    }
}
