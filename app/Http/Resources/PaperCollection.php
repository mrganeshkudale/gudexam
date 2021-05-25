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
            'slot'              =>  $single->slot,
            'from_date'         =>  $single->from_date != '' ? Carbon::createFromFormat('Y-m-d H:i:s.u',$single->from_date, 'UTC')->getPreciseTimestamp(3) : '',
            'to_date'           =>  $single->to_date != '' ? Carbon::createFromFormat('Y-m-d H:i:s.u',$single->to_date, 'UTC')->getPreciseTimestamp(3) : '',
            'active'            =>  $single->active,
            'score_view'        =>  $single->score_view,
            'review_question'   =>  $single->review_question,
            'proctoring'        =>  $single->proctoring,
            'photo_capture'     =>  $single->photo_capture,
            'capture_interval'  =>  $single->capture_interval,
            'negative_marking'  =>  $single->negative_marking,
            'negative_marks'    =>  $single->negative_marks,
            'time_remaining_reminder' => $single->time_remaining_reminder,
            'exam_switch'       => $single->exam_switch,
            'exam_switch_alerts'=> $single->exam_switch_alerts,
            'option_shuffle'    =>  $single->option_shuffle,
            'question_marks'    =>  $single->question_marks,
            'instructions'      => $single->instructions,
            'ph_time'           => $single->ph_time,
            'static_assign'     =>  $single->static_assign,
            'questwisetimer'    =>  $single->questwisetimer,
          'secperquest'         =>  $single->secperquest, 
            'created_at'        => $single->created_at,
            'updated_at'        => $single->updated_at,
            'exam_mode'         => $single->exam_mode
          ];
        }
        return $arr;
    }
}
