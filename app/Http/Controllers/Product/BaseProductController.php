<?php

namespace App\Http\Controllers\Product;

use App\Model\Payment\Plan;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use Bugsnag;
use Illuminate\Http\Request;

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

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Response
     */
    public function fileDestroy(Request $request)
    {
        $ids = $request->input('select');
        if (!empty($ids)) {
            foreach ($ids as $id) {
                $product = ProductUpload::where('id', $id)->first();
                if ($product) {
                    $product->delete();
                } else {
                    echo "<div class='alert alert-success alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */\Lang::get('message.success').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */\Lang::get('message.no-record').'
                </div>';
                    //echo \Lang::get('message.no-record') . '  [id=>' . $id . ']';
                }
            }
            echo "<div class='alert alert-success alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */\Lang::get('message.success').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */\Lang::get('message.deleted-successfully').'
                </div>';
        } else {
            echo "<div class='alert alert-success alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */ \Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */ \Lang::get('message.success').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */\Lang::get('message.select-a-row').'
                </div>';
            //echo \Lang::get('message.select-a-row');
        }
    }

    public function getProductQtyCheck($productid)
    {
        try {
            $check = self::checkMultiProduct($productid);
            if ($check == true) {
                return "<div class='col-md-4 form-group'>
	                        <label class='required'>"./* @scrutinizer ignore-type */
                            \Lang::get('message.quantity')."</label>
	                        <input type='text' name='quantity' class='form-control' id='quantity' value='1'>
	                </div>";
            }
        } catch (\Exception $ex) {
            Bugsnag::notifyException($ex);

            return $ex->getMessage();
        }
    }

    public function getSubscriptionCheck($productid, Request $request)
    {
        try {
            $controller = new \App\Http\Controllers\Front\CartController();
            $check = $controller->allowSubscription($productid);
            $field = '';
            $price = '';
            if ($check === true) {
                $plan = new Plan();
                $plans = $plan->where('product', $productid)->pluck('name', 'id')->toArray();
                $script = ''; //$this->getSubscriptionCheckScript();
                $field = "<div class='col-md-4 form-group'>
                        <label class='required'>"./* @scrutinizer ignore-type */
                        \Lang::get('message.subscription').'</label>
                       '.\Form::select('plan', ['' => 'Select', 'Plans' => $plans], null,
                        ['class' => 'form-control', 'id' => 'plan', 'onchange' => 'getPrice(this.value)']).'
                </div>'.$script;
            } else {
                $userid = $request->input('user');
                $price = $controller->productCost($productid, $userid);
            }
            $field .= $this->getDescriptionField($productid);
            $result = ['price' => $price, 'field' => $field];

            return response()->json($result);
        } catch (\Exception $ex) {
            Bugsnag::notifyException($ex);

            return $ex->getMessage();
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

            if ($user && $invoice) {
                if ($user->active == 1) {
                    $order = $invoice->order()->orderBy('id', 'desc')->select('product')->first();
                    $product_id = $order->product;
                    $invoice_id = $invoice->id;
                    $release = $this->downloadProduct($uploadid, $userid, $invoice_id, $version_id);
                    if (is_array($release) && array_key_exists('type', $release)) {
                        $release = $release['release'];

                        return view('themes.default1.front.download', compact('release', 'form'));
                    } else {
                        header('Content-type: Zip');
                        header('Content-Description: File Transfer');
                        header('Content-Disposition: attachment; filename=Faveo.zip');
                        //header("Content-type: application/zip");
                        header('Content-Length: '.filesize($release));
                        ob_end_clean();
                        // flush();
                        readfile($release);
                    }
                } else {
                    return redirect('auth/login')->with('fails', \Lang::get('activate-your-account'));
                }
            } else {
                return redirect('auth/login')->with('fails', \Lang::get('please-purcahse-a-product'));
            }
        } catch (\Exception $ex) {
            Bugsnag::notifyException($ex);

            return redirect('auth/login')->with('fails', $ex->getMessage());
        }
    }

    public function getRelease($owner, $repository, $order_id, $file)
    {
        if ($owner && $repository) {//If the Product is downloaded from Github
            $github_controller = new \App\Http\Controllers\Github\GithubController();
            $relese = $github_controller->listRepositories($owner, $repository, $order_id);

            return ['release'=>$relese, 'type'=>'github'];
        } elseif ($file) {
            //If the Product is Downloaded from FileSystem
            $fileName = $file->file;
            $relese = storage_path().'/products'.'//'.$fileName; //For Local Server
            // $relese = '/home/faveo/products/'.$file->file;

            return $relese;
        }
    }

    public function getReleaseAdmin($owner, $repository, $file)
    {
        if ($owner && $repository) {
            $github_controller = new \App\Http\Controllers\Github\GithubController();
            $relese = $github_controller->listRepositoriesAdmin($owner, $repository);

            return ['release'=>$relese, 'type'=>'github'];
        } elseif ($file->file) {
            // $relese = storage_path().'\products'.'\\'.$file->file;
            $relese = '/home/faveo/products/'.$file->file;

            return $relese;
        }
    }

    // public function getLinkToDownload($role, $invoice, $id)
    // {
    //      if ($role == 'user') {
    //         // dd($invoice);
    //         if ($invoice && $invoice != '') {
    //             return $this->downloadProductAdmin($id);
    //         } else {
    //             throw new \Exception('This user has no permission for this action');
    //         }
    //     }

    //     return $this->downloadProductAdmin($id);
    // }

    public function downloadProductAdmin($id)
    {
        try {
            $product = Product::findOrFail($id);
            $type = $product->type;
            $owner = $product->github_owner;
            $repository = $product->github_repository;
            $file = ProductUpload::where('product_id', '=', $id)->select('file')
            ->orderBy('created_at', 'desc')
            ->first();

            if ($type == 2) {
                $relese = $this->getReleaseAdmin($owner, $repository, $file);

                return $relese;
            }
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);

            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    public function getPrice(Request $request)
    {
        try {
            $id = $request->input('product');
            // dd($id);
            $userid = $request->input('user');
            $plan = $request->input('plan');
            $controller = new \App\Http\Controllers\Front\CartController();
            $price = $controller->cost($id, $userid, $plan);
            $field = $this->getProductField($id).$this->getProductQtyCheck($id);

            $result = ['price' => $price, 'field' => $field];

            return response()->json($result);
        } catch (\Exception $ex) {
            Bugsnag::notifyException($ex);
            $result = ['price' => $ex->getMessage(), 'field' => ''];

            return response()->json($result);
        }
    }

    public function updateVersionFromGithub($productid)
    {
        try {
            if (\Input::has('github_owner') && \Input::has('github_repository')) {
                $owner = \Input::get('github_owner');
                $repo = \Input::get('github_repository');
                $product = Product::find($productid);
                $github_controller = new \App\Http\Controllers\Github\GithubController();
                $version = $github_controller->findVersion($owner, $repo);
                $product->version = $version;
                $product->save();
            }
        } catch (\Exception $ex) {
            Bugsnag::notifyException($ex);

            throw new \Exception($ex->getMessage());
        }
    }

    public static function checkMultiProduct($productid)
    {
        try {
            $product = new Product();
            $product = $product->find($productid);
            if ($product) {
                // dd($product->multiple_qty == 1);
                if ($product->multiple_qty == 1) {
                    return true;
                }
            }

            return false;
        } catch (\Exception $ex) {
            Bugsnag::notifyException($ex);
        }
    }

    public function getDescriptionField($productid)
    {
        try {
            $product = Product::find($productid);
            $field = '';

            if ($product->retired == 1) {
                $field .= "<div class='col-md-4 form-group'>
	                        <label class='required'>"./* @scrutinizer ignore-type */
                             \Lang::get('message.description')."</label>
	                        <textarea name='description' class='form-control'
                             id='description' placeholder='Description'></textarea>
	                </div>";
            }

            return $field;
        } catch (\Exception $ex) {
            Bugsnag::notifyException($ex);

            return $ex->getMessage();
        }
    }
}
