<?php

use App\Http\Controllers\Assas\AssasController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('webhook-assas', [AssasController::class, 'webhook'])->name('webhook-assas');
Route::post('webhook-account', [AssasController::class, 'webhookAccount'])->name('webhook-account');
Route::post('webhook-zapsing', [AssasController::class, 'webhookSing'])->name('webhook-zapsing');