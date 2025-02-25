<?php

namespace App\Http\Controllers\Product;

use App\Facades\Attach;
use App\Http\Controllers\License\LicenseController;
use App\Http\Controllers\License\LicensePermissionsController;
use App\Model\Payment\Plan;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use App\ThirdPartyApp;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BaseProductController extends ExtendedBaseProductController
{
    public function getMyUrl()
    {
        $server = new Request();
        $url = $_SERVER['REQUEST_URI'];
        $server = parse_url($url);
        $server['path'] = dirname($server['path']);
        $server = parse_url($server['path']);
        $server['path'] = dirname($server['path']);
        $server = 'http://'.$_SERVER['HTTP_HOST'].$server['path'];

        return $server;
    }

    /*
    * Get Product Qty if Product can be modified
     */
    public function getProductQtyCheck($productid, $planid)
    {
        try {
            $check = self::checkMultiProduct($productid);
            if ($check == true) {
                $value = Product::find($productid)->planRelation->find($planid)->planPrice->first()->product_quantity;
                $value = $value == null ? 1 : $value;

                return "<div>
	                        <label class='required'>"./* @scrutinizer ignore-type */
                            \Lang::get('message.quantity')."</label>
	                        <input type='text' name='quantity' class='form-control' id='quantity' value='$value'>
	                </div>";
            }
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    /*
    * Check whether Product is allowed for Increasing the Quantity fromAdmin Panel
    * @param int $productid
    *
    * @return boolean
     */
    public function checkMultiProduct(int $productid)
    {
        $product = new Product();
        $product = $product->find($productid);
        if ($product) {
            if ($product->can_modify_quantity == 1) {
                return true;
            }
        }

        return false;
    }

    public function getAgentQtyCheck($productid, $planid)
    {
        try {
            $check = self::checkMultiAgent($productid);
            if ($check == true) {
                $value = Product::find($productid)->planRelation->find($planid)->planPrice->first()->no_of_agents;
                $value = $value == null ? 0 : $value;

                return "<div>
                            <label class='required'>"./* @scrutinizer ignore-type */
                            \Lang::get('message.agent')."</label>
                            <input type='text' name='agents' class='form-control' id='agents' value='$value'>
                    </div>";
            }
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    /*
    * Check whether No of the GAents can be modified or not fromAdmin Panel
    * @param int $productid
    *
    * @return boolean
     */
    public function checkMultiAgent(int $productid)
    {
        $product = new Product();
        $product = $product->find($productid);
        if ($product) {
            if ($product->can_modify_agent == 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the Subscription and Price Based on the Product Selected while generating Invoice (Admin Panel).
     *
     * @param  int  $productid
     * @param  Request  $request
     * @return [type]
     */
    public function getSubscriptionCheck(int $productid, Request $request)
    {
        try {
            $controller = new \App\Http\Controllers\Front\CartController();
            $field = '';
            $price = '';

            $plan = new Plan();
            $plans = $plan->where('product', $productid)->pluck('name', 'id')->toArray();
            if (count($plans) > 0) {//If Plan Exist For A product, Display Dropdown for Plans
                $field = "<div>
                        <label for='plan' class='required'>"./* @scrutinizer ignore-type */
                        \Lang::get('message.subscription').'</label>
                       '.\Form::select(
                            'plan',
                            ['' => 'Select', 'Plans' => $plans],
                            null,
                            ['class' => 'form-control required', 'id' => 'plan', 'onchange' => 'getPrice(this.value)']
                        ).'
                </div>';
            } else {//If No Plan Exist For A Product
                $userid = $request->input('user');
                $price = $controller->cost($productid, $userid);
            }
            $result = ['price' => $price, 'field' => $field];

            return response()->json($result);
        } catch (\Exception $ex) {
            app('log')->error($ex->getMessage());
            $result = [$ex->getMessage()];

            return response()->json($result);
        }
    }

    public function userDownload($uploadid, $userid, $invoice_number, $version_id = '')
    {
        try {
            if (\Auth::user()->role != 'admin') {
                if (\Auth::user()->id != $userid) {
                    throw new \Exception('This user has no permission for this action');
                }
            }
            $user = new \App\User();
            $user = $user->findOrFail($userid);

            $invoice = new \App\Model\Order\Invoice();
            $invoice = $invoice->where('number', $invoice_number)->first();
            $this->checkSubscriptionExpiry($invoice);
            if ($user && $invoice) {
                if ($user->active == 1) {
                    $product_id = $invoice->order()->value('product');
                    $name = Product::where('id', $product_id)->value('name');
                    $invoice_id = $invoice->id;

                    $release = $this->downloadProduct($uploadid, $userid, $invoice_id, $version_id);

                    if (is_array($release) && array_key_exists('type', $release)) {
                        $release = $release['release'];

                        return view('themes.default1.front.download', compact('release'));
                    } else {
                        if (isS3Enabled()) {
                            if (! Attach::exists('products/'.explode('?', urldecode(basename($release)))[0])) {
                                return redirect()->back()->with('fails', \Lang::get('message.file_not_exist'));
                            }

                            return downloadExternalFile($release, $name);
                        } else {
                            if (! $release instanceof \Symfony\Component\HttpFoundation\StreamedResponse) {
                                return redirect()->back()->with('fails', \Lang::get('message.file_not_exist'));
                            }
                            $customFileName = "{$name}.zip";

                            $release->headers->set(
                                'Content-Disposition',
                                $release->headers->makeDisposition(
                                    ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                                    $customFileName
                                )
                            );

                            return $release;
                        }
                    }
                } else {
                    return redirect()->back()->with('fails', \Lang::get('activate-your-account'));
                }
            } else {
                throw new \Exception(\Lang::get('message.no_permission_for_action'));
            }
        } catch (\Exception $ex) {
            app('log')->error($ex->getMessage());

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function getRelease($owner, $repository, $order_id, $file)
    {
        if ($owner && $repository) {//If the Product is downloaded from Github
            $github_controller = new \App\Http\Controllers\Github\GithubController();
            $relese = $github_controller->listRepositories($owner, $repository, $order_id);

            return ['release' => $relese, 'type' => 'github'];
        } elseif ($file) {
            //If the Product is Downloaded from FileSystem
            $fileName = $file->file;
            $relese = Attach::download('products/'.$fileName);

            return $relese;
        }
    }

    public function getReleaseAdmin($owner, $repository, $file)
    {
        if ($owner && $repository) {
            $github_controller = new \App\Http\Controllers\Github\GithubController();
            $relese = $github_controller->listRepositoriesAdmin($owner, $repository);

            return ['release' => $relese, 'type' => 'github'];
        } elseif ($file->file) {
            // $relese = storage_path().'\products'.'\\'.$file->file;
            //    $relese = '/home/faveo/products/'.$file->file;
            $fileName = $file->file;

            $relese = Attach::download('products/'.$fileName);

            return $relese;
        }
    }

    public function downloadProductAdmin($id, $beta = 1)
    {
        try {
            $product = Product::findOrFail($id);
            $type = $product->type;
            $owner = $product->github_owner;
            $repository = $product->github_repository;
            if ($beta) {
                $file = ProductUpload::where('product_id', '=', $id)->select('file')
                    ->orderBy('created_at', 'desc')
                    ->first();
            } else {
                $file = ProductUpload::where('product_id', '=', $id)->
                    where('is_pre_release', 0)
                    ->select('file')
                    ->orderBy('created_at', 'desc')
                    ->first();
            }
            $permissions = LicensePermissionsController::getPermissionsForProduct($id);
            if ($permissions['downloadPermission'] == 1) {
                $relese = $this->getReleaseAdmin($owner, $repository, $file);

                return $relese;
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Get Price For a Particular Plan Selected.
     *
     * get productid,userid,plan id as request
     *
     * @return json The final Price of the Prduct
     */
    public function getPrice(Request $request)
    {
        try {
            $id = $request->input('product');
            $userid = $request->input('user');
            $plan = $request->input('plan');
            $controller = new \App\Http\Controllers\Front\CartController();
            $price = $controller->cost($id, $plan, $userid, true);
            $field = $this->getProductField($id);
            $quantity = $this->getProductQtyCheck($id, $plan);
            $agents = $this->getAgentQtyCheck($id, $plan);
            $result = ['price' => $price, 'field' => $field, 'quantity' => $quantity, 'agents' => $agents];

            return response()->json($result);
        } catch (\Exception $ex) {
            $result = ['price' => $ex->getMessage(), 'field' => ''];

            return response()->json($result);
        }
    }

    public function updateVersionFromGithub($productid, $github_owner, $github_repository)
    {
        try {
            $product = Product::find($productid)->select('version')->first();
            $github_controller = new \App\Http\Controllers\Github\GithubController();
            $version = $github_controller->findVersion($github_owner, $github_repository);
            $product->version = $version;
            $product->save();
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    /**
     * Check Whether No. of Agents Allowed or Product Qunatity on cart.
     *
     * @author Ashutosh Pathak <ashutosh.pathak@ladybirdweb.com>
     *
     * @date   2019-01-11T00:18:49+0530
     *
     * @param  int  $productid
     * @return bool
     */
    public function allowQuantityOrAgent(int $productid)
    {
        $product = Product::find($productid);

        return $product->show_agent;
    }

    /**
     * Checks Permission for Incresing the no. of Agents/Quantity in Cart.
     *
     *
     * @param  int  $productid  The id of the Product added to the cart
     * @return array The permissons for Agents and Quantity
     */
    public function isAllowedtoEdit(int $productid)
    {
        $product = Product::where('id', $productid)->first();

        $agentModifyPermission = $product->can_modify_agent;
        $quantityModifyPermission = $product->can_modify_quantity;

        return ['agent' => $agentModifyPermission, 'quantity' => $quantityModifyPermission];
    }

    public function productDownload(Request $request)
    {
        if (! $this->validateLicenseManagerAppKey($request->input('app_key'), $request->input('app_secret'))) {
            return errorResponse(\Lang::get('message.invalid_app_key'));
        }

        $fileName = $request->input('file_name');
        $filePath = 'products/'.$fileName;

        if (! $this->fileExists($filePath)) {
            return errorResponse(\Lang::get('message.file_not_exist'));
        }

        return $this->streamProduct($filePath);
    }

    public function productFileExist(Request $request)
    {
        if (! $this->validateLicenseManagerAppKey($request->input('app_key'), $request->input('app_secret'))) {
            return errorResponse(\Lang::get('message.invalid_app_key'));
        }
        $fileName = $request->input('file_name');
        $filePath = 'products/'.$fileName;

        if (! $this->fileExists($filePath)) {
            return errorResponse(\Lang::get('message.file_not_exist'));
        }

        return successResponse(\Lang::get('message.file_exist'));
    }

    private function fileExists($filePath): bool
    {
        return Attach::exists($filePath);
    }

    private function streamProduct($filePath)
    {
        try {
            $response = new StreamedResponse(function () use ($filePath) {
                $stream = Attach::readStream($filePath);
                while (! feof($stream)) {
                    echo fread($stream, 1024 * 8);  // Read in 8 KB chunks
                }

                fclose($stream);
            });

            $response->headers->set('Content-Type', 'application/octet-stream');
            $response->headers->set('Content-Disposition', 'attachment; filename="'.basename($filePath).'"');

            return $response;
        } catch (\Exception $e) {
            return errorResponse(\Lang::get('message.error_occured_while_downloading'));
        }
    }

    private function validateLicenseManagerAppKey($appKey, $appSecret): bool
    {
        return ThirdPartyApp::where('app_key', $appKey)
            ->where('app_secret', $appSecret)
            ->exists();
    }

    public function agentProductDownload(Request $request)
    {
        $product_key = $request->input('product_key');
        $license = new LicenseController();

        $product_id = $license->searchProductUsingProductKey($product_key);

        $version_number = $request->input('version_number');
        $version = ! empty($version_number) ? $version_number : ProductUpload::where('product_id', $product_id)
            ->latest()
            ->value('version');

        if (! $version) {
            return errorResponse(\Lang::get('message.file_not_exist'));
        }

        $product = ProductUpload::where('product_id', $product_id)
            ->where('version', $version)
            ->latest()
            ->first();

        $filePath = 'products/'.$product->file;

        if (! $product || ! $this->fileExists($filePath)) {
            return errorResponse(\Lang::get('message.file_not_exist'));
        }

        return $this->streamProduct($filePath);
    }

    public function getProductUsingLicenseCode(Request $request)
    {
        $license_code = $request->input('license_code');

        $license = new LicenseController();

        $product = $license->searchProductUsingLicense($license_code);

        if (! $product) {
            return errorResponse(\Lang::get('message.product_not_found'));
        }

        $data = [
            'product_id' => $product[0]['product_id'],
        ];

        return successResponse('Product details retrieved successfully', $data);
    }
}
