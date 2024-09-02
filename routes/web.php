<?php

use App\Http\Controllers\Access\Forgout;
use App\Http\Controllers\Access\Login;
use App\Http\Controllers\Access\Registrer;
use App\Http\Controllers\AppController;
use App\Http\Controllers\Assas\AssasController;
use App\Http\Controllers\Payment\Payment;
use App\Http\Controllers\Photoshop\PhotoshopController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Sale\DefaultController;
use App\Http\Controllers\Sale\SaleController;
use App\Http\Controllers\Upload\UploadController;
use App\Http\Controllers\User\ListController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\WalletController;

use App\Http\Middleware\Monthly;
use Illuminate\Support\Facades\Route;

Route::get('/', [Login::class, 'index'])->name('login');
Route::post('/logon', [Login::class, 'logon'])->name('logon');

Route::get('/registrer/{id?}/{type?}', [Registrer::class, 'index'])->name('registrer');
Route::post('registrer-user', [Registrer::class, 'registrerUser'])->name('registrer-user');

Route::get('/forgout/{code?}', [Forgout::class, 'forgout'])->name('forgout');
Route::post('send-code-password', [Forgout::class, 'sendCodePassword'])->name('send-code-password');
Route::post('update-password', [Forgout::class, 'updatePassword'])->name('update-password');

Route::get('/sale-link/{product}/{user}/{value}', [SaleController::class, 'saleLink'])->name('sale-link');
Route::post('create-sale-external', [SaleController::class, 'createSale'])->name('create-sale-external');

Route::middleware(['auth'])->group(function () {

    Route::get('/app/{list?}', [AppController::class, 'app'])->name('app');
    Route::get('/apresentation', [UserController::class, 'apresentation'])->name('apresentation');
    Route::post('create-apresentation', [UserController::class, 'createApresentation'])->name('create-apresentation');
    Route::post('delete-apresentation', [UserController::class, 'deleteApresentation'])->name('delete-apresentation');

    Route::middleware(['monthly'])->group(function () {

        Route::middleware(['verify'])->group(function () {
        
            //Sale
            Route::get('/createsale/{id}', [SaleController::class, 'create'])->name('createsale');
            Route::post('create-sale', [SaleController::class, 'createSale'])->name('create-sale');
    
            Route::get('/manager-sale', [SaleController::class, 'manager'])->name('manager-sale');
            Route::get('/update-sale/{id}', [SaleController::class, 'viewSale'])->name('update-sale');
            Route::get('/invoice-default', [SaleController::class, 'default'])->name('invoice-default');
            Route::post('delete-sale', [SaleController::class, 'deleteSale'])->name('delete-sale');
    
            Route::get('/send-default-whatsapp/{id}', [DefaultController::class, 'sendWhatsapp'])->name('send-default-whatsapp');
            Route::get('/send-contract/{id}', [SaleController::class, 'sendContractWhatsapp'])->name('send-contract');

            //Upload
            Route::get('/createupload/{id}', [UploadController::class, 'create'])->name('createupload');
            Route::post('create-upload', [UploadController::class, 'createSale'])->name('create-upload');

            //Wallet
            Route::get('/wallet', [WalletController::class, 'wallet'])->name('wallet');
            Route::get('/withdraw', [WalletController::class, 'withdraw'])->name('withdraw');
            Route::post('withdraw-send', [WalletController::class, 'withdrawSend'])->name('withdraw-send');
    
            //Payments
            Route::get('/receivable', [Payment::class, 'receivable'])->name('receivable');

            //Gatway Assas
            Route::post('create-deposit', [AssasController::class, 'createDeposit'])->name('create-deposit');

            //Photoshop
            Route::get('/photoshop', [PhotoshopController::class, 'list'])->name('photoshop');
            Route::post('/create-photoshop', [PhotoshopController::class, 'createPhotoshop'])->name('create-photoshop');
            Route::post('/delete-photoshop', [PhotoshopController::class, 'deletePhotoshop'])->name('delete-photoshop');
            Route::post('/create-midia', [PhotoshopController::class, 'createMidia'])->name('create-midia');
        });

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
    Route::post('update-payment', [ProductController::class, 'updatePayment'])->name('update-payment');
    Route::post('create-item', [ProductController::class, 'createItem'])->name('create-item');
    Route::post('delete-item', [ProductController::class, 'deleteItem'])->name('delete-item');

    //User
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::post('update-user', [UserController::class, 'updateProfile'])->name('update-user');
    Route::post('delete-user', [UserController::class, 'deleteUser'])->name('delete-user');
    Route::get('/search', [UserController::class, 'search'])->name('search');
    Route::get('/listuser/{type}', [UserController::class, 'listuser'])->name('listuser');
    Route::get('/list-rede', [UserController::class, 'listRede'])->name('list-rede');
    Route::get('view-notification/{id}', [UserController::class, 'viewNotification'])->name('view-notification');
        //Archive
        Route::get('/my-archive', [UserController::class, 'myArchive'])->name('my-archive');
        Route::post('/create-archive', [UserController::class, 'createArchive'])->name('create-archive');
        Route::post('/delete-archive', [UserController::class, 'deleteArchive'])->name('delete-archive');

        //Active
        Route::get('/list-active/{status}', [UserController::class, 'listActive'])->name('list-active');
        Route::get('/send-active/{id}', [UserController::class, 'sendActive'])->name('send-active');

    //List
    Route::get('/lists', [ListController::class, 'list'])->name('lists');
    Route::get('/createlist', [ListController::class, 'create'])->name('createlist');
    Route::get('/excel-list/{id}', [ListController::class, 'excelList'])->name('excel-list');
    Route::post('create-list', [ListController::class, 'createList'])->name('create-list');
    Route::get('/updatelist/{id}', [ListController::class, 'update'])->name('updatelist');
    Route::post('update-list', [ListController::class, 'updateList'])->name('update-list');
    Route::post('delete-list', [ListController::class, 'delete'])->name('delete-list');

    //Payments Assas
    Route::get('/createMonthly/{id}', [AssasController::class, 'createMonthly'])->name('createMonthly');
    Route::get('/payments', [Payment::class, 'payments'])->name('payments');

    //Client
    Route::get('/my-shop', [SaleController::class, 'myShop'])->name('my-shop');
    Route::get('/my-product/{id}', [SaleController::class, 'myProduct'])->name('my-product');
    
    Route::get('/logout', [Login::class, 'logout'])->name('logout');
});
