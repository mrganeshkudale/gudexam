<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\UserResource;
use App\Http\Resources\PaperResource;
use App\Http\Resources\SessionResource;
use App\Models\User;
use App\Models\StudentProctorAllocMaster;
use App\Models\SubjectMaster;
use App\Models\Session;
use Carbon\Carbon;

class ProctorSubjectStudCountCollection extends ResourceCollection
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
            'id'            =>  $single->id,
            'proctor'       =>  new UserResource(User::find($single->uid)),
            'subject'       =>  new PaperResource(SubjectMaster::find($single->paperId)),
            'studcount'     =>  StudentProctorAllocMaster::where('proctorid',$single->uid)->where('paperId',$single->paperId)->count(),
            'lastLogin'     =>  new SessionResource(Session::where('uid',$single->uid)->orderBy('starttime','DESC')->first())
          ];
        }
        return $arr;
    }
}
