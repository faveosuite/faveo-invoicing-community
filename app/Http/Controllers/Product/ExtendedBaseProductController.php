<?php

namespace App\Http\Controllers\Product;

use App\Facades\Attach;
use App\Http\Controllers\AutoUpdate\AutoUpdateController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Github\GithubApiController;
use App\Http\Controllers\License\LicensePermissionsController;
use App\Model\Payment\TaxProductRelation;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use Auth;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Lang;
use Logger;
use Symfony\Component\HttpFoundation\Response;

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
                return back()
                    ->with('fails', __('message.product_not_found'));
            }

            return view(
                'themes.default1.product.product.edit-upload-option',
                compact('model', 'selectedProduct')
            );
        } catch (ModelNotFoundException) {
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
            $updateClassObj = new AutoUpdateController();
            $updateClassObj->editVersion($request->input('version'), $productSku);

            return back()->with('success', __('message.product_updated_successfully'));
        } catch (Exception $exception) {
            Logger::exception($exception);
            $message = [$exception->getMessage()];
            $response = ['success' => 'false', 'message' => $message];

            return back()->with('fails', $exception->getMessage());
        }
    }

    public function saveTax($taxes, $product_id): void
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
     */
    public function getProductField(int $productid): string
    {
        try {
            $field = '';
            $product = Product::find($productid);
            if ($product->require_domain == 1) {
                $field .= '<div>
                        <label>'./* @scrutinizer ignore-type */
                         Lang::get('message.domain')."</label>
                        <input type='text' name='domain' class='form-control' 
                        id='domain' placeholder='domain.com or sub.domain.com'>
                </div>";
            }

            if (in_array($product->id, cloudPopupProducts())) {
                $field .= '<div>
    <div class="form-group">
        <label class="required">'./* @scrutinizer ignore-type */ Lang::get('message.cloud_domain').'</label>
        <div class="input-group">
            <input type="text" name="cloud_domain" class="form-control" id="cloud_domain" placeholder="'.__('message.admin_domain').'" required >
            <input type="text" class="form-control" value=".'.cloudSubDomain().'" disabled="true" style="background-color: #4081B5; color:white; border-color: #0088CC">
        </div>
            <span class="error-message" id="cloud-msg"></span>
    </div>
</div>';
            }

            return $field;
        } catch (Exception $exception) {
            return $exception->getMessage();
        }
    }

    public function adminDownload($id, $release = 'official')
    {
        try {
            $permissions = LicensePermissionsController::getPermissionsForProduct($id);
            if (($permissions['downloadPermission'] ?? 0) != 1) {
                throw new Exception(Lang::get('message.no_permission_for_action'));
            }

            $product = Product::findOrFail($id);

            $tag = $product->github_owner
                ? resolve(GithubApiController::class)->latestTag($product->github_owner, $product->github_repository)
                : null;

            $version = $tag ? null : ProductUpload::where('product_id', $id)
                ->where('release_type', $release)
                ->where('is_private', 0)
                ->latest()
                ->first();

            return $this->download($product, $version, $tag);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function download(Product $product, ?ProductUpload $version = null, ?string $tag = null): Response
    {
        if ($product->github_owner && $product->github_repository) {
            if (! $tag) {
                throw new Exception(trans('message.file_not_exist'));
            }

            return redirect(resolve(GithubApiController::class)->resolveDownloadUrl(
                resolve(GithubApiController::class)->zipballUrl($product->github_owner, $product->github_repository, $tag)
            ));
        }

        if (! $version?->file) {
            throw new Exception(trans('message.file_not_exist'));
        }

        $path = 'products/'.$version->file;

        if (! Attach::exists($path)) {
            throw new Exception(trans('message.file_not_exist'));
        }

        return Attach::download($path);
    }

    public function checkSubscriptionExpiry($invoice): void
    {
        $checkSubscription = false;
        if ($invoice) {
            if ($invoice->user_id != Auth::user()->id) {
                throw new Exception(__('message.invalid_modification_data_permission'));
            }

            $checkSubscription = $invoice->order()->first() ? $invoice->order()->first()->subscription : false;
        }

        if ($checkSubscription) {
            if (strtotime((string) $checkSubscription->update_ends_at) > 1 && $checkSubscription->update_ends_at < new Carbon()->toDateTimeString()) {
                throw new Exception(__('message.renew_subscription_download'));
            }
        } else {
            throw new Exception(__('message.no_order_exists_invoice'));
        }
    }

    /**
     * Save Values Related to Cart(eg: whether show Agents or Quantity in Cart etc).
     *
     * @author Ashutosh Pathak <ashutosh.pathak@ladybirdweb.com>
     *
     * @date   2019-01-07T14:34:54+0530
     *
     * @param  Request  $input  All the Product Detais Sent from  the form
     * @param  bool  $can_modify_agent  Whether Agents can be modified by customer
     * @param  bool  $can_modify_quantity  Whether Product Quantity can be modified by Customers
     */
    public function saveCartValues($input, bool $can_modify_agent, bool $can_modify_quantity, $highlight, $add_to_contact): void
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
     * @param  Request  $input  All the Product Detais Sent from  the form
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $product  instance of the Product
     * @return void
     */
    public function saveCartDetailsWhileUpdating($input, $request, $product, $highlight, $add_to_contact): void
    {
        $product->show_agent = $input['show_agent'] == 1 ? 1 : 0; //if Show Agents Selected
        if ($product->show_agent === 1) {
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
