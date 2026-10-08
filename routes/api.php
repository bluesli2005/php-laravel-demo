<?php

use App\Http\Controllers\Api\V1\LifeInsurancePolicyController;
use App\Http\Middleware\EnsureLocalApiWrites;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->middleware(EnsureLocalApiWrites::class)->group(function (): void {
    Route::apiResource('life-insurance-policies', LifeInsurancePolicyController::class);
});
