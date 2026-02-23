<?php

use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ProfilePhotoController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\ValidationEventController;
use App\Http\Controllers\AccessRequestController;
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\ServiceMessageController;
use App\Http\Controllers\Api\TrackingFeedController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/tickets/issue', [TicketController::class, 'issue']);
    Route::post('/registration/complete-profile', [RegistrationController::class, 'completeProfile']);
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::post('/profile/photo', [ProfilePhotoController::class, 'store']);
});

Route::post('/validation-events', [ValidationEventController::class, 'store']);
Route::post('/access-requests', [AccessRequestController::class, 'store']);
Route::post('/registration/request-otp', [RegistrationController::class, 'requestOtp'])->middleware('throttle:10,1');
Route::post('/registration/verify-otp', [RegistrationController::class, 'verifyOtp'])->middleware('throttle:10,1');
Route::post('/tracking/feed', [TrackingFeedController::class, 'store'])->middleware('tracking_auth');
Route::get('/tracking/locations', [TrackingFeedController::class, 'latest']);
Route::get('/service-messages/current', [ServiceMessageController::class, 'current']);
