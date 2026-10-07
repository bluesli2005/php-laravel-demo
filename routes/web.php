<?php

use App\Models\WelcomeMessage;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home', [
        'welcomeMessage' => WelcomeMessage::query()->where('page', 'home')->firstOrFail()->content,
    ]);
})->name('home');

Route::get('/about', function () {
    return Inertia::render('About', [
        'welcomeMessage' => WelcomeMessage::query()->where('page', 'about')->firstOrFail()->content,
    ]);
})->name('about');

Route::get('/services', fn () => Inertia::render('Services'))->name('services');
Route::get('/contact', fn () => Inertia::render('Contact'))->name('contact');
