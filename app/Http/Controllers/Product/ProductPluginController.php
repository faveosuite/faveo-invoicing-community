<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Model\License\LicenseType;
use App\Model\Product\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductPluginController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function index($productId)
    {
        try {
            $product = Product::findOrFail($productId);

            $pluginTypeId  = LicenseType::where('name', 'plugin')->value('id');
            $bundledIds    = $product->bundledPlugins()->pluck('products.id')->toArray();
            $compatibleIds = $product->compatiblePlugins()->pluck('products.id')->toArray();

            $plugins = Product::where('type', $pluginTypeId)
                ->where('id', '!=', $productId)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($p) => [
                    'id'            => $p->id,
                    'name'          => $p->name,
                    'is_bundled'    => in_array($p->id, $bundledIds),
                    'is_compatible' => in_array($p->id, $compatibleIds),
                ]);

            return successResponse('', ['plugins' => $plugins]);
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function sync(Request $request, $productId)
    {
        $request->validate([
            'bundled'      => 'array',
            'bundled.*'    => 'integer',
            'compatible'   => 'array',
            'compatible.*' => 'integer',
        ]);

        try {
            $product = Product::findOrFail($productId);

            $pluginTypeId  = LicenseType::where('name', 'plugin')->value('id');
            $validIds      = Product::where('type', $pluginTypeId)
                ->where('id', '!=', $productId)
                ->pluck('id')->toArray();

            $bundledIds    = array_values(array_intersect($request->input('bundled', []), $validIds));
            $compatibleIds = array_values(array_intersect($request->input('compatible', []), $validIds));

            DB::transaction(function () use ($product, $bundledIds, $compatibleIds) {
                $product->bundledPlugins()->sync($bundledIds);
                $product->compatiblePlugins()->sync($compatibleIds);
            });

            return successResponse(__('message.updated-successfully'));
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }
}
