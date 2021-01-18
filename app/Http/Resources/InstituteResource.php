<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InstituteResource extends JsonResource
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
            'inst_id'           =>  $this->inst_id,
            'username'          =>  $this->username,
            'region'            =>  $this->region,
            "mobile"            =>  $this->mobile,
            "email"             =>  $this->email,
            "role"              =>  $this->role,
            "name"              =>  $this->name,
        ];
    }
}
