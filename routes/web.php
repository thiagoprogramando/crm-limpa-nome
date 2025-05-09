<?php

use App\Http\Controllers\Access\ForgoutController;
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
use App\Http\Controllers\Sale\InvoiceController;
use App\Http\Controllers\Sale\SaleController;
use App\Http\Controllers\Trash\RecoverController;
use App\Http\Controllers\Trash\TrashController;
use App\Http\Controllers\Upload\UploadController;
use App\Http\Controllers\User\ListController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Wallet\WalletController;
use App\Http\Controllers\WhiteLabel\ContractController as WhiteLabelContractController;
use Illuminate\Support\Facades\Route;

Route::get('/', [Login::class, 'index'])->name('login');
Route::post('/logon', [Login::class, 'logon'])->name('logon');

Route::get('/login-cliente', [LoginController::class, 'login'])->name('login.cliente');
Route::post('/logon-cliente', [LoginController::class, 'logon'])->name('logon.cliente');

Route::get('/registrer/{id?}/{fixed_cost?}', [Registrer::class, 'index'])->name('registrer');
Route::post('registrer-user', [Registrer::class, 'registrerUser'])->name('registrer-user');

Route::get('/forgout/{token?}', [ForgoutController::class, 'forgout'])->name('forgout');
Route::post('forgout-password', [ForgoutController::class, 'forgoutPassword'])->name('forgout-password');
Route::post('recovery-password/{token}', [ForgoutController::class, 'recoveryPassword'])->name('recovery-password');

Route::get('/view-contract/{id}', [ContractController::class, 'view'])->name('view-contract');

Route::middleware(['auth'])->group(function () {

    Route::middleware(['checkMonthly'])->group(function () {

        Route::get('/app', [AppController::class, 'app'])->name('app');

        // ========================== Users =============================
            //Sales
            Route::get('/list-sales', [SaleController::class, 'listSale'])->name('list-sales');
            Route::get('/view-sale/{uuid}', [SaleController::class, 'viewSale'])->name('view-sale');
            Route::get('/create-sale/{product}/{user?}', [SaleController::class, 'createSale'])->name('create-sale');
            Route::post('created-client-sale', [SaleController::class, 'createdClientSale'])->name('created-client-sale');
            Route::post('created-payment-sale', [SaleController::class, 'createdPaymentSale'])->name('created-payment-sale');
            Route::post('updated-sale', [SaleController::class, 'updatedSale'])->name('updated-sale');
            Route::post('deleted-sale', [SaleController::class, 'deletedSale'])->name('deleted-sale');
            Route::get('reprotocol-sale/{id}', [SaleController::class, 'reprotocolSale'])->name('reprotocol-sale');

                //Coupons
                Route::get('/list-coupons', [CouponController::class, 'coupons'])->name('list-coupons');
                Route::post('created-coupon', [CouponController::class, 'created'])->name('created-coupon');
                Route::post('deleted-coupon', [CouponController::class, 'deleted'])->name('deleted-coupon');
                Route::post('add-coupon', [CouponController::class, 'addCoupon'])->name('add-coupon');

            //Invoices
            Route::get('/view-invoice/{id}', [InvoiceController::class, 'index'])->name('view-invoice');
            Route::post('created-invoice', [InvoiceController::class, 'createdInvoice'])->name('created-invoice');
            Route::post('updated-invoice', [InvoiceController::class, 'updatedInvoice'])->name('updated-invoice');
            Route::post('/deleted-invoice', [InvoiceController::class, 'deletedInvoice'])->name('deleted-invoice');

            //Contracts
            Route::get('/send-contract/{id}', [ContractController::class, 'createContract'])->name('send-contract');
            Route::get('/send-default-whatsapp/{id}', [DefaultController::class, 'sendWhatsapp'])->name('send-default-whatsapp');
            
            //User
                Route::get('/profile', [UserController::class, 'profile'])->name('profile');
                Route::post('update-user', [UserController::class, 'updateProfile'])->name('update-user');
                Route::post('delete-user', [UserController::class, 'deleteUser'])->name('delete-user');
                    //Network
                    Route::get('/list-network', [UserController::class, 'listNetwork'])->name('list-network');
                    Route::get('/list-client', [UserController::class, 'listClient'])->name('list-client');
                    Route::get('/list-active/{status}', [UserController::class, 'listActive'])->name('list-active');

            // Finance
            Route::get('/wallet', [WalletController::class, 'wallet'])->name('wallet');
            Route::post('withdraw-send', [WalletController::class, 'withdrawSend'])->name('withdraw-send');
                //Operations
                Route::get('/payments', [Payment::class, 'payments'])->name('payments');
                Route::get('/receivable', [Payment::class, 'receivable'])->name('receivable');

            //Intagrations
            Route::get('/Integrate-wallet', [WalletController::class, 'IntegrateWallet'])->name('Integrate-wallet');

            //Lixeira
            Route::get('/trash-sales', [TrashController::class, 'trashSales'])->name('trash-sales');
            Route::get('/trash-users', [TrashController::class, 'trashUsers'])->name('trash-users');
            Route::post('sale-recover', [RecoverController::class, 'recoverSale'])->name('sale-recover');
            Route::post('user-recover', [RecoverController::class, 'recoverUser'])->name('user-recover');

            //Gateway
            Route::get('/createMonthly/{id}', [AssasController::class, 'createMonthly'])->name('createMonthly');
            Route::get('/payMonthly/{id}', [AssasController::class, 'payMonthly'])->name('payMonthly');
            Route::get('/request-invoices/{id}', [AssasController::class, 'requestInvoice'])->name('request-invoices');

            Route::middleware(['checkAdmin'])->group(function () {
                //Users
                Route::get('/list-user/{type}', [UserController::class, 'listuser'])->name('list-user');

                //Products
                Route::get('/list-products', [ProductController::class, 'index'])->name('list-products');
                Route::get('/create-product', [ProductController::class, 'productView'])->name('create-product');
                Route::post('created-product', [ProductController::class, 'productCreate'])->name('created-product');
                Route::get('/update-product/{id}', [ProductController::class, 'productDetails'])->name('update-product');
                Route::post('updated-product', [ProductController::class, 'productUpdate'])->name('updated-product');
                Route::post('deleted-product', [ProductController::class, 'productDelete'])->name('deleted-product');

                //Lists
                Route::get('/list-lists', [ListController::class, 'listLists'])->name('list-lists');
                Route::post('created-list', [ListController::class, 'createdList'])->name('created-list');
                Route::get('/view-list/{id}', [ListController::class, 'viewList'])->name('view-list');
                Route::post('updated-list', [ListController::class, 'updatedList'])->name('updated-list');
                Route::post('deleted-list', [ListController::class, 'deletedList'])->name('deleted-list');
                Route::get('/list-excel/{id}', [ListController::class, 'excelList'])->name('list-excel');
            });
    });

    Route::get('/logout', [Login::class, 'logout'])->name('logout');

    // ========================== Clientes =============================
    Route::get('/app-client', [ClientAppController::class, 'app'])->name('app.client');
    Route::get('/invoice-client/{sale?}', [ClientAppController::class, 'invoice'])->name('invoice.client');
    Route::get('/logout-client', [ClientAppController::class, 'logout'])->name('logout.client');
});