<?php

use App\BillingLog\Controllers\LogViewController;
use App\BillingLog\Controllers\AutomationController;
use App\BillingLog\Controllers\LogWriteController;

Route::middleware(['auth', 'admin'])->group(function (): void {
    Route::get('system-logs', [LogViewController::class, 'getSystemLogs'])->name('system.logs');
    Route::get('logs/{type}', [LogViewController::class, 'getLogs']);
    Route::get('log-category-list', [AutomationController::class, 'getAutomationLog']);
    Route::get('retry/mail-log/{id}', [AutomationController::class, 'dispatchPayload']);
    Route::delete('logs/delete', [LogWriteController::class, 'deleteLogs']);
});
