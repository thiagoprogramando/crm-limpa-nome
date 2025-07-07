<?php

use App\Http\Controllers\Access\TermsUsabilityContract;
use App\Http\Controllers\Gateway\AssasController;
use App\Http\Controllers\Sale\ContractController;
use App\Http\Controllers\Sale\SaleController;

use Illuminate\Support\Facades\Route;

Route::post('webhook-assas', [AssasController::class, 'webhook'])->name('webhook-assas');

Route::post('approved-all-payment', [SaleController::class, 'approvedAllPayment'])->name('approved-all-payment');
Route::post('create-payment', [AssasController::class, 'createPayment'])->name('create-payment');

Route::post('sign-sale', [ContractController::class, 'signSale'])->name('sign-sale');
Route::post('sign-terms', [TermsUsabilityContract::class, 'signTerms'])->name('sign-terms');