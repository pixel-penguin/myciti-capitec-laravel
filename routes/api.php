<?php

use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\ValidationEventController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::post('/tickets/issue', [TicketController::class, 'issue']);
});

Route::post('/validation-events', [ValidationEventController::class, 'store']);
