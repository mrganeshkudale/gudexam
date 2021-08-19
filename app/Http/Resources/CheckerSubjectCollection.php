<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\UserResource;
use App\Http\Resources\PaperResource;
use App\Models\User;
use App\Models\SubjectMaster;
use Carbon\Carbon;

class CheckerSubjectCollection extends ResourceCollection
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
            'instId'        =>  new UserResource(User::find($single->instId)),
            'checker'       =>  new UserResource(User::find($single->uid)),
            'subject'       =>  new PaperResource(SubjectMaster::find($single->paperId)),
            'type'          =>  $single->type,
          ];
        }
        return $arr;
    }
}
