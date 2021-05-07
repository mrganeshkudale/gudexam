<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\StudentController;
use App\Http\Controllers\API\CandidateTestController;
use App\Http\Controllers\API\HeaderImageController;
use App\Http\Controllers\API\ExamController;
use App\Http\Controllers\API\SettingsController;
use App\Http\Controllers\API\ExamSessionController;
use App\Http\Controllers\API\AnswerController;
use App\Http\Controllers\API\SubjectsController;
use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\QuestionSetController;
use App\Http\Controllers\API\GAdminController;
use App\Http\Controllers\API\CAdminController;
use App\Http\Controllers\API\EAdminController;
use App\Http\Controllers\API\ProctorController;
use App\Http\Controllers\API\ProctorDetailsController;
use App\Http\Controllers\API\SessionsController;
use App\Http\Controllers\API\UsersController;
use App\Http\Controllers\API\ConfigurationsController;
use App\Http\Controllers\API\ProgramController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
*/


Route::post('login',[AuthController::class, 'login'])->name('postlogin');
Route::post('appLogin',[AuthController::class, 'appLogin'])->name('postAppLogin');
Route::get('login',[AuthController::class, 'getlogin'])->name('login');
Route::post('OTP/send',[AuthController::class, 'sendOTP'])->name('sendOTP');
Route::post('OTP/resend',[AuthController::class, 'resendOTP'])->name('resendOTP');
Route::post('OTP/verify',[AuthController::class, 'verifyOTP'])->name('verifyOTP');
Route::post('register',[AuthController::class, 'register'])->name('register');
Route::get('settings',[SettingsController::class, 'index'])->name('settings');
Route::get('configurations', [ConfigurationsController::class, 'show'])->name('getConfig');

//--------------------------General Student Exam API----------------------------
Route::middleware(['auth:api'])->group(function()
{
    Route::get('whoAmI',[AuthController::class, 'index'])->name('whoAmI');
    Route::get('isLoggedIn',[AuthController::class, 'isLoggedIn'])->name('isLoggedIn');

    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('exam', [ExamController::class, 'index'])->name('getExam');
    Route::get('exam/{id}', [ExamController::class, 'show'])->name('showExam');
    Route::put('exam/{id}', [ExamController::class, 'update'])->name('putExam');

    Route::put('examSession', [ExamSessionController::class, 'update'])->name('putExam1');
    Route::get('examSession', [ExamSessionController::class, 'show'])->name('getExamSession');

    Route::get('headerImage', [HeaderImageController::class, 'index'])->name('headerImage');

    Route::get('answer', [AnswerController::class, 'index'])->name('getAnswer');
    Route::put('answer/{id}', [AnswerController::class, 'update'])->name('updateAnswer');
    Route::put('answer/{qnidSr}/{examId}', [AnswerController::class, 'updateByExamId'])->name('updateByExamId');

    Route::post('proctor/{id}', [ProctorController::class, 'store'])->name('PostProctor');
    Route::post('proctorDetails/{id}', [ProctorDetailsController::class, 'store'])->name('PostroctorDetails');

    Route::get('paper/{id}', [SubjectsController::class, 'showById'])->name('getSubjectsById');
    Route::get('subject/topic', [SubjectsController::class, 'getTopic'])->name('getTopic');
});
//---------------------------------Student API End------------------------------

//--------------------------Specific ADMIN Roles API----------------------------s
Route::middleware(['auth:api','admin'])->group(function()
{
    Route::get('user', [UsersController::class, 'index'])->name('getUser');
    Route::post('user', [UsersController::class, 'store'])->name('postUser');
    Route::delete('user/{id}', [UsersController::class, 'del'])->name('delUser');
    Route::put('user/{id}', [UsersController::class, 'update'])->name('updateUser');
    Route::post('user/upload', [UsersController::class, 'upload'])->name('uploadUser');
    Route::get('user/{id}', [UsersController::class, 'show'])->name('getUser1');

    Route::put('sessions', [SessionsController::class, 'update'])->name('putSessions');

    Route::put('configurations', [ConfigurationsController::class, 'update'])->name('putConfig');
    Route::get('configurations/{id}', [ConfigurationsController::class, 'index'])->name('getMyConfig');
    Route::post('configurations', [ConfigurationsController::class, 'store'])->name('postConfig');

    Route::get('program', [ProgramController::class, 'index'])->name('getProgram');
    Route::put('program/{id}', [ProgramController::class, 'update'])->name('updateProgram');
    Route::get('program/inst', [ProgramController::class, 'indexProgInst'])->name('indexProgInst');
    Route::delete('program/inst/{id}', [ProgramController::class, 'deleteProgInst'])->name('deleteProgInst');
    Route::post('program', [ProgramController::class, 'store'])->name('storeProgram');
    Route::post('program/upload', [ProgramController::class, 'upload'])->name('uploadProgram');
    Route::post('program/inst/upload', [ProgramController::class, 'uploadProgInst'])->name('uploadProgramInst');
    Route::get('program/{username}', [ProgramController::class, 'show'])->name('showProgram');
    Route::delete('program/{id}', [ProgramController::class, 'del'])->name('delProgram');

    Route::get('paper', [SubjectsController::class, 'show'])->name('getSubjects');
    

    Route::get('questions/{paper_id}', [QuestionSetController::class, 'show'])->name('getQuestions');
    Route::get('questions/specification/compare', [QuestionSetController::class, 'specificationCompare'])->name('getQuestSpecificationCompare');
    Route::delete('questions/{qnid}', [QuestionSetController::class, 'delete'])->name('delQuestions');
    Route::get('question/{qnid}', [QuestionSetController::class, 'getQuestion'])->name('getQuestion');
    Route::post('question/moderate/{qnid}', [QuestionSetController::class, 'updateQuestion'])->name('updateQuestion');

    Route::post('subject', [SubjectsController::class, 'store'])->name('postSubject');
    Route::put('subject/{id}', [SubjectsController::class, 'update'])->name('updateSubject');
    Route::post('subject/upload', [SubjectsController::class, 'upload'])->name('uploadSubject');
    Route::post('subject/test/upload', [SubjectsController::class, 'uploadTest'])->name('uploadTestSubject');
    Route::put('subject/test/{id}', [SubjectsController::class, 'updateTest'])->name('updateTestSubject');
    Route::put('subject/config/{id}', [SubjectsController::class, 'updateConfig'])->name('updateConfigSubject');

    Route::put('subject/config/generic/{id}', [SubjectsController::class, 'updateGenericConfig'])->name('updateGenericConfig');
    
    Route::get('subject', [SubjectsController::class, 'index'])->name('getSubject');
    Route::get('subject/byDate/{date}', [SubjectsController::class, 'getSubjectByDate'])->name('getSubjectByDate');
    Route::get('subject/byDateInst/{date}/{inst}', [SubjectsController::class, 'getSubjectByDateInst'])->name('getSubjectByDateInst');
    Route::get('subject/config/generic', [SubjectsController::class, 'getGenericConfig'])->name('getGenericConfig');

    Route::delete('subject/{id}', [SubjectsController::class, 'del'])->name('delSubject');

    
    Route::post('subject/topic', [SubjectsController::class, 'storeTopic'])->name('storeTopic');
    Route::post('subject/topic/upload', [SubjectsController::class, 'storeTopicUpload'])->name('storeTopicUpload');
    Route::delete('subject/topic/{id}', [SubjectsController::class, 'delTopic'])->name('delTopic');

    Route::post('subject/question/add', [SubjectsController::class, 'storeQuestion'])->name('storeQuestion');
   
    Route::post('subject/question/upload', [SubjectsController::class, 'uploadQuestion'])->name('uploadQuestion');

    Route::post('exam/upload', [ExamController::class, 'upload'])->name('uploadExam');
    Route::post('exam/', [ExamController::class, 'store'])->name('postExam');
    Route::delete('exam/{id}', [ExamController::class, 'del'])->name('delExam');

    Route::get('exam/report/count', [ExamController::class, 'examReportCount'])->name('examReportCount');
    Route::get('exam/report/countDatewise/{date}/{subject}/{slot}', [ExamController::class, 'examReportCountDatewise'])->name('examReportCountDatewise');

    Route::get('exam/dashboardreport/countDateInstWise', [ExamController::class, 'examReportCountDateInstWise'])->name('examReportCountDateInstWise');

    Route::get('exam/bypaperid/type', [ExamController::class, 'examByPaperIdAndType'])->name('examByPaperIdAndType');
    Route::get('exam/log/{enrollno}/{paperId}', [ExamController::class, 'examLog'])->name('getExamLog');

    Route::get('proctor/{enrollno}/{paperId}', [ProctorController::class, 'proctorByEnrollno'])->name('GetProctorByEnrollno');

    Route::get('exam/report/countByDate', [ExamController::class, 'examReportCountByDate'])->name('examReportCountByDate');

    Route::get('exam/autoEnd/count', [ExamController::class, 'getAutoEndExamCount'])->name('getAutoEndExamCount');

    Route::get('exam/active/count', [ExamController::class, 'getActiveExamCount'])->name('getActiveExamCount');

    Route::put('exam/autoEnd/{date}', [ExamController::class, 'autoEndExam'])->name('putAutoEndExam');
});
//------------------------------------------------------------------------------
?>
