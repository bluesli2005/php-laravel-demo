<?php

use App\Models\WelcomeMessage;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome', [
        'message' => WelcomeMessage::query()->firstOrFail(),
    ]);
});
