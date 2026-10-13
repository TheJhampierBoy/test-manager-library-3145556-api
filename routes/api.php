<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;

/*
|--------------------------------------------------------------------------
| API Routes — Manager Library
|--------------------------------------------------------------------------
| Laravel automatically applies the /api prefix.
| Full URL: GET /api/v1/health
*/

Route::prefix('v1')->group(function () {
    // Health check — public, no auth required (T-01)
    Route::get('/health', [HealthController::class, 'index']);
});
