<?php

Route::middleware(['web'])->group(function () {
    Route::get('system-logs', [\App\BillingLog\Controllers\LogViewController::class, 'getSystemLogs'])->name('system.logs');
    Route::post('logs/{type}', [\App\BillingLog\Controllers\LogViewController::class, 'getLogs']);
    Route::get('log-category-list', [\App\BillingLog\Controllers\LogViewController::class, 'getLogCategoryList']);
});
