<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;
use App\Models\User;

class ProgramResource extends JsonResource
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
            'id'                    =>  $this->id,
            'program_code'          =>  $this->program_code,
            'program_name'          =>  $this->program_name,
            'inst'                  =>  new UserResource(User::find($this->inst_uid)),
            'created_at'            =>  $this->created_at,
            'updated_at'            =>  $this->updated_at,
        ];
    }
}
