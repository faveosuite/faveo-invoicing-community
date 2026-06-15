<?php

namespace App\Http\Controllers\Product;

use App\Facades\Attach;
use App\Http\Controllers\Controller;
use App\Model\Order\Invoice;
use App\Model\Payment\TaxProductRelation;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ExtendedBaseProductController extends Controller
{

    /**
     * Go to edit Product Upload Page.
     *
     * @date   2019-03-07T13:15:58+0530
     *
     * @param  int  $id  Product Upload id
     */
    public function editProductUpload($id)
    {
        try {
            $model = ProductUpload::with('product')->findOrFail($id);

            $selectedProduct = $model->product?->name;

            if (! $selectedProduct) {
                return redirect()->back()
                    ->with('fails', __('message.product_not_found'));
            }

            return view(
                'themes.default1.product.product.edit-upload-option',
                compact('model', 'selectedProduct')
            );
        } catch (ModelNotFoundException $e) {
            return redirect()->to('products')
                ->with('fails', __('message.product_not_found'));
        }
    }

    //Update the File Info
    public function uploadUpdate($id, Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'version' => 'required',
            'dependencies' => 'required',
        ],
            [
                'title.required' => __('validation.extend_product.title_required'),
                'version.required' => __('validation.extend_product.version_required'),
                'dependencies.required' => __('validation.extend_product.dependencies_required'),
            ]);
        try {
            $file_upload = ProductUpload::find($id);
            $file_upload->update(['title' => $request->input('title'), 'description' => $request->input('description'), 'version' => $request->input('version'), 'dependencies' => json_encode($request->input('dependencies')), 'is_private' => $request->input('is_private'), 'is_restricted' => $request->input('is_restricted'), 'release_type' => $request->input('release_type')]);
            $productSku = $file_upload->product->product_sku;
            $updateClassObj = new \App\Http\Controllers\AutoUpdate\AutoUpdateController();
            $addProductToAutoUpdate = $updateClassObj->editVersion($request->input('version'), $productSku);

            return redirect()->back()->with('success', __('message.product_updated_successfully'));
        } catch (\Exception $e) {
            \Logger::exception($e);
            $message = [$e->getMessage()];
            $response = ['success' => 'false', 'message' => $message];

            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    public function saveTax($taxes, $product_id)
    {
        TaxProductRelation::where('product_id', $product_id)->delete();
        if ($taxes) {
            foreach ($taxes as $tax) {
                $newTax = new TaxProductRelation();
                $newTax->product_id = $product_id;
                $newTax->tax_class_id = $tax;
                $newTax->save();
            }
        }
    }

    /**
     * Whether the Product Requires the domain to be entered.
     *
     * @param  int  $productid
     */
    public function getProductField(int $productid)
    {
        try {
            $field = '';
            $product = Product::find($productid);
            if ($product->require_domain == 1) {
                $field .= '<div>
                        <label>'./* @scrutinizer ignore-type */
                         \Lang::get('message.domain')."</label>
                        <input type='text' name='domain' class='form-control' 
                        id='domain' placeholder='domain.com or sub.domain.com'>
                </div>";
            }
            if (in_array($product->id, cloudPopupProducts())) {
                $field .= '<div>
    <div class="form-group">
        <label class="required">'./* @scrutinizer ignore-type */ \Lang::get('message.cloud_domain').'</label>
        <div class="input-group">
            <input type="text" name="cloud_domain" class="form-control" id="cloud_domain" placeholder="'.__('message.admin_domain').'" required >
            <input type="text" class="form-control" value=".'.cloudSubDomain().'" disabled="true" style="background-color: #4081B5; color:white; border-color: #0088CC">
        </div>
            <span class="error-message" id="cloud-msg"></span>
    </div>
</div>';
            }

            return $field;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function adminDownload($id, $invoice = '', $api = false, $beta = 1)
    {
        $product = Product::where('id', $id)->get();
        $product = $product->toArray();
        try {
            if ($this->downloadValidation(true, $id, $invoice, $api)) {
                if ($product[0]['github_owner'] && $product[0]['github_repository']) {
                    $repo = $product[0]['github_repository'];
                    $owner = $product[0]['github_owner'];
                    $githubApi = new \App\Http\Controllers\Github\GithubApiController();
                    $url = "https://api.github.com/repos/$owner/$repo/releases";
                    $countExpiry = 0;
                    $link = $githubApi->getCurl1($url);
                    $link = $link['body'];
                    $countVersions = 3; //because we are taking only the first 10 versions
                    $link = array_slice($link, 0, 1, true);
                    $link1 = $githubApi->getCurl1($link[0]['zipball_url']);
                    if ($link1['body'] == null) {
                        $fileName = 'faveo.zip';
                        $url = $link1['header']['location'];

                        return response()->streamDownload(function () use ($url) {
                            echo file_get_contents($url);
                        }, $fileName);
                    } else {
                        $string = $link1['body']['message'];
                        preg_match_all('/https:\/\/[^\s,"]+/', $string, $matches);
                        $url = $matches[0][0];
                        $fileName = 'faveo.zip';

                        return response()->streamDownload(function () use ($url) {
                            echo file_get_contents($url);
                        }, $fileName);
                    }
                }
                $release = $this->downloadProductAdmin($id, $beta);
                $name = Product::where('id', $id)->value('name');
                if (isS3Enabled()) {
                    if (! Attach::exists('products/'.explode('?', urldecode(basename($release)))[0])) {
                        return redirect('my-orders')->with('fails', __('message.file_not_exist'));
                    }

                    return downloadExternalFile($release, $name);
                } else {
                    if (! $release instanceof \Symfony\Component\HttpFoundation\StreamedResponse) {
                        return redirect('my-orders')->with('fails', \Lang::get('message.file_not_exist'));
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
            } else {
                throw new \Exception(\Lang::get('message.no_permission_for_action'));
            }
        } catch (\Exception $e) {
            return redirect('my-orders')->with('fails', $e->getMessage());
        }
    }

    /**
     * Checks whether order exists or not for a product and invoice.
     *
     * @date   2020-04-13T14:53:04+0530
     *
     * @param  int  $id  Product id
     * @param  int  $invoice  Invoice Number
     * @param  bool  $allowDownload
     * @return bool
     */
    private function downloadValidation(bool $allowDownload, $id, $invoice, $api)
    {
        if ($api == false) {
            if (\Auth::user()->role == 'user') {
                $invoice = Invoice::where('number', $invoice)->first(); //If invoice number sent as parameter exists
                $this->checkSubscriptionExpiry($invoice);
                $allowDownload = $invoice ? $invoice->order()->value('product') == $id : false; //If the order for the product sent in the parameter exists
            }
        }

        return $allowDownload;
    }

    public function checkSubscriptionExpiry($invoice)
    {
        $checkSubscription = false;
        if ($invoice) {
            if ($invoice->user_id != \Auth::user()->id) {
                throw new \Exception(__('message.invalid_modification_data_permission'));
            }
            $checkSubscription = $invoice->order()->first() ? $invoice->order()->first()->subscription : false;
        }
        if ($checkSubscription) {
            if (strtotime($checkSubscription->update_ends_at) > 1) {
                if ($checkSubscription->update_ends_at < (new Carbon())->toDateTimeString()) {
                    throw new \Exception(__('message.renew_subscription_download'));
                }
            }
        } else {
            throw new \Exception(__('message.no_order_exists_invoice'));
        }
    }

    /**
     * Save Values Related to Cart(eg: whether show Agents or Quantity in Cart etc).
     *
     * @author Ashutosh Pathak <ashutosh.pathak@ladybirdweb.com>
     *
     * @date   2019-01-07T14:34:54+0530
     *
     * @param  Illuminate\Http\Request  $input  All the Product Detais Sent from  the form
     * @param  bool  $can_modify_agent  Whether Agents can be modified by customer
     * @param  bool  $can_modify_quantity  Whether Product Quantity can be modified by Customers
     * @return
     */
    public function saveCartValues($input, bool $can_modify_agent, bool $can_modify_quantity, $highlight, $add_to_contact)
    {
        $this->product->show_agent = $input['show_agent'] == 1; //if Show Agents Selected
        $this->product->highlight = ($highlight == 1) ? 1 : 0;
        $this->product->add_to_contact = ($add_to_contact == 1) ? 1 : 0;
        $this->product->can_modify_agent = $can_modify_agent;
        $this->product->can_modify_quantity = $can_modify_quantity;
    }

    /**
     * Save Values Related to Cart while Updating Produc(eg: whether show Agents or Quantityof Product in Cart etc).
     *
     * @author Ashutosh Pathak <ashutosh.pathak@ladybirdweb.com>
     *
     * @date   2019-01-07T20:40:20+0530
     *
     * @param  Illuminate\Http\Request  $input  All the Product Detais Sent from  the form
     * @param Illuminate\Http\Request; $request
     * @param  array  $product  instance of the Product
     * @return Save The Details
     */
    public function saveCartDetailsWhileUpdating($input, $request, $product, $highlight, $add_to_contact)
    {
        $product->show_agent = $input['show_agent'] == 1 ? 1 : 0; //if Show Agents Selected
        if ($product->show_agent == 1) {
            $product->can_modify_quantity = 0;
            if ($request->has('can_modify_agent')) {
                $product->can_modify_agent = 1;
            } else {
                $product->can_modify_agent = 0;
                $product->can_modify_quantity = 0;
            }
        } else {
            $product->can_modify_agent = 0;
            if ($request->has('can_modify_quantity')) {
                $product->can_modify_quantity = 1;
            } else {
                $product->can_modify_agent = 0;
                $product->can_modify_quantity = 0;
            }
        }
        $product->highlight = $highlight;
        $product->add_to_contact = $add_to_contact;
        $product->save();
    }
}
