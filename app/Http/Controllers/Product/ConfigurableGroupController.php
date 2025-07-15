<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\GroupRequest;
use App\Model\Common\Country;
use App\Model\Common\PricingTemplate;
use App\Model\Configure\ConfigGroup;
use App\Model\Payment\Currency;
use App\Model\Payment\Plan;
use App\Model\Product\ConfigurableOption;
use App\Model\Product\GroupFeatures;
use App\Model\Product\Product;
use App\Model\Product\ProductGroup;
use Illuminate\Http\Request;
use function Laravel\Prompts\error;

class ConfigurableGroupController extends Controller{
    public $group;
    public $feature;
    public $config;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');

        $group = new ProductGroup();
        $this->group = $group;

        $feature = new GroupFeatures();
        $this->feature = $feature;

        $config = new ConfigurableOption();
        $this->config = $config;
    }

    public function index()
    {
        try {
            return view('themes.default1.product.configurable-group.index');
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function getConfigurableGroups(Request $request){
    $configOptions=$this->attachTables();

        return \DataTables::of($configOptions)
            ->addColumn('group_name', function($configOptions){
                return $configOptions->config_group_name;
            })
            ->addColumn('group_description', function($configOptions){
                return $configOptions->description;
            })
            ->addColumn('products', function($configOptions){
                return $configOptions->product_id;
            })
            ->addColumn('action', function ($configOptions) {
                return '<a href='.url('option/'.$configOptions->id.'/edit')
                    ." class='btn btn-sm btn-secondary btn-xs'".tooltip(__('message.edit'))."
                            <i class='fa fa-edit' style='color:white;'> </i></a>";
            })
            ->make(true);
    }
    public function attachTables(){
        return ConfigGroup::leftJoin('config_option','config_option.group_id','=','config_group.id')
            ->select('config_group.*','config_option.id as config_option_id','config_option.config_option_name','config_option.group_id',
                    'config_option.config_option_description','config_option.plan_id','config_option.product_id')
            ->get();
    }

    public function create(){
        try {
            $product=Product::all();
            $currency = Currency::where('status', '1')->pluck('name', 'code')->toArray();
            $countries = Country::get(['country_id', 'country_name'])->toArray();
            return view('themes.default1.product.configurable-group.create',compact('product','currency','countries'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }


    public function groupEditDisplay($id){
        $group=ConfigGroup::where('id',$id)->first();
        $product=Product::all();
        $group_id=$product->where('name',$group->config_group_name)->value('id');
        $currency = Currency::where('status', '1')->pluck('name', 'code')->toArray();
        $countries = Country::get(['country_id', 'country_name'])->toArray();
        return view('themes.default1.product.configurable-group.edit',compact('product','currency','countries','group','group_id'));

        }

    public function groupEdit(Request $request){
        dd($request->all());
    }

    public function groupCreate(Request $request){
        $product_name=$request->input('product_name');
        $description=$request->input('description');

        try{
            ConfigGroup::create(
                [
                    'config_group_name'=>$product_name,
                    'description'=>$description,
                ]
            );
            return successResponse('SuccessFully Created');
        }catch(\Exception $ex){
            return errorResponse($ex->getMessage());
        }
    }


}