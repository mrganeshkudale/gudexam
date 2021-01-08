<?php

namespace App\Http\Resources;
use Auth;
use App\Models\SubjectMaster;
use App\Models\ProgramMaster;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\PaperResource;
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
        foreach($this->collection as $single)
        {
          $arr[$i++] = [
            'id'          =>  $single->id,
            'examstatus'  =>  $single->status,
            'starttime'   =>  $single->starttime,
            'endtime'     =>  $single->endtime,
            'startedon'   =>  $single->entry_on,
            'endon'       =>  $single->end_on,
            'switched'    =>  $single->switched,
            'now'         =>  date('Y-m-d H:i:s'),
            'paper'       =>  new PaperResource(SubjectMaster::find($single->paper_id)),
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
