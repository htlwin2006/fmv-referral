<?php

use App\Http\Controllers\Api\V1\Campaign\CreateCampaignController;
use App\Http\Controllers\Api\V1\ReferralCode\CreateReferralCodeController;
use App\Http\Controllers\Api\V1\Referral\AccountOpenedController;
use App\Http\Controllers\Api\V1\Referral\AccountRejectedController;
use App\Http\Controllers\Api\V1\Referral\GetReferralInfoController;
use App\Http\Controllers\Api\V1\Referral\LeaderboardController;
use App\Http\Controllers\Api\V1\Referral\TrackReferralController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// API v1 Routes
Route::prefix('v1')->group(function () {
    Route::post('/campaigns', CreateCampaignController::class);
    Route::post('/referral-codes', CreateReferralCodeController::class);
    Route::post('/referrals/track', TrackReferralController::class);
    Route::post('/referrals/account-opened', AccountOpenedController::class);
    Route::post('/referrals/account-rejected', AccountRejectedController::class);
    Route::get('/referrals/leaderboard', LeaderboardController::class);
    Route::get('/referrals/info/prospect_telegram_id/{telegram_id}', GetReferralInfoController::class);
});
