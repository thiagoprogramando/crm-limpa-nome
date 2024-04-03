<?php

use App\Http\Controllers\Access\Login;
use App\Http\Controllers\Access\Registrer;
use App\Http\Controllers\AppController;
use App\Http\Controllers\Assas\AssasController;
use App\Http\Controllers\Payment\Payment;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Sale\DefaultController;
use App\Http\Controllers\Sale\SaleController;
use App\Http\Controllers\User\ListController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\WalletController;
use Illuminate\Support\Facades\Route;

Route::get('/', [Login::class, 'index'])->name('login');
Route::post('/logon', [Login::class, 'logon'])->name('logon');

Route::get('/registrer/{id?}', [Registrer::class, 'index'])->name('registrer');
Route::post('registrer-user', [Registrer::class, 'registrerUser'])->name('registrer-user');

Route::middleware(['auth'])->group(function () {

    Route::get('/app', [AppController::class, 'app'])->name('app');

    Route::middleware(['verify'])->group(function () {
        
        //Sale
        Route::get('/createsale/{id}', [SaleController::class, 'create'])->name('createsale');
        Route::post('create-sale', [SaleController::class, 'createSale'])->name('create-sale');

        Route::get('/manager-sale', [SaleController::class, 'manager'])->name('manager-sale');
        Route::get('/update-sale/{id}', [SaleController::class, 'viewSale'])->name('update-sale');
        Route::get('/invoice-default', [SaleController::class, 'default'])->name('invoice-default');
        Route::post('delete-sale', [SaleController::class, 'deleteSale'])->name('delete-sale');

        Route::get('/send-default-whatsapp/{id}', [DefaultController::class, 'sendWhatsapp'])->name('send-default-whatsapp');

        //Wallet
        Route::get('/wallet', [WalletController::class, 'wallet'])->name('wallet');
        Route::get('/withdraw', [WalletController::class, 'withdraw'])->name('withdraw');
        Route::post('withdraw-send', [WalletController::class, 'withdrawSend'])->name('withdraw-send');

        //Payments
        Route::get('/receivable', [Payment::class, 'receivable'])->name('receivable');

    });

    //Product
    Route::get('/listproduct', [ProductController::class, 'list'])->name('listproduct');
    Route::get('/createproduct', [ProductController::class, 'create'])->name('createproduct');
    Route::post('create-product', [ProductController::class, 'createProduct'])->name('create-product');
    Route::get('/updateproduct/{id}', [ProductController::class, 'update'])->name('updateproduct');
    Route::post('update-product', [ProductController::class, 'updateProduct'])->name('update-product');
    Route::post('delete-product', [ProductController::class, 'delete'])->name('delete-product');
    Route::post('create-payment', [ProductController::class, 'payment'])->name('create-payment');
    Route::post('delete-payment', [ProductController::class, 'deletePayment'])->name('delete-payment');

    //User
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::post('update-user', [UserController::class, 'updateProfile'])->name('update-user');
    Route::post('delete-user', [UserController::class, 'deleteUser'])->name('delete-user');
    Route::get('/search', [UserController::class, 'search'])->name('search');
    Route::get('/listuser/{type}', [UserController::class, 'listuser'])->name('listuser');
    Route::get('view-notification/{id}', [UserController::class, 'viewNotification'])->name('view-notification');

    //List
    Route::get('/lists', [ListController::class, 'list'])->name('lists');
    Route::get('/createlist', [ListController::class, 'create'])->name('createlist');
    Route::post('create-list', [ListController::class, 'createList'])->name('create-list');
    Route::get('/updatelist/{id}', [ListController::class, 'update'])->name('updatelist');
    Route::post('update-list', [ListController::class, 'updateList'])->name('update-list');
    Route::post('delete-list', [ListController::class, 'delete'])->name('delete-list');

    //Payments Assas
    Route::get('/createMonthly/{id}', [AssasController::class, 'createMonthly'])->name('createMonthly');
    Route::get('/payments', [Payment::class, 'payments'])->name('payments');
    

    Route::get('/logout', [Login::class, 'logout'])->name('logout');

});
