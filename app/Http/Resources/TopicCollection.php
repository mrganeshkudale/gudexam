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

class TopicCollection extends ResourceCollection
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
            'paper'         =>  new PaperResource(SubjectMaster::find($single->paper_id)),
            'topic'         =>  $single->topic,
            'subtopic'      =>  $single->subtopic,
            'questMode'     =>  $single->questMode,
            'questType'     =>  $single->questType,
            'questions'     =>  $single->questions,
            'marks'         =>  $single->marks,
          ];
        }
        return $arr;
    }
}
