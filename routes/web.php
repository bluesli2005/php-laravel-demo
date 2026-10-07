<?php

use App\Models\WelcomeMessage;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home', [
        'message' => WelcomeMessage::query()->firstOrFail(),
    ]);
});

Route::get('/welcome', function () {
    return view('welcome', [
        'message' => WelcomeMessage::query()->firstOrFail(),
    ]);
})->name('welcome');
