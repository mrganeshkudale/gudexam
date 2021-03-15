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

class InstProgramCollection extends ResourceCollection
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
            'program_id'  =>  $single->program_id,
            'program'     =>  new ProgramResource(ProgramMaster::find($single->program_id)),
            'inst'        =>  new UserResource(User::find($single->inst_uid)),
            'created_at'  =>  $single->created_at,
            'updated_at'  =>  $single->updated_at,
          ];
        }
        return $arr;
    }
}
