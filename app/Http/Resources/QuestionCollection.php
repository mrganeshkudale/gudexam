<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\PaperResource;
use App\Models\SubjectMaster;

class QuestionCollection extends ResourceCollection
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
        $str='';
        foreach($this->collection as $single)
        {
          $arr[$i++] = [
            'qnid'                  =>  $single->qnid,
            'paper'                 =>  new PaperResource(SubjectMaster::find($single->paper_uid)),
            'question'              =>  $single->question,
            'topic'                 =>  $single->topic,
            'subtopic'              =>  $single->subtopic,
            'qu_fig'                =>  $single->qu_fig,
            'figure'                =>  $single->figure,
            'optiona'               =>  $single->optiona, 
            'a1'                    =>  $single->a1,
            'optionb'               =>  $single->optionb, 
            'a2'                    =>  $single->a2,
            'optionc'               =>  $single->optionc, 
            'a3'                    =>  $single->a3,
            'optiond'               =>  $single->optiond, 
            'a4'                    =>  $single->a4,
            'correctanswer'         =>  $single->correctanswer, 
            'coption'               =>  $single->coption,
            'moderator'             =>  $single->moderator, 
            'updated_status'        =>  $single->updated_status,
            'difficulty_level'      =>  $single->difficulty_level,
            'created_at'            =>  $single->created_at,
            'updated_at'            =>  $single->updated_at,
          ];
        }
        return $arr;
    }
}
