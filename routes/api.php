<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ItemController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')
    ->controller(AuthController::class)
    ->group(function () {
        Route::post('sign-up', 'signUp');
        Route::post('sign-in', 'signIn');
    });


Route::middleware('auth:sanctum')->group(function () {

    // EXPENSES
    Route::prefix('expense')
        ->controller(ExpenseController::class)
        ->group(function () {

            Route::get('/', 'index');
            Route::post('/', 'createExpense');
        });

    // ITEMS
    Route::prefix('item')
        ->controller(ItemController::class)
        ->group(function () {

            Route::get('/', 'index');
            Route::post('merge', 'merge');
            Route::get('search', 'search');
        });
});
