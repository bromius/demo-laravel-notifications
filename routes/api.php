<?php

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NotificationReportController;
use App\Http\Controllers\UserNotificationController;
use Illuminate\Support\Facades\Route;

Route::post('notifications', [NotificationController::class, 'store']);
Route::get('notifications/{notification}', [NotificationController::class, 'show']);

Route::get('users/{user}/notifications', [UserNotificationController::class, 'index']);
Route::post('users/{user}/notification-reports', [NotificationReportController::class, 'store']);

Route::get('notification-reports/{report}', [NotificationReportController::class, 'show']);
Route::get('notification-reports/{report}/download', [NotificationReportController::class, 'download']);
