<?php

use App\Http\Controllers\Assas\AssasController;
use App\Http\Controllers\Sale\SaleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('webhook-assas', [AssasController::class, 'webhook'])->name('webhook-assas');
Route::post('webhook-account', [AssasController::class, 'webhookAccount'])->name('webhook-account');
Route::post('webhook-zapsing', [AssasController::class, 'webhookSing'])->name('webhook-zapsing');

Route::post('approved-all', [SaleController::class, 'approvedAll'])->name('approved-all');
Route::post('create-payment', [AssasController::class, 'createPayment'])->name('create-payment');