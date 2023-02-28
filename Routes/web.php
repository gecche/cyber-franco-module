<?php

use App\Http\Controllers\PdfRequestController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['validate-api-franco'])->name('pdf-request.')
    ->prefix('pdf-request')
    ->group(function () {
        //  route per la generazione dei pdf da fonti note (lexy)
        //In post:
        //token (di lexy) da controllare anche con la source e la origin della request
        //item
        //email
        //level
        //attributes (eventuali info agiguntive sul pdf da generare)
        //da rimandare anche altre cose di billing e altro che ci diranno
        //Il token è un verification token temporaneo (unica route dove passa)
        //hash è l'hash generato dall'uuid della richiesta
        Route::get('verify/{token}/{hash}', [PdfRequestController::class, 'verify'])
            ->name('verify');
        Route::get('reject/{token}/{hash}', [PdfRequestController::class, 'reject'])
            ->name('reject');
        Route::post('verify/{token}/{hash}', [PdfRequestController::class, 'verifyPost'])
            ->name('verify-post');
        Route::post('reject/{token}/{hash}', [PdfRequestController::class, 'rejectPost'])
            ->name('reject-post');

        Route::get('generate/{source}', [PdfRequestController::class, 'generate'])
            ->where('source', join("|", array_keys(config('pdf-request.sources', []))))
            ->name('generate');


        Route::group(['middleware' => ['pdf-uuid']], function () {

            //hash è l'uuid della richiesta
            //hash è l'hash generato dall'uuid della richiesta
            //da mettere in stand by
            Route::get('resend-verification/{uuid}/{hash}', [PdfRequestController::class, 'resendVerification'])
                ->name('resend-verification');

            Route::get('get-status/{uuid}/{hash}', [PdfRequestController::class, 'getStatus'])
                ->name('get-status');
        });

    });
