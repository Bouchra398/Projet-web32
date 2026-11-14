<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\FactureController;
use App\Http\Controllers\FournisseurController;
use App\Http\Controllers\ReceptionController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::apiResource('fournisseurs', FournisseurController::class);
    Route::apiResource('commandes', CommandeController::class);
    Route::apiResource('receptions', ReceptionController::class);
    Route::apiResource('factures', FactureController::class);
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
});