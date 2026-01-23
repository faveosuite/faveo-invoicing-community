<?php

namespace App\Http\Controllers\PluginsGrouping;

use App\Http\Controllers\Controller;
use App\Model\Configure\ConfigGroup;
use App\Model\Configure\ConfigOption;
use App\Model\Configure\ConfigOptionValue;
use App\Model\Configure\PluginCompatibleWithProducts;
use App\Model\Configure\ProductPluginGroup;
use App\Model\Payment\Plan;
use App\Model\Product\Product;
use Illuminate\Http\Request;

class PluginAdminController extends Controller
{
    public function __construct()
    {
    }

    /**
     * This function is to get all the products id and name.
     *
     * @param
     * @return
     */
    public function index()
    {
        $products = Product::pluck('id', 'name')->toArray();

        return successResponse('', $products);
    }

    /**
     * This function is to get all the compatible plugins for the product.
     *
     * @param
     * @return
     */
    public function getPlugins(Request $request)
    {
        $id = $request->input('id');

        $pluginIds = PluginCompatibleWithProducts::where('product_id', $id)->pluck('plugin_id')->toArray();

        $plugins = Product::whereIn('id', $pluginIds)->pluck('id', 'name')->toArray();

        return successResponse('', $plugins);
    }

    /**
     * This function is to add multiple plugins which are to be shown when buying a product(cross-selling).
     *
     * @param
     * @return
     */
    public function addPlugins(Request $request)
    {
        $product_id = $request->input('product_id');
        $plugin_ids = $request->input('plugin_ids');
        try {
            array_map(function ($id) use ($product_id) {
                PluginCompatibleWithProducts::create(['product_id' => $product_id, 'plugin_id' => $id]);
            }, $plugin_ids);

            return successResponse(__('message.updated-successfully'));
        } catch(\Exception $ex) {
            dd($ex->getMessage());

            return errorResponse($ex->getMessage());
        }
    }

    /**
     * This function deletes plugins for a particular product, when it is done you cannot buy the removed plugin for that product.
     *
     * @param
     * @return
     */
    public function deletePlugins(Request $request)
    {
        $request->validate([
            'plugin_ids' => 'required||array',
            'product_id' => 'required',
        ]);
        $plugin_ids = $request->input('plugin_ids');

        try {
            PluginCompatibleWithProducts::where('product_id', $request->product_id)->whereIn('plugin_id', $plugin_ids)->delete();

            return successResponse(__('message.deleted-successfully'));
        } catch(\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    /**
     * This function is to group multiple plugin for a product, these plugins are given in bulk like a package
     * (This operation is determine what and all plugins are allowed to be grouped).
     *
     * @param
     * @return
     */
    public function productGrouping(Request $request)
    {
        $request->validate([
            'plugin_ids' => 'required',
            'product_id' => 'required',
        ]);
        $product_id = $request->input('product_id');
        $plugin_ids = $request->input('plugin_ids');

        try {
            array_map(function ($id) use ($product_id) {
                ProductPluginGroup::create(['product_id' => $product_id, 'plugin_id' => $id]);
            }, $plugin_ids);

            return successResponse(__('message.updated-successfully'));
        } catch(\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    /**
     * This function is to remove particular group for a product.
     *
     * @param
     * @return
     */
    public function RemoveFromGroup(Request $request)
    {
        $request->validate([
            'group_ids' => 'required||array',
        ]);
        $group_ids = $request->input('group_ids');
        try {
            ProductPluginGroup::whereIn('id', $group_ids)->delete();

            return successResponse(__('message.deleted-successfully'));
        } catch(\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    /**
     * This function is to get all the groups for a product.
     *
     * @param
     * @return
     */
    public function getGroup(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
        ]);
        $product_id = $request->input('product_id');
        try {
            $groups = ProductPluginGroup::with('plugin')
                ->where('product_id', $product_id)
                ->get()
                ->mapwithKeys(function ($group) {
                    return [
                        $group->id => $group->plugin->name,
                    ];
                })
                ->toArray();

            return successResponse('', $groups);
        } catch(\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    /**
     * This function is to create config group(plan, planPrice are also created with this).
     *
     * @param
     * @return
     */
    public function createGroup(Request $request)
    {
        $request->validate([
            'group_name' => 'required',
            'group_description' => 'required',
            'product_id' => 'required',
        ]);
        $group_name = $request->input('group_name');
        $group_description = $request->input('group_description');
        try {
            $this->planCreation($request, $request->product_id, $group_name, $group_description);

            return successResponse(__('message.created-successfully'));
        } catch(\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    /**
     * This function is to delete config group.
     *
     * @param
     * @return
     */
    public function groupDeletion(Request $request)
    {
        $request->validate([
            'group_id' => 'required',
        ]);
        $group_id = $request->input('group_id');
        try {
            $configGroup = ConfigGroup::where('id', $group_id)->first();
            Plan::where('group_id', $configGroup->plan_id)->delete();
            $configGroup->delete();

            return successResponse(__('message.deleted-successfully'));
        } catch(\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    /**
     * This function is to create plan and  planPrice.
     *
     * @param
     * @return
     */
    public function planCreation($request, $product_id, $group_name, $group_description)
    {
        $request->validate([
            'add_price' => 'required',
            'renew_price' => 'required',
            'offer_price' => 'required',
            'name' => 'required',
        ]);
        $add_prices = $request->add_price;
        $renew_prices = $request->renew_price;
        $offer_prices = $request->offer_price;

        $plan = Plan::create(['name' => $request->name, 'product' => $product_id]);
        ConfigGroup::create(['config_group_name' => $group_name, 'description' => $group_description]);

        try {
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
        } catch(\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    /**
     * This function is to create config options and configOptionKeyValue.
     *
     * @param
     * @return
     */
    public function createOption(Request $request)
    {
        $request->validate([
            'group_id' => 'required',
            'option_name' => 'required',
            'option_description' => 'required',
            'config_option_key_value' => 'required',
        ]);
        $group_id = $request->input('group_id');
        $option_name = $request->input('option_name');
        $option_description = $request->input('option_description');
        $config_option_key_value = $request->input('config_option_key_value');
        [$plan_id,$product_id] = array_values(ConfigGroup::where('id', $group_id)->select('plan_id', 'product_id')->first()->toArray());
        try {
            $configOption = ConfigOption::create(['group_id' => $group_id, 'config_option_name' => $option_name, 'config_option_description' => $option_description,
                'plan_id' => $plan_id, 'product_id' => $product_id]);

            $data = [];

            foreach ($config_option_key_value as $key => $value) {
                $data[] = [
                    'option_id' => $configOption->id,
                    'key' => $key,
                    'value' => $value,
                ];
            }

            ConfigOptionValue::insert($data);

            return successResponse(__('message.created-successfully'));
        } catch(\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    /**
     * This function is to delete ConfigOption.
     *
     * @param
     * @return
     */
    public function optionDeletion(Request $request)
    {
        $request->validate([
            'option_ids' => 'required',

        ]);

        $option_ids = $request->input('option_ids');
        try {
            ConfigOption::whereIn('id', $option_ids)->delete();

            return successResponse(__('message.deleted-successfully'));
        } catch (\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }
}
