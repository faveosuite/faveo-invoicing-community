<?php
namespace App\Http\Controllers\PluginsGrouping;

use App\Http\Controllers\Controller;
use App\Model\Configure\PluginCompatibleWithProducts;
use App\Model\Product\Product;
use Illuminate\Http\Request;

class PluginClientController extends Controller{

    public function  __construct(){

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





    }

}