<?php

namespace App\Http\Resources;
use App\Models\User;
use App\Models\CandTest;
use App\Models\SubjectMaster;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\ExamResource;

class ProctorStudentWarningCollection extends ResourceCollection
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
            'exam'          =>  new ExamResource(CandTest::find($single->examId)),
            'subject'       =>  SubjectMaster::find($single->paperId),
            'inst'          =>  User::where('username',$single->instId)->where('role','EADMIN')->first(),
            'proctor'       =>  User::where('username',$single->proctor)->where('role','PROCTOR')->first(),
            'student'       =>  User::where('username',$single->student)->where('role','STUDENT')->first(),
            'warning'       =>  $single->warning,
            'noted'         =>  $single->noted,
            'warningNo'     =>  $single->warningNo,
            'created_at'    =>  $single->created_at,
          ];
        }
        return $arr;
    }
}
