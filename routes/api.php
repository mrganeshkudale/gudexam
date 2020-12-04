<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\StudentController;
use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\GAdminController;
use App\Http\Controllers\API\CAdminController;
use App\Http\Controllers\API\EAdminController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('login',[AuthController::class, 'login'])->name('postlogin');
Route::get('login',[AuthController::class, 'getlogin'])->name('login');
Route::post('getOTP',[AuthController::class, 'getOTP'])->name('getOTP');
Route::post('resendOTP',[AuthController::class, 'resendOTP'])->name('resendOTP');
Route::post('verifyOTP',[AuthController::class, 'verifyOTP'])->name('verifyOTP');
Route::post('registerUser',[AuthController::class, 'registerUser'])->name('registerUser');

Route::middleware('auth:api')->group(function()
{
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('studenthome', [StudentController::class, 'studenthome'])->name('studenthome');
    Route::get('gadminhome', [GAdminController::class, 'gadminhome'])->name('gadminhome');
    Route::get('cadminhome', [CAdminController::class, 'cadminhome'])->name('cadminhome');
    Route::get('eadminhome', [EAdminController::class, 'eadminhome'])->name('eadminhome');
    Route::get('adminhome', [AdminController::class, 'adminhome'])->name('adminhome');


    Route::post('startexamInstructions', [StudentController::class, 'startexamInstructions'])->name('startexamInstructions');
    Route::post('startexam', [StudentController::class, 'startexam'])->name('startexam');
    Route::post('getQuestion', [StudentController::class, 'getQuestion'])->name('getQuestion');
    Route::post('markUnmarkReview', [StudentController::class, 'markUnmarkReview'])->name('markUnmarkReview');
    Route::post('saveAnswer', [StudentController::class, 'saveAnswer'])->name('saveAnswer');
    Route::post('preEndExam', [StudentController::class, 'preEndExam'])->name('preEndExam');
    Route::post('endExam', [StudentController::class, 'endExam'])->name('endExam');
});
?>
