<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\ProctorSnaps;
use App\Models\ProctorSnapDetails;
use Carbon\Carbon;

class ProctorResource extends JsonResource
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
            'id'                =>  $this->id,
            'snapid'            =>  $this->snapid,
            'agerange'          =>  $this->agerange,
            'gender'            =>  $this->gender,
            'created_at'        =>  $this->created_at ? Carbon::createFromFormat('Y-m-d H:i:s.u', $this->created_at, 'UTC')->getPreciseTimestamp(3) : '',
            'updated_at'        =>  $this->updated_at ? Carbon::createFromFormat('Y-m-d H:i:s.u', $this->updated_at, 'UTC')->getPreciseTimestamp(3) : '',
          ];
    }
}
