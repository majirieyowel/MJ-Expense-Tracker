<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response('Expense Tracker by majirieyowel technologies v1.0.0 💯', 200)
        ->header('Content-Type', 'text/plain');
});
