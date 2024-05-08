<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KelasApiController;
use App\Http\Controllers\SiswaApiController;
use App\Http\Controllers\JadwalApiController;
use App\Http\Controllers\JurusanApiController;
use App\Http\Controllers\MapelApiController;
use App\Http\Controllers\GuruApiController;
use App\Http\Controllers\NilaiApiController;

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

Route::resource('/jurusan', JurusanApiController::class);
Route::resource('/kelas', KelasApiController::class);
Route::resource('/siswa', SiswaApiController::class);
Route::resource('/jadwal', JadwalApiController::class);
Route::resource('/mapel', MapelApiController::class);
Route::resource('/guru', GuruApiController::class);
Route::resource('/nilai', NilaiApiController::class);

