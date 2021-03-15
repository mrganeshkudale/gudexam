<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/encryptpass', function () {
    $results = User::where('password','')->get();
    foreach($results as $result)
    {
      $uid = $result->uid;
      $origpassword = $result->origpass;
      $password = Hash::make($origpassword);

      $result1 = User::find($uid)->update(['password' => $password]);
    }
});

Route::get('/specification', function () {
  $results = DB::select("select * from specification where paper_code in(select distinct trim(paper_id) from question_set)");
  foreach($results as $result)
  {
    $paper_code = $result->paper_code;
    $rrrr = DB::table('subject_master')->where('paper_code',trim($paper_code))->first();
    $paper_id= $rrrr->id;
    
    for($i=1;$i<=14;$i++)
    {
      $topic = $i;
      $ru = 'KU'.$i;
      $rru = $result->$ru;
      $uu = 'UU'.$i;
      $uuu = $result->$uu;
      $au = 'AU'.$i;
      $aau = $result->$au;


      //dd($paper_code.' '.$topic.' '.$rru.' '.$uuu.' '.$aau);

      $marks = 1;

      $res1 = DB::table('topic_master')->insert([
        'paper_id'      =>  $paper_id,
        'topic'         =>  $topic,
        'subtopic'      =>  0,
        'questType'     =>  'R',
        'questions'     =>  $rru,
        'marks'         =>  '1'
      ]);

      $res1 = DB::table('topic_master')->insert([
        'paper_id'      =>  $paper_id,
        'topic'         =>  $topic,
        'subtopic'      =>  0,
        'questType'     =>  'U',
        'questions'     =>  $uuu,
        'marks'         =>  '1'
      ]);

      $res1 = DB::table('topic_master')->insert([
        'paper_id'      =>  $paper_id,
        'topic'         =>  $topic,
        'subtopic'      =>  0,
        'questType'     =>  'A',
        'questions'     =>  $aau,
        'marks'         =>  '1'
      ]);
    }
  }
});

Route::get('/forceClearDB', function () {
  DB::table('cand_questions')->truncate();
  DB::table('cand_test')->truncate();
  DB::table('exam_session')->truncate();
  DB::table('global_to_cluster_maps')->truncate();
  DB::table('header_footer_text')->truncate();
  DB::table('inst_programs')->truncate();
  DB::table('proctor_snaps')->truncate();
  DB::table('proctor_snap_details')->truncate();
  DB::table('program_master')->truncate();
  DB::table('question_set')->truncate();
  DB::table('sessions')->truncate();
  DB::table('specification')->truncate();
  DB::table('subject_master')->truncate();
  DB::table('topic_master')->truncate();
  DB::table('users')->where('role', '!=', 'ADMIN')->delete();
  echo '<br><br><center><font size=5>Database Force Cleared Successfully</font></center>';
});

Route::fallback(function ()
{
    return File::get(public_path() . '/index.html');
});
