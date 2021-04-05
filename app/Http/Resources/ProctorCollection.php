<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\ProctorSnaps;
use App\Models\ProctorSnapDetails;
use App\Http\Resources\ProctorResource;
use Carbon\Carbon;

class ProctorCollection extends ResourceCollection
{
    public function toArray($request)
    {
        $arr = [];
         $i=0;
         foreach($this->collection as $single)
         {
           $arr[$i++] = [
            'id'                =>  $single->id,
            'snapid'            =>  $single->snapid,
            'agerange'          =>  $single->agerange,
            'gender'            =>  $single->gender,
            'beared'            =>  $single->beared,
            'eyeglasses'        =>  $single->eyeglasses,
            
            'created_at'        =>  $single->created_at ? Carbon::createFromFormat('Y-m-d H:i:s', $single->created_at, 'UTC')->getPreciseTimestamp(3) : '',

            'updated_at'        =>  $single->updated_at ? Carbon::createFromFormat('Y-m-d H:i:s', $single->updated_at, 'UTC')->getPreciseTimestamp(3) : '',
           ];
         }
         return $arr;
    }
}
