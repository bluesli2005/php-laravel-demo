<?php

use App\Http\Controllers\Api\V1\WelcomeMessageController;
use App\Http\Middleware\EnsureLocalApiWrites;
use App\Models\WelcomeMessage;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->middleware(EnsureLocalApiWrites::class)->group(function (): void {
    Route::apiResource('welcome-messages', WelcomeMessageController::class)
        ->parameters(['welcome-messages' => 'page'])
        ->scoped(['page' => 'page'])
        ->where(['page' => implode('|', WelcomeMessage::PAGES)]);
});
