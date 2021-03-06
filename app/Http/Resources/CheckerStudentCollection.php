<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\UserResource;
use App\Http\Resources\PaperResource;
use App\Models\User;
use App\Models\SubjectMaster;
use Carbon\Carbon;

class CheckerStudentCollection extends ResourceCollection
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
            'checker'       =>  new UserResource(User::find($single->checkerid)),
            'subject'       =>  new PaperResource(SubjectMaster::find($single->paperId)),
            'student'       =>  new UserResource(User::find($single->studid)),
          ];
        }
        return $arr;
    }
}
