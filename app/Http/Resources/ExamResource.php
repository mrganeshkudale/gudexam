<?php
namespace App\Http\Resources;
use Auth;
use App\Models\SubjectMaster;
use App\Models\ProgramMaster;
use App\Models\User;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\PaperResource;
use App\Http\Resources\ProgramResource;
use App\Http\Resources\UserResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamResource extends JsonResource
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
            'id'                    =>  $this->id,
            'stdid'                 =>  new UserResource(User::find($this->stdid)),
            'examstatus'            =>  $this->status,
            'curQuestion'           =>  $this->curQuestion,
            'starttime'             =>  $this->starttime,
            'endtime'               =>  $this->endtime,

            'startedon'             =>  $this->entry_on ? Carbon::createFromFormat('Y-m-d H:i:s.u', $this->entry_on, 'UTC')->getPreciseTimestamp(3) : '',

            'endon'                 =>  $this->end_on ? Carbon::createFromFormat('Y-m-d H:i:s.u', $this->end_on, 'UTC')->getPreciseTimestamp(3) : '',
            
            'switched'              =>  $this->switched,
            'now'                   =>  round(microtime(true) * 1000),
            'paper'                 =>  new PaperResource(SubjectMaster::find($this->paper_id)),
            'marksobt'              =>  $this->marksobt,
            'paper_checking_status' =>  $this->paper_checking,
            'paper_moderation_status' => $this->paper_moderation,
            'result'                =>  $this->result,
            'result1'               => $this->result1,
            'answerFile'            =>  $this->answerFile,
        ];
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
