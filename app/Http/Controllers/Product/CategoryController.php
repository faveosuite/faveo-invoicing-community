<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Model\Product\ProductCategory;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Lang;

class CategoryController extends Controller
{
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
     *
     * @return Response
     */
    public function store(Request $request)
    {
        try {
            $productCategory = $this->productCategory->fill($request->input())->save();

            return back()->with('success', Lang::get('message.saved-successfully'));
        } catch (Exception $ex) {
            return back()->with('fails', $ex->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $cat_name = $request->input('category_name');
            $category = $this->productCategory->where('id', $id)->update(['category_name' => $cat_name]);

            return back()->with('success', Lang::get('message.updated-successfully'));
        } catch (Exception $ex) {
            return back()->with('fails', $ex->getMessage());
        }
    }
}
