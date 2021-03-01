<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\ProgramMaster;
use Carbon\Carbon;

class PaperResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
          'id'            =>  $this->id,
          'paper_code'    =>  $this->paper_code,
          'paper_name'    =>  $this->paper_name,
          'program'       =>  ProgramMaster::find($this->program_id),
          'semester'      =>  $this->semester,
          'exam_name'     =>  $this->exam_name,
          'marks'         =>  $this->marks,
          'questions'     =>  $this->questions,
          'mark1'         =>  $this->mark1,
          'mark2'         =>  $this->mark2,
          'mark3'         =>  $this->mark3,
          'mark4'         =>  $this->mark4,
          'duration'      =>  $this->durations,
          'from_date'     =>  $this->from_date != '' ? Carbon::createFromFormat('Y-m-d H:i:s.u', $this->from_date, 'UTC')->getPreciseTimestamp(3) : '',
          'to_date'       =>  $this->to_date != '' ? Carbon::createFromFormat('Y-m-d H:i:s.u', $this->to_date, 'UTC')->getPreciseTimestamp(3) : '',
          /*'from_date' => $this->from_date,
          'to_date'=>$this->to_date,*/
          'score_view'    =>  $this->score_view,
          'review_question'=>  $this->review_question,
          'proctoring'    =>  $this->proctoring,
          'photo_capture' =>  $this->photo_capture,
          'capture_interval'=>  $this->capture_interval,
          'negative_marks'=> $this->negative_marks,
          'time_remaining_reminder'       =>  $this->time_remaining_reminder,
          'exam_switch_alerts' => $this->exam_switch_alerts,
          'option_shuffle'  => $this->option_shuffle,
          'question_marks'  =>  $this->question_marks,
          'instructions'    =>  $this->instructions,
          'ph_time'         =>  $this->ph_time,
          'created_at'      =>  $this->created_at,
          'updated_at'      =>  $this->updated_at
        ];
    }
}
