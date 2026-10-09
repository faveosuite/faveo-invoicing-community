<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ThirdPartyApiController;
use Illuminate\Support\Facades\Route;

/*
 * Release automation for external CI (HMAC-signed via `validateThirdParty`,
 * no session). Two steps: `chunk-upload` pushes the build zip in chunks and
 * returns its stored path; `upload/save` then registers that one build as a
 * release for one or many products, each with its own version — the
 * third-party twin of the admin screen's `product/upload-build/apply`.
 */

Route::post('api/chunk-upload', [ThirdPartyApiController::class, 'chunkUploadFile']);

Route::post('api/upload/save', [ThirdPartyApiController::class, 'applyBuildToProducts']);
