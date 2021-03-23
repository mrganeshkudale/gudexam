<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\UserResource;
use App\Models\User;

class ProgramCollection extends ResourceCollection
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
            'id'                    =>  $single->id,
            'program_code'          =>  $single->program_code,
            'program_name'          =>  $single->program_name,
            'inst'                  =>  new UserResource(User::find($single->inst_uid)),
            'created_at'            =>  $single->created_at,
            'updated_at'            =>  $single->updated_at,
          ];
        }
        return $arr;
    }
}
