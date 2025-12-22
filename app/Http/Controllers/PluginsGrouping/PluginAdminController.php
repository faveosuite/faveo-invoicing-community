<?php
namespace App\Http\Controllers\PluginsGrouping;

use App\Http\Controllers\Controller;
use App\Model\Configure\ConfigGroup;
use App\Model\Configure\ConfigOption;
use App\Model\Configure\ConfigOptionValue;
use App\Model\Configure\PluginCompatibleWithProducts;
use App\Model\Configure\ProductPluginGroup;
use App\Model\Payment\Period;
use App\Model\Payment\Plan;
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


public function productGrouping(Request $request){
        $product_id=$request->input('product_id');
        $plugin_ids=$request->input('group_ids');

        try {
            array_map(function ($id) use ($product_id) {
                ProductPluginGroup::create(['product_id'=>$product_id,'plugin_id'=>$id]);
            },$plugin_ids);

            return successResponse(__('message.updated-successfully'));
        }catch(\Exception $ex){
            return errorResponse($ex->getMessage());
        }

}


public function RemoveFromGroup(Request $request){
        $group_ids=$request->input('group_ids');
        try{
            ProductPluginGroup::whereIn('id',$group_ids)->delete();
            return successResponse(__('message.deleted-successfully'));
        }catch(\Exception $ex){
            return errorResponse($ex->getMessage());
        }
}

public function getGroup(Request $request){
        $product_id=$request->input('product_id');
        try {
            $groups = ProductPluginGroup::with('plugin')
                ->where('product_id', $product_id)
                ->get()
                ->mapwithKeys(function ($group) {
                    return [
                        $group->id => $group->plugin->name
                    ];
                })
                ->toArray();
            return successResponse('', $groups);
        }catch(\Exception $ex){
            return errorResponse($ex->getMessage());
        }
}


public function createGroup(Request $request){
    $group_name=$request->input('group_name');
    $group_description=$request->input('group_description');
    try{
        $group=ConfigGroup::create(['config_group_name'=>$group_name,'description'=>$group_description]);
        $this->planCreation($request,$group->id);
        return successResponse(__('message.created-successfully'));
    }catch(\Exception $ex){
        return errorResponse($ex->getMessage());
    }
}

public function groupDeletion(Request $request){
        $group_id=$request->input('group_id');
        try{
            ConfigGroup::where('id',$group_id)->delete();
            Plan::where('group',$group_id)->delete();
            return successResponse(__('message.deleted-successfully'));
        }catch(\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
}

public function planCreation($request,$group_id){

    $add_prices = $request->add_price;
    $renew_prices = $request->renew_price;
    $offer_prices = $request->offer_price;

    $plan=Plan::create(['name'=>$request->name,'group'=>$group_id]);

    if (count($add_prices) > 0) {
        $dataForCreating = [];
        foreach ($add_prices as $key => $value) {
            $dataForCreating[] = [
                'plan_id' => $plan->id,
                'currency' => $request->input('currency')[$key],
                'add_price' => $value,
                'renew_price' => $renew_prices[$key],
                'offer_price' => $offer_prices[$key] !== '' ? $offer_prices[$key] : null,
                'price_description' => $request->input('price_description'),
                'product_quantity' => $request->input('product_quantity'),
                'no_of_agents' => $request->no_of_agents,
            ];
        }
        $plan->planPrice()->insert($dataForCreating);
    }
}


public function createOption(Request $request){
        $group_id=$request->input('group_id');
        $option_name=$request->input('option_name');
        $option_description=$request->input('option_description');
        $config_option_key_value=$request->input('config_option_key_value');
        [$plan_id,$product_id]=array_values(ConfigGroup::where('id',$group_id)->pluck('plan_id','product_id')->toArray());
        try{
            $configOption=ConfigOption::create(['group_id' => $group_id, 'config_option_name' => $option_name, 'config_option_description'=>$option_description,
                                'plan_id' => $plan_id, 'product_id' => $product_id]);

            $data = [];

            foreach ($config_option_key_value as $key => $value) {
                $data[] = [
                    'option_id' => $configOption->id,
                    'key'       => $key,
                    'value'     => $value,
                ];
            }

            ConfigOptionValue::insert($data);

            return successResponse(__('message.created-successfully'));
        }catch(\Exception $ex){
            return errorResponse($ex->getMessage());
        }
            
}

public function optionDeletion(Request $request)
{
    $option_ids = $request->input('option_ids');
    try {
        ConfigOption::whereIn('id', $option_ids)->delete();
        return successResponse(__('message.deleted-successfully'));
    } catch (\Exception $ex) {
        return errorResponse($ex->getMessage());
    }
}






}