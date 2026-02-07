<?php

use Illuminate\Support\Facades\Route;
// /Users/bernaldonapitupulu/Documents/Next Level Study/nextlevelstudy-api/app/Http/Controllers/api/AuthController.php
// use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\Api\WilayahController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BankSoalController;
use App\Http\Controllers\Api\TryoutController;
use App\Http\Controllers\Api\TryoutSoalController;

use App\Models\Sekolah;
use App\Http\Controllers\Api\UserTryoutController;
// use app/Http/Controllers/api/AuthController.php
use Illuminate\Http\Request;
use App\Models\Mapel;

Route::get('/ping', function () {
    return response()->json([
        'success' => true,
        'message' => 'API connected'
    ]);
});

Route::get('/wilayah/provinsi', [WilayahController::class, 'provinsi']);
Route::get('/wilayah/kabupaten/{provinsiId}', [WilayahController::class, 'kabupaten']);
Route::get('/wilayah/kecamatan/{kabupatenId}', [WilayahController::class, 'kecamatan']);

Route::get('/auth/google', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    return $request->user();
});

// Route::apiResource('banksoal', BankSoalController::class);
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('banksoal', BankSoalController::class);
    Route::get('/mapel', function () {
        return Mapel::select('id', 'nama', 'tingkat')->orderBy('nama')->get();
    });
    
    Route::get('/banksoal', [BankSoalController::class, 'index']);
    Route::get('/banksoaltryout', [BankSoalController::class, 'listForTryout']);
    // Route::get('/banksoal/tryout', [BankSoalController::class, 'listForTryout']);
    Route::post('/banksoal', [BankSoalController::class, 'store']);
    Route::get('/banksoal/{id}', [BankSoalController::class, 'show']);
    Route::put('/banksoal/{id}', [BankSoalController::class, 'update']);
    
    Route::get('/tryout', [TryoutController::class, 'index']);
    Route::post('/tryout', [TryoutController::class, 'store']);
    Route::get('/tryout/{id}', [TryoutController::class, 'show']);
    Route::put('/tryout/{id}', [TryoutController::class, 'update']);

    Route::get('/tryout/{id}/soal', [TryoutSoalController::class, 'index']);
    Route::get('/tryout/{id}/soal-detail', [TryoutSoalController::class, 'indexDetail']);
    Route::post('/tryout/{id}/soal', [TryoutSoalController::class, 'store']);
    Route::delete('/tryout/{id}/soal/{banksoalId}', [TryoutSoalController::class, 'destroy']);
    Route::put('/tryout/{id}/soal/urutan', [TryoutSoalController::class, 'updateUrutan']);
    Route::put('/tryout/{id}/soal/{banksoalId}/poin', [TryoutSoalController::class, 'updatePoin']);


    // Route::get('/sekolah', function () {return \App\Models\Sekolah::orderBy('nama')->get();});
    // routes/api.php
    Route::get('/sekolah', function () {
        return Sekolah::orderBy('nama')->get();
    });

    Route::get('/user/tryout', [UserTryoutController::class, 'index']);
    Route::get('/user/tryout/{id}', [UserTryoutController::class, 'show']);
    Route::post('/user/tryout/{id}/start', [UserTryoutController::class, 'start']);

    Route::get('/user/tryout/{id}/questions', [UserTryoutController::class, 'questions']);
    Route::post('/user/tryout/{id}/answer', [UserTryoutController::class, 'answer']);
    Route::post('/user/tryout/{id}/finish', [UserTryoutController::class, 'finish']);
    Route::get('/user/tryout/hasil/{tryoutId}', [UserTryoutController::class, 'hasil']);
});