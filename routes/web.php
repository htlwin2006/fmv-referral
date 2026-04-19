<?php

use App\Http\Controllers\ReferralNetworkMapController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Referral Network Map
Route::get('/referral-network-map', [ReferralNetworkMapController::class, 'index'])->name('referral.network.map');
Route::get('/api/referral-network-data', [ReferralNetworkMapController::class, 'getData'])->name('referral.network.data');
