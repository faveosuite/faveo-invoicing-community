<?php

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('system-logs', [\App\BillingLog\Controllers\LogViewController::class, 'getSystemLogs'])->name('system.logs');
    Route::post('logs/{type}', [\App\BillingLog\Controllers\LogViewController::class, 'getLogs']);
    Route::get('log-category-list', [\App\BillingLog\Controllers\AutomationController::class, 'getAutomationLog']);
    Route::get('retry/mail-log/{id}', [\App\BillingLog\Controllers\AutomationController::class, 'dispatchPayload']);
    Route::delete('logs/delete', [\App\BillingLog\Controllers\LogWriteController::class, 'deleteLogs']);
});
