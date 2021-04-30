<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\SubjectMaster;
use App\Models\ProgramMaster;
use App\Http\Resources\ProgramResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\PaperResource;
use App\Http\Resources\PaperCollection;

class CustomExamReportCollection extends ResourceCollection
{
    public function toArray($request)
    {
        $arr = [];
        $i=0;
        $str='';
        foreach($this->collection as $single)
        {
          $str=explode(',',$single->paper_id);

          $res = SubjectMaster::select(DB::raw('group_concat(concat(paper_code,"-",paper_name)) as paper'))->whereIn('id',$str)->first();

          $res1 = SubjectMaster::whereIn('id',$str)->get();

          $arr[$i++] = [
            'stdid'         =>  new UserResource(User::find($single->stdid)),
            'inst'          =>  new UserResource(User::where('username',$single->inst)->first()),
            'program'       =>  new ProgramResource(ProgramMaster::find($single->program_id)),
            'paper'         =>  $res->paper,
            'paperDetails'  =>  new PaperCollection($res1),
          ];

        }
        return $arr;
    }
}
