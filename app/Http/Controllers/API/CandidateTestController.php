<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Student\Student;
use App\Admin\Admin;
use Illuminate\Http\Request;

class CandidateTestController extends Controller
{
  public function index()
  {
  }

  public function show(Student $s, $id)
  {
    return $s->getCandidateTestData($id);
  }

  public function store(Student $s, Request $request)
  {
    return $s->storeCandidateTest($request->paper_id);
  }

  public function update(Student $s, Request $request, $id)
  {
    return $s->updateCandidateTest($request, $id);
  }

  public function delete(Admin $a, $stdid, $paper_id, $inst)
  {
    return $a->deleteCandidateTest($stdid, $paper_id, $inst);
  }
}
