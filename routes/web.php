<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PatientController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/pasien', [PatientController::class, 'index']);
// Route::post('/pasien', [PatientController::class, 'store']);
Route::get('/dashboard', function () {
    return view('dashboard/dashboard');  // Mengarahkan ke view dashboard.blade.php
});

// Route::get('/api/documentation', function () {
//     return view('vendor.l5-swagger.index');  // Mengarahkan ke view Swagger UI
// });