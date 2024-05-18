<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WhatsappController;

Route::get('meta-webhook', [WhatsappController::class, 'verifyMetaWebhook']);
Route::post('meta-webhook', [WhatsappController::class, 'processWebhook']);

Route::get('/', function () {
    return response('Expense Tracker by majirieyowel technologies v1.0.0 💯', 200)
        ->header('Content-Type', 'text/plain');
});

require __DIR__.'/auth.php';
