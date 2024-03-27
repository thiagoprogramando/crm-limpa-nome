<?php

use App\Http\Controllers\Assas\AssasController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('webhook-assas', [AssasController::class, 'webhook'])->name('webhook-assas');
