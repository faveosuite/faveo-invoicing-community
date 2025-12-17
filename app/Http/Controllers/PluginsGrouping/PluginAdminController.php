<?php
namespace App\Http\Controllers\PluginsGrouping;

use App\Http\Controllers\Controller;
use App\Model\Configure\PluginCompatibleWithProducts;
use App\Model\Product\Product;
use Illuminate\Http\Request;


class PluginAdminController extends Controller{

    protected string $test;

    public function __construct(){
        $this->test='';
    }

public function index()
{
    $products= Product::pluck('id','name')->toArray();
    return successResponse('',$products);
}

public function getPlugins(Request $request){
        $id=$request->input('id');

        $pluginIds= PluginCompatibleWithProducts::where('product_id',$id)->pluck('plugin_id')->toArray();

        $plugins=Product::whereIn('id',$pluginIds)->pluck('id','name')->toArray();

        return successResponse('',$plugins);

}

public function addPlugins(Request $request){

        $product_id=$request->input('product_id');
        $plugin_ids=$request->input('plugin_ids');
        try {
            array_map(function ($id) use ($product_id) {
                PluginCompatibleWithProducts::create(['product_id' => $product_id, 'plugin_id' => $id]);
            }, $plugin_ids);

            return successResponse(__('message.updated-successfully'));
        }catch(\Exception $ex){
            return errorResponse($ex->getMessage());
        }
}

public function deletePlugins(Request $request){
        $plugin_ids=$request->input('plugin_ids');

        try{
            PluginCompatibleWithProducts::whereIn('plugin_id',$plugin_ids)->delete();
            return successResponse(__('message.deleted-successfully'));
        }catch(\Exception $ex){
            return errorResponse($ex->getMessage());
        }
}


}