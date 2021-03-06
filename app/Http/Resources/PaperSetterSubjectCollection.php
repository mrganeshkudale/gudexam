<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\UserResource;
use App\Http\Resources\PaperResource;
use App\Models\User;
use App\Models\SubjectMaster;

class PaperSetterSubjectCollection extends ResourceCollection
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
            'institute'     =>  new UserResource(User::find($single->instId)),
            'paperSetter'   =>  new UserResource(User::find($single->uid)),
            'subject'       =>  new PaperResource(SubjectMaster::find($single->paperId)),
            'type'          =>  $single->type,
            'conf'          =>  $single->conf
          ];
        }
        return $arr;
    }
}
