<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Student\Student;
use App\Admin\Admin;
use App\Admin\Admin1;
use Illuminate\Http\Request;

class ProctorController extends Controller
{
    public function index(Request $request, Admin1 $a1)
    {
        return $a1->getProctors($request);
    }

    public function store($id, Request $request, Student $s)
    {
        if ($request->type == 'snapshot') {
            return $s->storeSnapshot($id, $request->image);
        }
    }


    public function proctorByEnrollno($enrollno, $paperId, Admin $a, Request $request, Admin1 $a1)
    {
        if ($request->type == 'latestSnap') {
            return $a1->proctorLatestByEnrollno($enrollno, $paperId, $request->instId, $request->stdid, $request->examid);
        } else {
            if (Auth::user()->role == 'EADMIN') {
                return $a->proctorByEnrollno($enrollno, $paperId);
            } else {
                $instId = $request->instId;
                return $a1->proctorByEnrollnoInstId($enrollno, $paperId, $instId);
            }
        }
    }

    public function allocateProctor(Request $request, Admin1 $a1)
    {
        return $a1->allocateStudentToProctor($request);
    }

    public function getProctorAllocation(Request $request, Admin1 $a1)
    {
        if ($request->type == 'byPaperId') {
            if ($request->studid != '') {
                return $a1->getSingleStudentToProctorsBySubject($request);
            } else {
                return $a1->getStudentToProctorsBySubject($request);
            }
        } else {
            return $a1->getStudentToProctors($request);
        }
    }

    public function deleteProctorAllocation($id, Admin1 $a1)
    {
        return $a1->deleteStudentToProctors($id);
    }


    public function searchProctorAllocation(Request $request, Admin1 $a1)
    {
        return $a1->searchProctorAllocation($request);
    }

    public function deleteBulkProctorAllocation(Request $request, Admin1 $a1)
    {
        return $a1->deleteBulkProctorAllocation($request);
    }

    public function sendWarning($examid, Request $request, Admin1 $a1)
    {
        return $a1->sendProctorWarning($examid, $request);
    }

    public function uploadStudProctor(Request $request, Admin1 $a1)
    {
        return $a1->uploadStudProctor($request);
    }

    public function notedWarning($warningId, Admin1 $a1)
    {
        return $a1->notedWarning($warningId);
    }

    public function getProctorSummary(Request $request, Admin1 $a1)
    {
        return $a1->getProctorSummary($request);
    }

    public function getProctorDashboard(Request $request, Admin1 $a1)
    {
        return $a1->getProctorDashboard($request);
    }
}
