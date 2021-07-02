<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\CandTest;
use App\Models\ProctorSnapDetails;
use Carbon\Carbon;

class ProctorSnapResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
                'id'           =>   $this->id,
                'totalPersons' =>   ProctorSnapDetails::where('snapid',$this->id)->count(),
                'examData'     =>   CandTest::where('id',$this->examid)->first(),
                'path'         =>   substr( $this->path, strrpos( $this->path, '/' )+1 ),

                'created_at'   =>   $this->created_at ? Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at, 'UTC')->getPreciseTimestamp(3) : '',
                'updated_at'   =>   $this->updated_at ? Carbon::createFromFormat('Y-m-d H:i:s', $this->updated_at, 'UTC')->getPreciseTimestamp(3) : '',
        ];
    }
}
