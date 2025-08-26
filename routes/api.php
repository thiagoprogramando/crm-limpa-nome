<?php

use App\Http\Controllers\Assas\AssasController;
use App\Http\Controllers\Sale\ContractController;
use App\Http\Controllers\Sale\SaleController;

use Illuminate\Support\Facades\Route;

Route::post('webhook-assas', [AssasController::class, 'webhook'])->name('webhook-assas');
Route::post('webhook-zapsing', [AssasController::class, 'webhookSing'])->name('webhook-zapsing');

Route::post('approved-all-payments', [SaleController::class, 'approvedAll'])->name('approved-all-payments');
Route::post('create-payment', [AssasController::class, 'createPayment'])->name('create-payment');

Route::post('sign-sale', [ContractController::class, 'signSale'])->name('sign-sale');