<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\QueryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\NotificationController;


Route::prefix('auth')
    ->controller(AuthController::class)
    ->group(function () {
        Route::get('me', 'signIn');
    });


Route::middleware('auth:sanctum')->group(function () {

    Route::get('/auth/me', [AuthController::class, 'me']);

    // EXPENSES
    Route::prefix('expense')
        ->controller(ExpenseController::class)
        ->group(function () {

            Route::get('/', 'index');
            Route::post('/', 'store');
            Route::delete('{expense_uuid}', 'destroy');
        });

    // ITEMS
    Route::prefix('item')
        ->controller(ItemController::class)
        ->group(function () {

            Route::get('/', 'index');
            Route::post('merge', 'merge');
            Route::get('search', 'search');
        });

    // QUERY
    Route::prefix('query')
        ->controller(QueryController::class)
        ->group(function () {
            Route::get('summary', 'summary');
        });

    // NOTIFICATION
    Route::prefix('notification')
        ->controller(NotificationController::class)
        ->group(function () {
            Route::get('/', 'index');
            Route::post('/', 'store');
        });
});
