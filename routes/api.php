<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AccountController;
use App\Http\Controllers\API\DocumentsController;
use App\Http\Controllers\API\RequestDocumentController;
use App\Http\Controllers\API\CertificateLogsController;
use App\Http\Controllers\API\RejectedAccountController;
use App\Http\Controllers\API\AnnouncementController;
use App\Http\Controllers\API\BlotterController;
use App\Http\Controllers\API\FeedbackController;
use App\Http\Controllers\API\ActivityLogsController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\SystemStatsController;

// Auth Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Public tracking route - no authentication required
Route::get('/track-document/{transaction_id}', [RequestDocumentController::class, 'trackByTransactionId']);
Route::get('/announcements', [AnnouncementController::class, 'index']);
Route::post('/announcements/show', [AnnouncementController::class, 'show']);
Route::get('/officials/get', [\App\Http\Controllers\API\OfficialsController::class, 'index']);
Route::get('/officials/get/{id}', [\App\Http\Controllers\API\OfficialsController::class, 'show']);
Route::get('/configs', [\App\Http\Controllers\API\ConfigController::class, 'index']);

// Account Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/admin/add-account', [AuthController::class, 'create_account']);
    Route::post('/admin/update/user-type/{id}', [AccountController::class, 'updateType']);
    Route::put('/accounts/{id}/update-information', [AccountController::class, 'updateInformation']);
    Route::put('/accounts/{id}/update-status', [AccountController::class, 'updateStatus']);
    Route::put('/accounts/{id}/update-password', [AccountController::class, 'updatePassword']);
    Route::post('/accounts/{id}/update-profile-picture', [AccountController::class, 'updateProfilePicture']);
    Route::put('/accounts/{id}/accept', [AccountController::class, 'acceptAccount']);
    Route::delete('/accounts/{id}/reject', [AccountController::class, 'rejectAccount']);
    Route::get('/accounts/all', [AccountController::class, 'index']);
    Route::get('/user', [AccountController::class, 'current']);
    Route::get('/user/{id}', [AccountController::class, 'show']);

    // Document Routes
    Route::get('/documents', [DocumentsController::class, 'index']);
    Route::get('/documents/{id}', [DocumentsController::class, 'show']);
    Route::post('/documents/create', [DocumentsController::class, 'store']);
    Route::put('/documents/update/{id}', [DocumentsController::class, 'update']);
    Route::delete('/documents/destroy/{id}', [DocumentsController::class, 'destroy']);

    // Document Template Routes
    Route::post('/documents/{id}/template/upload', [DocumentsController::class, 'uploadTemplate']);
    Route::get('/documents/{id}/template/get', [DocumentsController::class, 'getTemplate']);
    Route::delete('/documents/{id}/template/delete', [DocumentsController::class, 'deleteTemplate']);
    Route::get('/documents/{id}/template/extract-placeholders', [DocumentsController::class, 'extractPlaceholders']);

    // Request Document Routes
    Route::get('/request-documents', [RequestDocumentController::class, 'index']);
    Route::get('/request-documents/{id}', [RequestDocumentController::class, 'show']);
    Route::post('/request-documents/create', [RequestDocumentController::class, 'store']);
    Route::put('/request-documents/status/{id}', [RequestDocumentController::class, 'changeStatus']);
    Route::post('/request-documents/{id}/generate-filled-document', [RequestDocumentController::class, 'generateFilledDocument']);

    // Certificate Logs Routes
    Route::get('/certificate-logs', [CertificateLogsController::class, 'index']);
    Route::get('/certificate-logs/{id}', [CertificateLogsController::class, 'show']);
    Route::post('/certificate-logs/create', [CertificateLogsController::class, 'create']);

    // Officials Routes
    Route::post('/officials/create', [\App\Http\Controllers\API\OfficialsController::class, 'store']);
    Route::post('/officials/update/{id}', [\App\Http\Controllers\API\OfficialsController::class, 'update']);
    Route::post('/officials/update/status/{id}', [\App\Http\Controllers\API\OfficialsController::class, 'updateStatus']);

    // Rejected Accounts Routes
    Route::get('/rejected-accounts', [RejectedAccountController::class, 'index']);

    // Announcement Routes
    Route::post('/announcements/create', [AnnouncementController::class, 'store']);
    Route::put('/announcements/update', [AnnouncementController::class, 'update']);
    Route::delete('/announcements/delete', [AnnouncementController::class, 'destroy']);

    // Blotter Routes
    Route::get('/blotters', [BlotterController::class, 'index']);
    Route::post('/blotters/create', [BlotterController::class, 'store']);
    Route::post('/blotters/show/{case_number}', [BlotterController::class, 'show']);
    Route::get('/blotters/history/{case_number}', [BlotterController::class, 'getHistory']);
    Route::put('/blotters/update/{case_number}', [BlotterController::class, 'update']);
    Route::put('/blotters/update-status/{case_number}', [BlotterController::class, 'updateStatus']);
    Route::delete('/blotters/delete/{case_number}', [BlotterController::class, 'destroy']);

    // Feedback routes
    Route::get('/feedback', [FeedbackController::class, 'index']);
    Route::post('/feedback/create', [FeedbackController::class, 'store']);
    Route::post('/feedback/update', [FeedbackController::class, 'update']);
    Route::post('/feedback/delete', [FeedbackController::class, 'destroy']);

    // Activity Logs Routes
    Route::get('/activitylogs', [ActivityLogsController::class, 'index']);
    Route::get('/activitylogs/{id}', [ActivityLogsController::class, 'show']);
    Route::delete('/activitylogs/{id}', [ActivityLogsController::class, 'destroy']);

    // Config Routes
    Route::post('/configs/create', [\App\Http\Controllers\API\ConfigController::class, 'store']);
    Route::put('/configs/update', [\App\Http\Controllers\API\ConfigController::class, 'update']);
    Route::delete('/configs/delete', [\App\Http\Controllers\API\ConfigController::class, 'destroy']);

    // Dashboard Routes
    Route::prefix('dashboard')->group(function () {
        // Overview
        Route::get('/overview', [DashboardController::class, 'overview']);
        Route::get('/all-statistics', [DashboardController::class, 'allStatistics']);

        // Module-specific statistics
        Route::get('/document-requests', [DashboardController::class, 'documentRequests']);
        Route::get('/users', [DashboardController::class, 'userStatistics']);
        Route::get('/blotters', [DashboardController::class, 'blotterStatistics']);
        Route::get('/feedbacks', [DashboardController::class, 'feedbackStatistics']);
        Route::get('/certificates', [DashboardController::class, 'certificateStatistics']);
        Route::get('/activity_logs', [DashboardController::class, 'activityStatistics']);
        Route::get('/announcements', [DashboardController::class, 'announcementStatistics']);

        // Document analytics
        Route::get('/document-types', [DashboardController::class, 'documentTypeStatistics']);
        Route::get('/top-documents', [DashboardController::class, 'topDocuments']);

    // System combined stats
    Route::get('/system-stats', [SystemStatsController::class, 'index']);

        // Performance metrics
        Route::get('/performance', [DashboardController::class, 'performanceMetrics']);
        Route::get('/monthly-comparison', [DashboardController::class, 'monthlyComparison']);

        // Reports
        Route::post('/generate-report', [DashboardController::class, 'generateReport']);
    });
});

// Temporary bug fix sa Route [Login]
// Nag re-redirect sa login blade instead na json kaya eto nalang muna
Route::get('redirect', function () {
    return response()->json([
        'message'=>"unauthorized access"
    ], 401);
})->name('login');
