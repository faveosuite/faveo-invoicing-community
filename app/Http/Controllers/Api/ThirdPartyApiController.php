<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use App\Traits\Upload\ChunkUpload;
use Exception;
use Illuminate\Http\Request;

class ThirdPartyApiController extends Controller
{
    use ChunkUpload;

    private \App\Model\Product\ProductUpload $product_upload; // @phpstan-ignore property.onlyWritten

    private \App\Model\Product\Product $product; // @phpstan-ignore property.onlyWritten

    public function __construct()
    {
        $this->middleware('validateThirdParty');

        $product_upload = new ProductUpload();
        $this->product_upload = $product_upload;

        $product = new Product();
        $this->product = $product;
    }

    public function chunkUploadFile(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            //Put check in this api for valid product id before uploading
            $result = $this->uploadFile($request);

            return $result;
        } catch (Exception $exception) {
            $error = $exception->getMessage();

            return response()->json(compact('error'));
        }
    }
}
