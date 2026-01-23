<?php

namespace App\Http\Controllers\PluginsGrouping;

use App\Facades\Cart;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Front\CartController;
use App\Model\Configure\ConfigGroup;
use App\Model\Configure\ConfigOptionValue;
use App\Model\Configure\PluginCompatibleWithProducts;
use App\Model\Payment\Plan;
use App\Model\Product\Product;
use Illuminate\Http\Request;

class PluginClientController extends Controller
{
    protected $cart;

    public function __construct()
    {
        $this->cart = new Cart();
    }

    /**
     * This function is to all the plugins that are to be displayed for cross-selling.
     *
     * @param
     * @return
     */
    public function getPlugins(Request $request)
    {
        $id = $request->input('id');

        $pluginIds = PluginCompatibleWithProducts::where('product_id', $id)->pluck('plugin_id')->toArray();

        $plugins = Product::whereIn('id', $pluginIds)->get(['id', 'name', 'shoping_cart_link'])->toArray();

        return successResponse('', $plugins);
    }

    /**
     * This function is to check when buying a plugin individually, check if it is compatible with already bought product.
     *
     * @param
     * @return
     */
    public function checkProduct(Request $request)
    {
        $request->validate([
            'plugin_id' => 'required',
            'product_id' => 'required',
        ]);
        $plugin_id = $request->input('plugin_id');
        $product_id = $request->input('product_id');

        $count = PluginCompatibleWithProducts::where('plugin_id', $plugin_id)
                                        ->where('product_id', $product_id)->count();

        if ($count > 0) {
            return successResponse('true');
        } else {
            return successResponse('false');
        }
    }

    /**
     * This function is to get groups with options.
     *
     * @param
     * @return
     */
    public function getGroupsWithOptions(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
        ]);
        $product_id = $request->input('product_id');
        try {
            $groups = ConfigGroup::with('configOptions')
                ->where('product_id', $product_id)
                ->get()
                ->mapWithKeys(function ($group) {
                    return [
                        $group->id => $group->configOptions,
                    ];
                });

            return successResponse('', $groups);
        } catch(\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    /**
     * This function is to get options key,value pair.
     *
     * @param
     * @return
     */
    public function getOptionKeyValue(Request $request)
    {
        $request->validate([
            'option_id' => 'required',
        ]);
        $option_id = $request->input('option_id');
        try {
            $configOptionValue = ConfigOptionValue::where('option_id', $option_id)->pluck('value', 'key')->toArray();

            return successResponse('', [$configOptionValue]);
        } catch(\Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    /**
     * This function is to ot add group to the product.
     *
     * @param
     * @return
     */
    public function addGroupToProduct(Request $request)
    {
        $request->validate([
            'group_id' => 'required',
        ]);
        $id = $request->group_id;
        try {
            $groupDetails = ConfigGroup::where('id', $id)->first();
            $plan_id = $groupDetails->plan_id;
            $query = Plan::where('id', $groupDetails->plan_id)->first();

            $userPlan = userCurrencyAndPrice(\Auth::user()->id, $query);
            if (empty($userPlan['plan'])) {
                return errorResponse(__('message.no_available_plans_currency'));
            }
            $cartController = new CartController();
            $actualPrice = $cartController->applyOfferPrice($userPlan, true);
            $content = $this->cart->getContent();
            $groupData = ['groupId' => $groupDetails->id, 'groupName' => $groupDetails->config_group_name, 'groupPrice' => $actualPrice];
            foreach ($content as $key => $cont) {
                if ($cont['id'] == $plan_id) {
                    $price = $cont['price'] + $actualPrice;

                    $this->cart->update($key, [
                        'price' => $price,
                        'group' => $groupData,
                    ]);
                }
            }

            return successResponse(__('message.updated-successfully'));
        } catch(\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    /**
     * This function is to remove group from the product.
     *
     * @param
     * @return
     */
    public function removeGroupFromProduct(Request $request)
    {
        $request->validate([
            'group_id' => 'required',
        ]);
        $id = $request->group_id;
        try {
            $groupDetails = ConfigGroup::where('id', $id)->first();
            $plan_id = $groupDetails->plan_id;
            $query = Plan::where('id', $groupDetails->plan_id)->first();
            $userPlan = userCurrencyAndPrice('', $query);
            if (empty($userPlan['plan'])) {
                return errorResponse(__('message.no_available_plans_currency'));
            }
            $cartController = new CartController();
            $actualPrice = $cartController->applyOfferPrice($userPlan, true);
            $content = $this->cart->getContent();

            foreach ($content as $key => $cont) {
                if ($cont['id'] == $plan_id) {
                    $this->cart->update($cont['id'], ['price' => $actualPrice, 'group' => []]);
                }
            }

            return successResponse(__('message.updated-successfully'));
        } catch(\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    /**
     * This function is to remove whole group,that is a product with multiple plugins.
     *
     * @param
     * @return
     */
    public function removeWholeGroup(Request $request)
    {
        $request->validate([
            'groupedProductId' => 'required',
        ]);
        $groupedProductId = $request->groupedProductId;
        $content = $this->cart->getContent();
        try {
            foreach ($content as $cont) {
                if ($cont['groupedProductId'] == $groupedProductId) {
                    $this->cart->remove($cont['id']);
                }
            }

            return successResponse(__('message.removed-successfully'));
        } catch(\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }
}
