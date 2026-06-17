<?php

use App\Http\Controllers\Api\ThirdPartyApiController;
use Illuminate\Support\Facades\Route;

Route::post('api/chunk-upload', [ThirdPartyApiController::class, 'chunkUploadFile']);
