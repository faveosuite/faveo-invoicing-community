<?php
namespace App\Http\Controllers\PluginsGrouping;

use App\Facades\Cart;
use App\Http\Controllers\Controller;
use App\Model\Configure\ConfigGroup;
use App\Model\Configure\ConfigOptionValue;
use App\Model\Configure\PluginCompatibleWithProducts;
use App\Model\Product\Product;
use Illuminate\Http\Request;


class PluginClientController extends Controller{

    protected $cart;

    public function  __construct(){
        $this->cart=new Cart();
    }

    public function getPlugins(Request $request){
        $id=$request->input('id');

        $pluginIds= PluginCompatibleWithProducts::where('product_id',$id)->pluck('plugin_id')->toArray();

        $plugins=Product::whereIn('id',$pluginIds)->pluck('id','name','shoping_cart_link')->toArray();

        return successResponse('',$plugins);

    }

    public function checkProduct(Request $request){
        $plugin_id=$request->input('plugin_id');
        $product_id=$request->input('product_id');

        $count=PluginCompatibleWithProducts::where('plugin_id',$plugin_id)
                                        ->where('product_id',$product_id)->count();

        if($count>0){
            return successResponse('true');
        }else{
            return successResponse('false');
        }
    }

    public function getGroupsWithOptions(Request $request){
        $product_id=$request->input('product_id');
        try {
            $groups = ConfigGroup::with('configOptions')->where('product_id', $product_id)->mapWithKeys(function ($groups) {
                return [
                    $groups->id => $groups->configOptions
                ];
            });
            return successResponse('', $groups);
        }catch(\Exception $ex){
            return errorResponse($ex->getMessage());
        }
    }

    public function getOptionKeyValue(Request $request){
        $option_id=$request->input('option_id');
        try {
            $configOptionValue=ConfigOptionValue::where('option_id',$option_id)->pluck('key','value')->toArray();
            return successResponse('',[$configOptionValue]);
    }catch(\Exception $exception){
            return errorResponse($exception->getMessage());
        }
    }


    public function addGroupToTheProduct(Request $request){

    }

}