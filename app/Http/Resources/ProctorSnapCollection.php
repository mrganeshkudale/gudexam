<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\ProctorSnaps;
use App\Models\CandTest;
use App\Models\ProctorSnapDetails;
use App\Http\Resources\ProctorResource;
use App\Http\Resources\ExamResource;
use App\Http\Resources\ProctorCollection;
use Carbon\Carbon;

class ProctorSnapCollection extends ResourceCollection
{
    public function toArray($request)
    {
        $arr = [];
         $i=0;
         foreach($this->collection as $single)
         {
           $arr[$i++] = [
             'id'           => $single->id,
            'totalPersons' =>  ProctorSnapDetails::where('snapid',$single->id)->count(),
             'proctorData'  =>  new ProctorCollection(ProctorSnapDetails::where('snapid',$single->id)->get()),
    
             'examData'     =>  new ExamResource(CandTest::find($single->examid)),
             'path'         =>  substr( $single->path, strrpos( $single->path, '/' )+1 ),

             'created_at'        =>  $single->created_at ? Carbon::createFromFormat('Y-m-d H:i:s', $single->created_at, 'UTC')->getPreciseTimestamp(3) : '',

             'updated_at'        =>  $single->updated_at ? Carbon::createFromFormat('Y-m-d H:i:s', $single->updated_at, 'UTC')->getPreciseTimestamp(3) : '',

           ];
         }
         return $arr;
    }
}
