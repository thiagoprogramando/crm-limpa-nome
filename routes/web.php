<?php

use App\Http\Controllers\Access\Forgout;
use App\Http\Controllers\Access\Login;
use App\Http\Controllers\Access\Registrer;
use App\Http\Controllers\AppController;
use App\Http\Controllers\Assas\AssasController;
use App\Http\Controllers\Client\AppController as ClientAppController;
use App\Http\Controllers\Client\LoginController;
use App\Http\Controllers\Faq\FaqController;
use App\Http\Controllers\Payment\Payment;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Sale\ContractController;
use App\Http\Controllers\Sale\CouponController;
use App\Http\Controllers\Sale\DefaultController;
use App\Http\Controllers\Sale\SaleController;
use App\Http\Controllers\Upload\UploadController;
use App\Http\Controllers\User\ListController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\WalletController;
use App\Http\Controllers\WhiteLabel\ContractController as WhiteLabelContractController;
use Illuminate\Support\Facades\Route;

Route::get('/', [Login::class, 'index'])->name('login');
Route::post('/logon', [Login::class, 'logon'])->name('logon');

Route::get('/login-cliente', [LoginController::class, 'login'])->name('login.cliente');
Route::post('/logon-cliente', [LoginController::class, 'logon'])->name('logon.cliente');

Route::get('/registrer/{id?}/{type?}', [Registrer::class, 'index'])->name('registrer');
Route::post('registrer-user', [Registrer::class, 'registrerUser'])->name('registrer-user');

Route::get('/forgout/{code?}', [Forgout::class, 'forgout'])->name('forgout');
Route::post('send-code-password', [Forgout::class, 'sendCodePassword'])->name('send-code-password');
Route::post('update-password', [Forgout::class, 'updatePassword'])->name('update-password');

Route::get('/preview-contract/{id}', [ContractController::class, 'previewContract'])->name('preview-contract');

Route::middleware(['auth'])->group(function () {

    Route::middleware(['checkMonthly'])->group(function () {

        Route::middleware(['checkAccount'])->group(function () {

            Route::get('/app', [AppController::class, 'handleApp'])->name('app');
            Route::get('/faq', [FaqController::class, 'faq'])->name('faq');

            Route::middleware(['checkWallet'])->group(function () {

                Route::get('/list-network', [UserController::class, 'listNetwork'])->name('list-network');

                Route::get('/createsale/{id}', [SaleController::class, 'create'])->name('createsale');
                Route::post('create-sale', [SaleController::class, 'createSale'])->name('create-sale');

                Route::get('/wallet', [WalletController::class, 'wallet'])->name('wallet');
                Route::post('withdraw-send', [WalletController::class, 'withdrawSend'])->name('withdraw-send');
                Route::get('/receivable', [Payment::class, 'receivable'])->name('receivable');

                Route::post('create-invoice', [SaleController::class, 'createInvoice'])->name('create-invoice');
            });

            Route::get('/manager-sale', [SaleController::class, 'manager'])->name('manager-sale');
            Route::get('/update-sale/{id}', [SaleController::class, 'viewSale'])->name('update-sale');
            Route::post('updated-sale', [SaleController::class, 'updatedSale'])->name('updated-sale');
            Route::post('delete-sale', [SaleController::class, 'deleteSale'])->name('delete-sale');

            Route::get('/invoice-default', [SaleController::class, 'default'])->name('invoice-default');
            Route::get('/delete-invoice/{id}', [SaleController::class, 'deleteInvoice'])->name('delete-invoice');
            Route::get('reprotocol-sale/{id}', [SaleController::class, 'reprotocolSale'])->name('reprotocol-sale');

            Route::get('/send-contract/{id}', [ContractController::class, 'createContract'])->name('send-contract');
            Route::get('/send-default-whatsapp/{id}', [DefaultController::class, 'sendWhatsapp'])->name('send-default-whatsapp');

            Route::get('/createupload/{id}', [UploadController::class, 'create'])->name('createupload');
            Route::get('/create-payment-upload/{id}', [UploadController::class, 'createInvoice'])->name('create-payment-upload');
            Route::post('create-upload', [UploadController::class, 'createSale'])->name('create-upload');

            Route::get('/profile-white-label', [WhiteLabelContractController::class, 'profileContract'])->name('profile-white-label');
        });

        Route::get('/search', [UserController::class, 'search'])->name('search');
        Route::get('/list-client', [UserController::class, 'listClient'])->name('list-client');
        Route::get('/list-user/{type}', [UserController::class, 'listuser'])->name('list-user');

        Route::get('view-notification/{id}', [UserController::class, 'viewNotification'])->name('view-notification');
        Route::get('/create-wallet', [UserController::class, 'createWallet'])->name('create-wallet');
    });

    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::post('update-user', [UserController::class, 'updateProfile'])->name('update-user');
    Route::post('delete-user', [UserController::class, 'deleteUser'])->name('delete-user');
    Route::get('/list-active/{status}', [UserController::class, 'listActive'])->name('list-active');
    Route::get('/send-active/{id}', [UserController::class, 'sendActive'])->name('send-active');

    Route::get('/listproduct', [ProductController::class, 'list'])->name('listproduct');
    Route::get('/createproduct', [ProductController::class, 'create'])->name('createproduct');
    Route::post('create-product', [ProductController::class, 'createProduct'])->name('create-product');
    Route::get('/updateproduct/{id}', [ProductController::class, 'update'])->name('updateproduct');
    Route::post('update-product', [ProductController::class, 'updateProduct'])->name('update-product');
    Route::post('delete-product', [ProductController::class, 'delete'])->name('delete-product');
    Route::post('create-payment', [ProductController::class, 'payment'])->name('create-payment');
    Route::post('delete-payment', [ProductController::class, 'deletePayment'])->name('delete-payment');
    Route::post('update-payment', [ProductController::class, 'updatePayment'])->name('update-payment');
    Route::post('create-item', [ProductController::class, 'createItem'])->name('create-item');
    Route::post('delete-item', [ProductController::class, 'deleteItem'])->name('delete-item');

    Route::get('/lists', [ListController::class, 'list'])->name('lists');
    Route::get('/createlist', [ListController::class, 'create'])->name('createlist');
    Route::post('create-list', [ListController::class, 'createList'])->name('create-list');
    Route::get('/updatelist/{id}', [ListController::class, 'update'])->name('updatelist');
    Route::post('update-list', [ListController::class, 'updateList'])->name('update-list');
    Route::post('delete-list', [ListController::class, 'delete'])->name('delete-list');
    Route::get('/excel-list/{id}', [ListController::class, 'excelList'])->name('excel-list');

    Route::get('/coupons', [CouponController::class, 'coupons'])->name('coupons');
    Route::post('create-coupon', [CouponController::class, 'createCoupon'])->name('create-coupon');
    Route::post('delete-coupon', [CouponController::class, 'deleteCoupon'])->name('delete-coupon');
    Route::post('add-coupon', [CouponController::class, 'addCoupon'])->name('add-coupon');

    Route::get('/createMonthly/{id}', [AssasController::class, 'createMonthly'])->name('createMonthly');
    Route::get('/payMonthly/{id}', [AssasController::class, 'payMonthly'])->name('payMonthly');
    Route::get('/request-invoices/{id}', [AssasController::class, 'requestInvoice'])->name('request-invoices');
    Route::get('/payments', [Payment::class, 'payments'])->name('payments');

    Route::get('/app-cliente', [ClientAppController::class, 'app'])->name('app.cliente');
    Route::get('/invoice-cliente/{sale?}', [ClientAppController::class, 'invoice'])->name('invoice.cliente');
    Route::get('/logout-cliente', [ClientAppController::class, 'logout'])->name('logout.cliente');

    Route::get('/logout', [Login::class, 'logout'])->name('logout');
});
