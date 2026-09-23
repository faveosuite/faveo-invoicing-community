<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\Product\AppliesBuildToProducts;
use App\Traits\Upload\ChunkUpload;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ThirdPartyApiController extends Controller
{
    use AppliesBuildToProducts;
    use ChunkUpload;

    public function __construct()
    {
        $this->middleware('validateThirdParty');
    }

    public function chunkUploadFile(Request $request): JsonResponse
    {
        try {
            // Put check in this api for valid product id before uploading
            $result = $this->uploadFile($request);

            return $result;
        } catch (Exception $exception) {
            $error = $exception->getMessage();

            return response()->json(compact('error'));
        }
    }
}
