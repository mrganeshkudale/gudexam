<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\ProgramResource;
use App\Http\Resources\UserResource;
use App\Models\ProgramMaster;
use App\Models\User;
use Carbon\Carbon;

class PaperCollection extends ResourceCollection
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
            'id'                =>  $single->id,
            'paper_code'        =>  $single->paper_code,
            'paper_name'        =>  $single->paper_name,
            'program'           =>  new ProgramResource(ProgramMaster::find($single->program_id)),
            'institute'         =>  new UserResource(User::find($single->inst_uid)),
            'semester'          =>  $single->semester,
            'exam_name'         =>  $single->exam_name,
            'marks'             =>  $single->marks,
            'questions'         =>  $single->questions,
            'durations'         =>  $single->durations,
            'from_date'         =>  Carbon::createFromFormat('Y-m-d H:i:s.u',$single->from_date)->getPreciseTimestamp(3),
            'to_date'           =>  Carbon::createFromFormat('Y-m-d H:i:s.u',$single->to_date)->getPreciseTimestamp(3),

          ];
        }
        return $arr;
    }
}
