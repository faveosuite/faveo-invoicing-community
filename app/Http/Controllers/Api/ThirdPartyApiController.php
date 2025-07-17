<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\Common\StatusSetting;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use App\Traits\Upload\ChunkUpload;
use Exception;
use Illuminate\Http\Request;

class ThirdPartyApiController extends Controller
{
    use ChunkUpload;

    private $product_upload;

    private $product;

    public function __construct()
    {
        $this->middleware('validateThirdParty');

        $product_upload = new ProductUpload();
        $this->product_upload = $product_upload;

        $product = new Product();
        $this->product = $product;
    }

    public function chunkUploadFile(Request $request)
    {
        try {
            //Put check in this api for valid product id before uploading
            $result = $this->uploadFile($request);

            return $result;
        } catch (Exception $ex) {
            $error = $ex->getMessage();

            return response()->json(compact('error'));
        }
    }

    public function saveProduct(Request $request)
    {
        $this->validate(
            $request,
            [
                'productname' => 'required',
                'producttitle' => 'required',
                'version' => 'required',
                'filename' => 'required',
                'dependencies' => 'required',
            ],
            ['filename.required' => 'Please Uplaod A file',
            ]
        );
        try {
            $product_id = Product::where('id', $request->input('product_id'))->select('id')->first();

            if ($product_id) {
                $this->product_upload->product_id = $product_id->id;
                $this->product_upload->title = $request->input('producttitle');
                $this->product_upload->description = $request->input('description');
                $this->product_upload->version = $request->input('version');
                $this->product_upload->file = $request->input('filename');
                $this->product_upload->is_private = $request->input('is_private');
                $this->product_upload->is_restricted = $request->input('is_restricted');
                $this->product_upload->release_type = $request->input('release_type');
                $this->product_upload->dependencies = json_encode($request->input('dependencies'));
                $this->product_upload->save();
                $this->product->where('id', $product_id->id)->update(['version' => $request->input('version')]);
                $autoUpdateStatus = StatusSetting::pluck('license_status')->first();
                if ($autoUpdateStatus == 1) { //If License Setting Status is on,Add Product to the License Manager
                    $updateClassObj = new \App\Http\Controllers\AutoUpdate\AutoUpdateController();
                    $addProductToAutoUpdate = $updateClassObj->addNewVersion($product_id->id, $request->input('version'), $request->input('filename'), '1');
                }
                $response = ['success' => 'true', 'message' => __('message.product_uploaded_successfully')];
            } else {
                $response = ['success' => 'fails', 'message' => __('message.product_not_found')];
            }

            return $response;
        } catch (\Exception $e) {
            \Logger::exception($e);
            $message = [$e->getMessage()];
            $response = ['success' => 'false', 'message' => $message];

            return response()->json(compact('response'), 500);
        }
    }
}
