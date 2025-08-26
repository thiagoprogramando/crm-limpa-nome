<?php

use App\Http\Controllers\Access\Forgout;
use App\Http\Controllers\Access\Login;
use App\Http\Controllers\Access\Registrer;
use App\Http\Controllers\AppController;
use App\Http\Controllers\Assas\AssasController;
use App\Http\Controllers\Assets\BannerController;
use App\Http\Controllers\Assets\PostController;
use App\Http\Controllers\Client\AppController as ClientAppController;
use App\Http\Controllers\Client\LoginController;
use App\Http\Controllers\Faq\FaqController;
use App\Http\Controllers\Finance\RecurrenceController;
use App\Http\Controllers\Payment\Payment;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Sale\ContractController;
use App\Http\Controllers\Sale\CouponController;
use App\Http\Controllers\Sale\InvoiceController;
use App\Http\Controllers\Sale\SaleController;
use App\Http\Controllers\Trash\RecoverController;
use App\Http\Controllers\Trash\TrashController;
use App\Http\Controllers\User\ListController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\WalletController;
use App\Http\Controllers\WhiteLabel\ContractController as WhiteLabelContractController;
use Illuminate\Support\Facades\Route;

Route::get('/', [Login::class, 'index'])->name('login');
Route::post('/logon', [Login::class, 'store'])->name('logon');

Route::get('/login-cliente', [LoginController::class, 'index'])->name('login.cliente');
Route::post('/logon-cliente', [LoginController::class, 'store'])->name('logon.cliente');

Route::get('/registrer/{id?}/{fixed_cost?}', [Registrer::class, 'index'])->name('registrer');
Route::post('registrer-user', [Registrer::class, 'store'])->name('registrer-user');

Route::get('/forgout/{code?}', [Forgout::class, 'index'])->name('forgout');
Route::post('send-code-password', [Forgout::class, 'store'])->name('send-code-password');
Route::post('update-password', [Forgout::class, 'update'])->name('update-password');

Route::get('/preview-contract/{id}', [ContractController::class, 'previewContract'])->name('preview-contract');

Route::middleware(['auth'])->group(function () {

    Route::middleware(['checkMonthly'])->group(function () {

        Route::middleware(['checkAccount'])->group(function () {

            Route::get('/app', [AppController::class, 'index'])->name('app');
            Route::get('/faq', [FaqController::class, 'faq'])->name('faq');

            Route::middleware(['checkWallet'])->group(function () {

                Route::get('/list-network', [UserController::class, 'listNetwork'])->name('list-network');

                Route::get('/create-sale/{product}/{type?}/{user?}', [SaleController::class, 'create'])->name('create-sale');
                Route::post('created-client-sale', [SaleController::class, 'createdClientSale'])->name('created-client-sale');
                Route::post('created-payment-sale', [SaleController::class, 'createdPaymentSale'])->name('created-payment-sale');

                Route::get('/wallet', [WalletController::class, 'wallet'])->name('wallet');
                Route::post('withdraw-send', [WalletController::class, 'withdrawSend'])->name('withdraw-send');
                Route::get('/receivable', [Payment::class, 'receivable'])->name('receivable');

                Route::post('create-invoice', [SaleController::class, 'createInvoice'])->name('create-invoice');
            });

            Route::get('/sales', [SaleController::class, 'index'])->name('sales');
            Route::get('/view-sale/{id}', [SaleController::class, 'show'])->name('view-sale');
            Route::post('created-sale-excel/{product}/{type?}', [SaleController::class, 'createdSaleExcel'])->name('created-sale-excel');
            Route::post('created-sale-association/{product}/{type?}', [SaleController::class, 'createdSaleAssociation'])->name('created-sale-association');
            Route::post('updated-sale', [SaleController::class, 'update'])->name('updated-sale');
            Route::post('deleted-sale', [SaleController::class, 'destroy'])->name('deleted-sale');
            Route::post('created-sale-excel/{product}', [SaleController::class, 'createdSaleExcel'])->name('created-sale-excel');
            
            Route::get('/send-contract/{id}', [ContractController::class, 'store'])->name('send-contract');

            Route::get('/view-invoice/{id}', [InvoiceController::class, 'show'])->name('view-invoice');
            Route::post('created-invoice', [InvoiceController::class, 'store'])->name('created-invoice');
            Route::post('updated-invoice', [InvoiceController::class, 'update'])->name('updated-invoice');
            Route::post('deleted-invoice', [InvoiceController::class, 'destroy'])->name('deleted-invoice');

            Route::get('reprotocol-sale/{id}', [SaleController::class, 'reprotocolSale'])->name('reprotocol-sale');

            

            Route::get('/trash-sales', [TrashController::class, 'trashSales'])->name('trash-sales');
            Route::get('/trash-users', [TrashController::class, 'trashUsers'])->name('trash-users');
            Route::post('sale-recover', [RecoverController::class, 'recoverSale'])->name('sale-recover');
            Route::post('user-recover', [RecoverController::class, 'recoverUser'])->name('user-recover');
        });

        Route::get('/list-client', [UserController::class, 'listClient'])->name('list-client');
        Route::get('/list-user/{type}', [UserController::class, 'listuser'])->name('list-user');

        Route::get('view-notification/{id}', [UserController::class, 'viewNotification'])->name('view-notification');
        Route::get('/create-wallet', [UserController::class, 'createWallet'])->name('create-wallet');
    });

    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::post('updated-user', [UserController::class, 'update'])->name('updated-user');
    Route::post('deleted-user', [UserController::class, 'destroy'])->name('deleted-user');
    
    Route::get('/profile-white-label', [WhiteLabelContractController::class, 'profileContract'])->name('profile-white-label');

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

    Route::get('/update-invoice/{id}/{value}/{dueDate}/{callback?}/{commission?}/{wallet?}/', [AssasController::class, 'updateInvoice'])->name('update-invoice');
    Route::get('/payments', [Payment::class, 'payments'])->name('payments');

    Route::get('/app-cliente', [ClientAppController::class, 'app'])->name('app-cliente');
    Route::get('/invoice-cliente/{sale?}', [ClientAppController::class, 'invoice'])->name('invoice-cliente');
    Route::get('/logout-cliente', [ClientAppController::class, 'logout'])->name('logout-cliente');

    Route::middleware(['checkAdmin'])->group(function () {

        Route::post('/created-banner', [BannerController::class, 'store'])->name('created-banner');
        Route::post('/deleted-banner/{id}', [BannerController::class, 'destroy'])->name('deleted-banner');

        Route::post('/created-post', [PostController::class, 'store'])->name('created-post');
        Route::post('/deleted-post/{id}', [PostController::class, 'destroy'])->name('deleted-post');

        Route::get('/products', [ProductController::class, 'index'])->name('products');
        Route::get('/product/{id}', [ProductController::class, 'show'])->name('product');
        Route::get('/create-product', [ProductController::class, 'form'])->name('create-product');
        Route::post('created-product', [ProductController::class, 'store'])->name('created-product');
        Route::post('updated-product', [ProductController::class, 'update'])->name('updated-product');
        Route::post('deleted-product', [ProductController::class, 'destroy'])->name('deleted-product');

        Route::get('/recurrences/{status}', [RecurrenceController::class, 'index'])->name('recurrences');
        Route::get('/notification-recurrence/{id}', [RecurrenceController::class, 'notification'])->name('notification-recurrence');

    });

    Route::get('/logout', [Login::class, 'logout'])->name('logout');
});