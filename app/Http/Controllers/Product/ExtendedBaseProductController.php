<?php

namespace App\Http\Controllers\Product;

use App\Facades\Attach;
use App\Http\Controllers\Controller;
use App\Model\Common\StatusSetting;
use App\Model\Order\Invoice;
use App\Model\Payment\TaxProductRelation;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ExtendedBaseProductController extends Controller
{
    public function getUpload($id)
    {
        // $new_upload = ProductUpload::where('product_id', '=', $id)
        // ->select('id', 'product_id', 'title', 'description', 'version', 'file');

        $new_upload = ProductUpload::leftJoin('products', 'products.id', '=', 'product_uploads.product_id')
                      ->select('product_uploads.title', 'product_uploads.description', 'product_uploads.version', 'product_uploads.file', 'products.name', 'product_uploads.id', 'product_uploads.product_id', 'product_uploads.release_type')
                      ->where('product_id', '=', $id);

        return \DataTables::of($new_upload)
        ->orderColumn('title', '-product_uploads.id $1')
        ->orderColumn('description', '-product_uploads.id $1')
        ->orderColumn('version', '-product_uploads.id $1')
        ->orderColumn('file', '-product_uploads.id $1')
        ->orderColumn('releasetype', '-product_uploads.id $1')
        ->addColumn('checkbox', function ($model) {
            return "<input type='checkbox' class='upload_checkbox' value=".$model->id.' name=select[] id=checks>';
        })

        ->addColumn('product_id', function ($model) {
            return ucfirst($this->product->where('id', $model->product_id)->first()->name);
        })

        ->addColumn('title', function ($model) {
            return ucfirst($model->title);
        })
        ->addColumn('description', function ($model) {
            return ucfirst($model->description);
        })
        ->addColumn('version', function ($model) {
            return $model->version;
        })

        ->addColumn('file', function ($model) {
            return $model->file;
        })
        ->addColumn('releasetype', function ($model) {
            return $model->release_type;
        })
        ->addColumn('action', function ($model) {
            return '<p><a href='.url('edit-upload/'.$model->id).
                                " class='btn btn-sm btn-secondary'".tooltip(__('message.edit'))."<i class='fa fa-edit'
                                 style='color:white;'> </i></a></p>";
        })
         ->filterColumn('title', function ($query, $keyword) {
             $sql = 'title like ?';
             $query->whereRaw($sql, ["%{$keyword}%"]);
         })
         ->filterColumn('description', function ($query, $keyword) {
             $sql = 'product_uploads.description like ?';
             $query->whereRaw($sql, ["%{$keyword}%"]);
         })
        ->filterColumn('version', function ($query, $keyword) {
            $sql = 'product_uploads.version like ?';
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })
        ->filterColumn('releasetype', function ($query, $keyword) {
            $keyword = trim($keyword);
            $query->where('product_uploads.release_type', 'LIKE', "%{$keyword}%");
        })

        ->filterColumn('file', function ($query, $keyword) {
            $sql = 'product_uploads.file like ?';
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })
        ->rawcolumns(['checkbox', 'product_id', 'title', 'description', 'version', 'file', 'releasetype', 'action'])
        ->make(true);
    }

    /**
     * Go to edit Product Upload Page.
     *
     * @date   2019-03-07T13:15:58+0530
     *
     * @param  int  $id  Product Upload id
     */
    public function editProductUpload($id)
    {
        $model = ProductUpload::where('id', $id)->first();
        $selectedProduct = $model->product->name;

        return view('themes.default1.product.product.edit-upload-option', compact('model', 'selectedProduct'));
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
            $file_upload->where('id', $id)->update(['title' => $request->input('title'), 'description' => $request->input('description'), 'version' => $request->input('version'), 'dependencies' => json_encode($request->input('dependencies')), 'is_private' => $request->input('is_private'), 'is_restricted' => $request->input('is_restricted'), 'release_type' => $request->input('release_type')]);
            $autoUpdateStatus = StatusSetting::pluck('license_status')->first();
            if ($autoUpdateStatus == 1) { //If License Setting Status is on,Add Product to the AutoUpdate Script
                $productSku = $file_upload->product->product_sku;
                $updateClassObj = new \App\Http\Controllers\AutoUpdate\AutoUpdateController();
                $addProductToAutoUpdate = $updateClassObj->editVersion($request->input('version'), $productSku);
            }

            return redirect()->back()->with('success', __('message.product_updated_successfully'));
        } catch (\Exception $e) {
            app('log')->error($e->getMessage());
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
            <input type="text" name="cloud_domain" class="form-control" id="cloud_domain" placeholder="'.__('message.extended_domain').'" required >
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
