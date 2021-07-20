<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\UserResource;
use App\Http\Resources\PaperResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\StudentProctorAllocMaster;
use App\Models\SubjectMaster;
use App\Models\Session;
use Carbon\Carbon;

class SessionResource extends JsonResource
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
            'session_id'                        =>  $this->session_id,
            'uid'                               =>  $this->uid,
            'role'                              =>  $this->role,
            'ip'                                =>  $this->ip,
            'browser'                           =>  $this->browser,
            'os'                                =>  $this->os,
            'version'                           =>  $this->version,
            'starttime'                         =>  $this->starttime ? Carbon::createFromFormat('Y-m-d H:i:s.u', $this->starttime, 'UTC')->getPreciseTimestamp(3) : '',
            'endtime'                           =>  $this->endtime!== NULL  ? Carbon::createFromFormat('Y-m-d H:i:s.u', $this->endtime, 'UTC')->getPreciseTimestamp(3) : '',
            'created_at'                        =>  $this->created_at!== NULL ? Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at, 'UTC')->getPreciseTimestamp(3) : '',
            'updated_at'                        =>  $this->updated_at!== NULL ? Carbon::createFromFormat('Y-m-d H:i:s', $this->updated_at, 'UTC')->getPreciseTimestamp(3) : '',
        ];
    }
}
