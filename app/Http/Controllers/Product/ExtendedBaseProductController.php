<?php

namespace App\Http\Controllers\Product;

use App\Facades\Attach;
use App\Http\Controllers\AutoUpdate\AutoUpdateController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Github\GithubApiController;
use App\Http\Controllers\License\LicensePermissionsController;
use App\License\Services\ProductBundleStampingService;
use App\Model\Order\Order;
use App\Model\Payment\TaxProductRelation;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use App\User;
use Auth;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Logger;
use Symfony\Component\HttpFoundation\Response;

class ExtendedBaseProductController extends Controller
{
    public function __construct(protected ProductBundleStampingService $stampingService)
    {
    }

    // Update the File Info
    public function uploadUpdate(mixed $id, Request $request): mixed
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
            /** @var ProductUpload $file_upload */
            $file_upload = ProductUpload::find($id);
            $file_upload->update(['title' => $request->input('title'), 'description' => $request->input('description'), 'version' => $request->input('version'), 'dependencies' => json_encode($request->input('dependencies')), 'is_private' => $request->input('is_private'), 'is_restricted' => $request->input('is_restricted'), 'release_type' => $request->input('release_type')]);
            /** @var Product $productFromUpload */
            $productFromUpload = $file_upload->product;
            $productSku = $productFromUpload->product_sku;
            $updateClassObj = new AutoUpdateController; // @phpstan-ignore arguments.count
            $updateClassObj->editVersion($request->input('version'), $productSku);

            return successResponse(__('message.product_updated_successfully'));
        } catch (Exception $exception) {
            Logger::exception($exception);

            return errorResponse($exception->getMessage());
        }
    }

    public function saveTax(mixed $taxes, mixed $product_id): void
    {
        TaxProductRelation::where('product_id', $product_id)->delete();
        if ($taxes) {
            foreach ($taxes as $tax) {
                $newTax = new TaxProductRelation;
                $newTax->product_id = $product_id;
                $newTax->tax_class_id = $tax;
                $newTax->save();
            }
        }
    }

    public function adminDownload(mixed $id, mixed $release = 'official', ?Order $order = null): Response|JsonResponse
    {
        try {
            $permissions = LicensePermissionsController::getPermissionsForProduct($id);
            if (($permissions['downloadPermission'] ?? 0) != 1) {
                throw new Exception(__('message.no_permission_for_action'));
            }

            /** @var Product $product */
            $product = Product::findOrFail($id);

            $tag = $product->github_owner
                ? resolve(GithubApiController::class)->latestTag($product->github_owner, $product->github_repository)
                : null;

            $version = $tag ? null : ProductUpload::where('product_id', $id)
                ->where('release_type', $release)
                ->where('is_private', 0)
                ->latest()
                ->first();

            return $this->download($product, $version, $tag, $order);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    /**
     * Pass $order when this download is for a specific customer's order
     * (e.g. My Orders, or the public order-number download link) — if that
     * order has localized (File-mode) licensing enabled, the customer's own
     * signed license file and the signing public key are embedded into the
     * downloaded zip. Left null for admin-panel preview downloads that
     * aren't tied to any customer.
     */
    public function download(Product $product, ?ProductUpload $version = null, ?string $tag = null, ?Order $order = null): Response
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

        return $this->stampingService->downloadResponseFor($version, $product, $path, $order);
    }

    public function checkSubscriptionExpiry(mixed $invoice): void
    {
        $checkSubscription = false;
        if ($invoice) {
            /** @var User $authUser */
            $authUser = Auth::user();
            if ($invoice->user_id != $authUser->id) {
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
    public function saveCartValues($input, bool $can_modify_agent, bool $can_modify_quantity, mixed $highlight, mixed $add_to_contact): void
    {
        $this->product->show_agent = $input['show_agent'] == 1; // if Show Agents Selected // @phpstan-ignore property.notFound
        $this->product->highlight = ($highlight == 1) ? 1 : 0; // @phpstan-ignore property.notFound
        $this->product->add_to_contact = ($add_to_contact == 1) ? 1 : 0; // @phpstan-ignore property.notFound
        $this->product->can_modify_agent = $can_modify_agent; // @phpstan-ignore property.notFound
        $this->product->can_modify_quantity = $can_modify_quantity; // @phpstan-ignore property.notFound
    }

    /**
     * Save Values Related to Cart while Updating Produc(eg: whether show Agents or Quantityof Product in Cart etc).
     *
     * @author Ashutosh Pathak <ashutosh.pathak@ladybirdweb.com>
     *
     * @date   2019-01-07T20:40:20+0530
     *
     * @param  Request  $input  All the Product Detais Sent from  the form
     * @param  Request  $request
     * @param  mixed  $product  instance of the Product
     */
    public function saveCartDetailsWhileUpdating($input, $request, $product, mixed $highlight, mixed $add_to_contact): void
    {
        $product->show_agent = $input['show_agent'] == 1 ? 1 : 0; // if Show Agents Selected
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
