<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Model\Payment\Currency;
use App\Model\Payment\Plan;
use App\Model\Product\Addon;
use App\Model\Product\Price;
use App\Model\Product\Product;
use App\Model\Product\ProductGroup;
use App\Model\Product\Subscription;
use App\Model\Product\Type;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public $product;
    public $addon;
    public $price;
    public $type;
    public $subscription;
    public $currency;
    public $group;
    public $plan;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin', ['except' => ['userDownload']]);

        $product = new Product();
        $this->product = $product;

        $addon = new Addon();
        $this->addon = $addon;

        $price = new Price();
        $this->price = $price;

        $type = new Type();
        $this->type = $type;

        $subscription = new Subscription();
        $this->subscription = $subscription;

        $currency = new Currency();
        $this->currency = $currency;

        $group = new ProductGroup();
        $this->group = $group;

        $plan = new Plan();
        $this->plan = $plan;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        try {
            return view('themes.default1.product.product.index');
        } catch (\Exception $e) {
            return redirect('/')->with('fails', $e->getMessage());
        }
    }

    public function GetProducts()
    {

        // try {
        return \Datatable::collection($this->product->select('id', 'name', 'type', 'group')->where('id', '!=', 1)->get())
                        ->addColumn('#', function ($model) {
                            return "<input type='checkbox' value=".$model->id.' name=select[] id=check>';
                        })
                        ->addColumn('name', function ($model) {
                            return ucfirst($model->name);
                        })
                        ->addColumn('type', function ($model) {
                            //dd($model->type());
                            if ($this->type->where('id', $model->type)->first()) {
                                return $this->type->where('id', $model->type)->first()->name;
                            } else {
                                return 'Not available';
                            }
                        })
                        ->addColumn('group', function ($model) {
                            //dd($model->type());
                            if ($this->group->where('id', $model->group)->first()) {
                                return $this->group->where('id', $model->group)->first()->name;
                            } else {
                                return 'Not available';
                            }
                        })
                        ->addColumn('price', function ($model) {
                            if ($this->price->where('product_id', $model->id)->first()) {
                                return $this->price->where('product_id', $model->id)->first()->price;
                            } else {
                                return 'Not available';
                            }
                        })
                        ->addColumn('currency', function ($model) {
                            if ($this->price->where('product_id', $model->id)->first()) {
                                return $this->price->where('product_id', $model->id)->first()->currency;
                            } else {
                                return 'Not available';
                            }
                        })
                        ->addColumn('action', function ($model) {
                            return '<a href='.url('products/'.$model->id.'/edit')." class='btn btn-sm btn-primary'>Edit</a>";
                        })
                        ->searchColumns('name', 'email')
                        ->orderColumns('name', 'email')
                        ->make();
//        } catch (\Exception $e) {
//            return redirect()->back()->with('fails', $e->getMessage());
//        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        try {
            /*
             * server url
             */

            $url = $this->GetMyUrl();
            $i = $this->product->orderBy('created_at', 'desc')->first()->id + 1;
            $cartUrl = $url.'/pricing?id='.$i;
            $addon = $this->addon;
            $type = $this->type->lists('name', 'id')->toArray();
            $subscription = $this->plan->lists('name', 'id')->toArray();
            $currency = $this->currency->lists('name', 'code')->toArray();
            $group = $this->group->lists('name', 'id')->toArray();
            $products = $this->product->lists('name', 'id')->toArray();

            return view('themes.default1.product.product.create', compact('subscription', 'addon', 'type', 'currency', 'group', 'cartUrl', 'products'));
        } catch (\Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $v = \Validator::make($input, [
                    'name'         => 'required',
                    'type'         => 'required',
                    'group'        => 'required',
                    'subscription' => 'required',
                    'currency.*'   => 'required',
                    'price.*'      => 'required',
        ]);
        $v->sometimes(['file', 'image'], 'required', function ($input) {
            return $input->type == 2 && $input->github_owner == '' && $input->github_repository == '';
        });

        $v->sometimes(['github_owner', 'github_repository'], 'required', function ($input) {
            return $input->type == 2 && $input->file == '' && $input->image == '';
        });
        if ($v->fails()) {
            return redirect()->back()->with('errors', $v->errors());
            //dd();
        }
        try {
            if ($request->hasFile('image')) {
                $image = $request->file('image')->getClientOriginalName();
                $imagedestinationPath = 'dist/product/images';
                $request->file('image')->move($imagedestinationPath, $image);
                $this->product->image = $image;
            }
            if ($request->hasFile('file')) {
                $file = $request->file('file')->getClientOriginalName();
                $filedestinationPath = storage_path().'/products';
                $request->file('file')->move($filedestinationPath, $file);
                $this->product->file = $file;
            }

            //dd($request->input('currency'));

            $product = $this->product;
            $product->fill($request->except('image', 'file'))->save();

            $product_id = $product->id;
            $subscription = $request->input('subscription');
            $price = $request->input('price');
            $sales_price = $request->input('sales_price');
            $currencies = $request->input('currency');

            foreach ($currencies as $key => $currency) {
                $this->price->create(['product_id' => $product_id, 'currency' => $currency, 'subscription' => $subscription, 'price' => $price[$key], 'sales_price' => $sales_price[$key]]);
            }

            return redirect()->back()->with('success', \Lang::get('message.saved-successfully'));
        } catch (\Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        try {
            $addon = $this->addon;
            $type = $this->type->lists('name', 'id')->toArray();
            $subscription = $this->plan->lists('name', 'id')->toArray();
            $currency = $this->currency->lists('name', 'code')->toArray();
            $group = $this->group->lists('name', 'id')->toArray();
            $products = $this->product->lists('name', 'id')->toArray();
            $url = $this->GetMyUrl();
            $cartUrl = $url.'/cart?id='.$id;
            $product = $this->product->where('id', $id)->first();
            $price = $this->price->where('product_id', $product->id);
            foreach ($currency as $key => $value) {
                if ($this->price->where('product_id', $product->id)->where('currency', $key)->first()) {
                    $regular[$key] = $this->price->where('product_id', $product->id)->where('currency', $key)->first()->price;
                    $sales[$key] = $this->price->where('product_id', $product->id)->where('currency', $key)->first()->sales_price;
                } else {
                    $regular[$key] = '';
                    $sales[$key] = '';
                }
            }
            //dd($regular);

            return view('themes.default1.product.product.edit', compact('product', 'addon', 'type', 'subscription', 'currency', 'group', 'price', 'cartUrl', 'products', 'regular', 'sales'));
        } catch (\Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function update($id, Request $request)
    {
        $input = $request->all();
        //dd($input);
        $v = \Validator::make($input, [
                    'name'         => 'required',
                    'type'         => 'required',
                    'group'        => 'required',
                    'subscription' => 'required',
                    'currency.*'   => 'required',
                    'price.*'      => 'required',
        ]);
        $v->sometimes(['file', 'image'], 'required', function ($input) {
            return $input->type == 2 && $input->github_owner == '' && $input->github_repository == '';
        });

        $v->sometimes(['github_owner', 'github_repository'], 'required', function ($input) {
            return $input->type == 2 && $input->file == '' && $input->image == '';
        });
        if ($v->fails()) {
            return redirect()->back()->with('errors', $v->errors());
            //dd();
        }
        try {
            $product = $this->product->where('id', $id)->first();
            if ($request->hasFile('image')) {
                $image = $request->file('image')->getClientOriginalName();
                $imagedestinationPath = 'dist/product/images';
                $request->file('image')->move($imagedestinationPath, $image);
                $product->image = $image;
            }
            if ($request->hasFile('file')) {
                $file = $request->file('file')->getClientOriginalName();
                $filedestinationPath = storage_path().'/products';
                $request->file('file')->move($filedestinationPath, $file);
                $product->file = $file;
            }

            $product->fill($request->except('image', 'file'))->save();

            $product_id = $product->id;
            $subscription = $request->input('subscription');
            $cost = $request->input('price');
            $sales_price = $request->input('sales_price');
            $currencies = $request->input('currency');

            $prices = $this->price->where('product_id', $product->id)->get();
            foreach ($prices as $price) {
                $price->delete();
            }

            foreach ($currencies as $key => $currency) {
                $this->price->create(['product_id' => $product_id, 'currency' => $currency, 'subscription' => $subscription, 'price' => $cost[$key], 'sales_price' => $sales_price[$key]]);
            }

            return redirect()->back()->with('success', \Lang::get('message.updated-successfully'));
        } catch (\Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy(Request $request)
    {
        try {
            $ids = $request->input('select');
            if (!empty($ids)) {
                foreach ($ids as $id) {
                    if ($id != 1) {
                        $product = $this->product->where('id', $id)->first();
                        if ($product) {
                            $product->delete();
                        } else {
                            echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>".\Lang::get('message.alert').'!</b> '.\Lang::get('message.failed').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        '.\Lang::get('message.no-record').'
                </div>';
                            //echo \Lang::get('message.no-record') . '  [id=>' . $id . ']';
                        }
                        echo "<div class='alert alert-success alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>".\Lang::get('message.alert').'!</b> '.\Lang::get('message.success').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        '.\Lang::get('message.deleted-successfully').'
                </div>';
                    } else {
                        echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>".\Lang::get('message.alert').'!</b> '.\Lang::get('message.failed').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        '.\Lang::get('message.can-not-delete-default').'
                </div>';
                    }
                }
            } else {
                echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>".\Lang::get('message.alert').'!</b> '.\Lang::get('message.failed').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        '.\Lang::get('message.select-a-row').'
                </div>';
                //echo \Lang::get('message.select-a-row');
            }
        } catch (\Exception $e) {
            echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>".\Lang::get('message.alert').'!</b> '.\Lang::get('message.failed').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        '.$e->getMessage().'
                </div>';
        }
    }

    public function GetMyUrl()
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

    public function downloadProduct($id)
    {
        try {
            $product = $this->product->findOrFail($id);
            //dd($product);
            $type = $product->type;
            $owner = $product->github_owner;
            $repository = $product->github_repository;
            $file = $product->file;
            if ($type == 2) {
                if ($owner && $repository) {
                    //dd($repository);
                    $github_controller = new \App\Http\Controllers\Github\GithubController();

                    return $github_controller->getReleaseByTag($owner, $repository);
                } elseif ($file) {
                    $file = storage_path().'/products/'.$file;

                    return \Response::download($file);
                }
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    public function userDownload($userid, $invoice_number)
    {
        try {
            $user = new \App\User();
            $user = $user->findOrFail($userid);
            $invoice = new \App\Model\Order\Invoice();
            $invoice = $invoice->where('number', $invoice_number)->first();
            if ($user && $invoice) {
                if ($user->active == 1) {
                    $invoice_item = new \App\Model\Order\InvoiceItem();
                    $item = $invoice_item->where('invoice_id', $invoice->id)->first();
                    $product_id = $this->product->where('name', $item->product_name)->first()->id;
                    $this->downloadProduct($product_id);
                } else {
                    return redirect('auth/login')->with('fails', \Lang::get('activate-your-account'));
                }
            } else {
                return redirect('auth/login')->with('fails', \Lang::get('please-purcahse-a-product'));
            }
        } catch (Exception $ex) {
        }
    }
}
