<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Model\Product\ProductCategory;
use Exception;
use Illuminate\Http\Request;
use Lang;

class CategoryController extends Controller
{
    /**
     * @var \App\Model\Product\ProductCategory
     */
    public $productCategory;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');

        $productCategory = new ProductCategory();
        $this->productCategory = $productCategory;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function store(Request $request): mixed
    {
        try {
            $productCategory = $this->productCategory->fill($request->input())->save();

            return back()->with('success', Lang::get('message.saved-successfully'));
        } catch (Exception $exception) {
            return back()->with('fails', $exception->getMessage());
        }
    }

    public function update(Request $request, mixed $id): mixed
    {
        try {
            $cat_name = $request->input('category_name');
            $category = $this->productCategory->where('id', $id)->update(['category_name' => $cat_name]);

            return back()->with('success', Lang::get('message.updated-successfully'));
        } catch (Exception $exception) {
            return back()->with('fails', $exception->getMessage());
        }
    }
}
