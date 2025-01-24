<?php

// use App\Http\Controllers\PasienController;

use App\Http\Controllers\ItemTestController;
use App\Http\Controllers\Lab_TransController;
use App\Http\Controllers\LabTransController;
use App\Http\Controllers\LabTransDetailController;
use App\Http\Controllers\LabTransOtherController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\SupportServiceController;
use App\Models\ItemTest;
use App\Models\LabTransDetail;
use App\Models\LabTransOther;
use App\Models\SupportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/pasien', [PatientController::class, 'index']);
Route::post('/pasien', [PatientController::class, 'store']);
Route::get('/itemtest', [ItemTestController::class, 'index']);
Route::post('/itemtest', [ItemTestController::class, 'store']);
Route::get('/labtrans', [LabTransController::class, 'index']);
Route::post('/labtrans', [LabTransController::class, 'store']);
Route::get('/labtransdetail', [LabTransDetailController::class, 'index']);
Route::post('/labtransdetail', [LabTransDetailController::class, 'store']);
Route::get('/labtransother', [LabTransOtherController::class, 'index']);
// Route::post('/labtransother', [LabTransOtherController::class, 'store']);
Route::get('/supportservice', [SupportServiceController::class, 'index']);
Route::post('/supportservice', [SupportServiceController::class, 'store']);








