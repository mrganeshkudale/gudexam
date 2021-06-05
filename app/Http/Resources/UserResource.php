<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'uid'                       =>  $this->uid,
            'username'                  =>  $this->username,
            'program_name'              =>  $this->program_name,
            'type'                      =>  $this->type,
            'inst_id'                   =>  $this->inst_id,
            'region'                    =>  $this->region,
            'semester'                  =>  $this->semester,
            'mobile'                    =>  $this->mobile,
            'email'                     =>  $this->email,
            'role'                      =>  $this->role,
            'name'                      =>  $this->name,
            'password'                  =>  $this->origpass,
            'college_name'              =>  $this->college_name,
        ];
    }
}
