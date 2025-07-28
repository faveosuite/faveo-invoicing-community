<?php

use App\Http\Controllers\Api;
use Illuminate\Support\Facades\Route;

Route::post('api/chunk-upload', [Api\ThirdPartyApiController::class, 'chunkUploadFile']);

Route::post('api/upload/save', [Api\ThirdPartyApiController::class, 'saveProduct']);
