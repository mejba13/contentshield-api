<?php

use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\DmcaController;
use App\Http\Controllers\Api\LicenseController;
use App\Http\Controllers\Api\MonitoringController;
use App\Http\Controllers\Api\ReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| ContentShield AI SaaS API Routes
| Base URL: /api/v1
|
*/

Route::prefix('v1')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Public Routes (No Authentication Required)
    |--------------------------------------------------------------------------
    */

    // License validation and activation (public - no auth required)
    Route::post('/license/validate', [LicenseController::class, 'validate']);
    Route::post('/license/deactivate', [LicenseController::class, 'deactivate']);

    /*
    |--------------------------------------------------------------------------
    | Protected Routes (License Authentication Required)
    |--------------------------------------------------------------------------
    */

    Route::middleware(['license'])->group(function () {
        // License management
        Route::prefix('license')->group(function () {
            Route::get('/status', [LicenseController::class, 'status']);
            Route::post('/refresh', [LicenseController::class, 'refresh']);
        });

        // Content management
        Route::prefix('content')->group(function () {
            Route::post('/register', [ContentController::class, 'register']);
            Route::post('/bulk-register', [ContentController::class, 'bulkRegister']);
            Route::get('/list', [ContentController::class, 'list']);
            Route::get('/{id}', [ContentController::class, 'show']);
            Route::put('/{id}', [ContentController::class, 'update']);
            Route::delete('/{id}', [ContentController::class, 'destroy']);
        });

        // Monitoring
        Route::prefix('monitoring')->group(function () {
            Route::get('/status', [MonitoringController::class, 'status']);
            Route::get('/results', [MonitoringController::class, 'results']);
            Route::get('/results/{id}', [MonitoringController::class, 'showResult']);
            Route::put('/results/{id}', [MonitoringController::class, 'updateResult']);
            Route::post('/scan', [MonitoringController::class, 'scan']);
            Route::get('/logs', [MonitoringController::class, 'logs']);
            Route::get('/logs/{id}', [MonitoringController::class, 'showLog']);
        });

        // DMCA management
        Route::prefix('dmca')->group(function () {
            Route::post('/generate', [DmcaController::class, 'generate']);
            Route::post('/send', [DmcaController::class, 'send']);
            Route::get('/templates', [DmcaController::class, 'templates']);
            Route::get('/history', [DmcaController::class, 'history']);
            Route::get('/stats', [DmcaController::class, 'stats']);
            Route::get('/{id}', [DmcaController::class, 'show']);
            Route::put('/{id}', [DmcaController::class, 'update']);
            Route::delete('/{id}', [DmcaController::class, 'destroy']);
        });

        // Reports
        Route::prefix('reports')->group(function () {
            Route::get('/dashboard', [ReportController::class, 'dashboard']);
            Route::get('/export', [ReportController::class, 'export']);
            Route::get('/trends', [ReportController::class, 'trends']);
        });
    });
});

/*
|--------------------------------------------------------------------------
| API Health Check
|--------------------------------------------------------------------------
*/

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'version' => config('contentshield.api_version', '1.0.0'),
    ]);
});
