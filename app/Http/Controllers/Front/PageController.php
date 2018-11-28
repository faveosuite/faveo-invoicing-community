<?php

namespace App\Http\Controllers\Front;

use App\DefaultPage;
use App\Model\Front\FrontendPage;
use App\Model\Product\ProductGroup;
use Bugsnag;
use Illuminate\Http\Request;

class PageController extends GetPageTemplateController
{
    public $page;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');

        $page = new FrontendPage();
        $this->page = $page;
    }

    public function index()
    {
        try {
            return view('themes.default1.front.page.index');
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function getLocation()
    {
        try {
            $location = \GeoIP::getLocation();

            return $location;
        } catch (Exception $ex) {
            app('log')->error($ex->getMessage());
            Bugsnag::notifyException($ex);
            $error = $ex->getMessage();
            $location = \Config::get('geoip.default_location');

            return $location;
        }
    }

    public function getPages()
    {
        return \DataTables::of($this->page->get())
                        ->addColumn('checkbox', function ($model) {
                            return "<input type='checkbox' class='page_checkbox' 
                            value=".$model->id.' name=select[] id=check>';
                        })
                        ->addColumn('name', function ($model) {
                            return ucfirst($model->name);
                        })
                        ->addColumn('url', function ($model) {
                            return $model->url;
                        })
                        ->addColumn('created_at', function ($model) {
                            return $model->created_at;
                        })

                        ->addColumn('content', function ($model) {
                            return str_limit($model->content, 10, '...');
                        })
                        ->addColumn('action', function ($model) {
                            return '<a href='.url('pages/'.$model->id.'/edit')
                            ." class='btn btn-sm btn-primary btn-xs'><i class='fa fa-edit'
                                 style='color:white;'> </i>&nbsp;&nbsp;Edit</a>";
                        })

                          ->rawColumns(['checkbox', 'name', 'url',  'created_at', 'content', 'action'])
                        ->make(true);
        // ->searchColumns('name', 'content')
                        // ->orderColumns('name')
                        // ->make();
    }

    public function create()
    {
        try {
            $parents = $this->page->pluck('name', 'id')->toArray();

            return view('themes.default1.front.page.create', compact('parents'));
        } catch (\Exception $ex) {
            app('log')->error($ex->getMessage());
            Bugsnag::notifyException($ex);

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $page = $this->page->where('id', $id)->first();
            $parents = $this->page->where('id', '!=', $id)->pluck('name', 'id')->toArray();
            $selectedDefault = DefaultPage::value('page_id');
            $date = $this->page->where('id', $id)->pluck('created_at')->first();
            $publishingDate = date('d/m/Y', strtotime($date));

            return view('themes.default1.front.page.edit', compact('parents', 'page', 'default', 'selectedDefault', 'publishingDate'));
        } catch (\Exception $ex) {
            dd($ex);

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name'    => 'required',
            'publish' => 'required',
            'slug'    => 'required',
            'url'     => 'required',
            'content' => 'required',
        ]);

        try {
            $this->page->fill($request->input())->save();

            return redirect()->back()->with('success', \Lang::get('message.saved-successfully'));
        } catch (\Exception $ex) {
            app('log')->error($ex->getMessage());
            Bugsnag::notifyException($ex);

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function update($id, Request $request)
    {
        $this->validate($request, [
            'name'           => 'required',
            'publish'        => 'required',
            'slug'           => 'required',
            'url'            => 'required',
            'content'        => 'required',
            'default_page_id'=> 'required',
            'created_at'     => 'required',
        ]);

        try {
            $page = $this->page->where('id', $id)->first();
            $page->fill($request->except('created_at'))->save();
            // $date = $request->input('created_at');

            $date = \DateTime::createFromFormat('d/m/Y', $request->input('created_at'));
            $page->created_at = $date->format('Y-m-d H:i:s');
            $page->save();
            $defaultUrl = $this->page->where('id', $request->input('default_page_id'))->pluck('url')->first();
            DefaultPage::findorFail(1)->update(['page_id'=>$request->input('default_page_id'), 'page_url'=>$defaultUrl]);

            return redirect()->back()->with('success', \Lang::get('message.updated-successfully'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function getPageUrl($slug)
    {
        $productController = new \App\Http\Controllers\Product\ProductController();
        $url = $productController->getMyUrl();
        $segment = $this->addSegment(['public/pages']);
        $url = $url.$segment;

        $slug = str_slug($slug, '-');
        echo $url.'/'.$slug;
    }

    public function getSlug($slug)
    {
        $slug = str_slug($slug, '-');
        echo $slug;
    }

    public function addSegment($segments = [])
    {
        $segment = '';
        foreach ($segments as $seg) {
            $segment .= '/'.$seg;
        }

        return $segment;
    }

    public function generate(Request $request)
    {
        // dd($request->all());
        if ($request->has('slug')) {
            $slug = $request->input('slug');

            return $this->getSlug($slug);
        }
        if ($request->has('url')) {
            $slug = $request->input('url');

            return $this->getPageUrl($slug);
        }
    }

    public function show($slug)
    {
        try {
            $page = $this->page->where('slug', $slug)->where('publish', 1)->first();
            if ($page && $page->type == 'cart') {
                return $this->cart();
            }

            return view('themes.default1.front.page.show', compact('page'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Response
     */
    public function destroy(Request $request)
    {
        try {
            $ids = $request->input('select');
            $defaultPageId = DefaultPage::pluck('page_id')->first();
            if (!empty($ids)) {
                foreach ($ids as $id) {
                    if ($id != $defaultPageId) {
                        $page = $this->page->where('id', $id)->first();
                        if ($page) {
                            // dd($page);
                            $page->delete();
                        } else {
                            echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */
                    \Lang::get('message.failed').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */\Lang::get('message.no-record').'
                </div>';
                            //echo \Lang::get('message.no-record') . '  [id=>' . $id . ']';
                        }
                        echo "<div class='alert alert-success alert-dismissable'>
                    <i class='fa fa-ban'></i>

                    <b>"./* @scrutinizer ignore-type */ \Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */
                    \Lang::get('message.success').'

                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */\Lang::get('message.deleted-successfully').'
                </div>';
                    } else {
                        echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */\Lang::get('message.failed').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */ \Lang::get('message.can-not-delete-default-page').'
                </div>';
                    }
                }
            } else {
                echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */\Lang::get('message.failed').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */\Lang::get('message.select-a-row').'
                </div>';
                //echo \Lang::get('message.select-a-row');
            }
        } catch (\Exception $e) {
            echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */\Lang::get('message.failed').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        '.$e->getMessage().'
                </div>';
        }
    }

    public function search(Request $request)
    {
        try {
            $search = $request->input('q');
            $model = $this->result($search, $this->page);

            return view('themes.default1.front.page.search', compact('model'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function transform($type, $data, $trasform = [])
    {
        $config = \Config::get("transform.$type");
        $result = '';
        $array = [];
        foreach ($trasform as $trans) {
            $array[] = $this->checkConfigKey($config, $trans);
        }
        $c = count($array);
        for ($i = 0; $i < $c; $i++) {
            $array1 = $this->keyArray($array[$i]);
            $array2 = $this->valueArray($array[$i]);
            $result .= str_replace($array1, $array2, $data);
        }

        return $result;
    }

    public function checkString($data, $string)
    {
        if (strpos($data, $string) !== false) {
            return true;
        }
    }

    public function cart()
    {
        try {
            $location = $this->getLocation();
            $country = \App\Http\Controllers\Front\CartController::findCountryByGeoip($location['iso_code']);
            $states = \App\Http\Controllers\Front\CartController::findStateByRegionId($location['iso_code']);
            $states = \App\Model\Common\State::pluck('state_subdivision_name', 'state_subdivision_code')->toArray();
            $state_code = $location['iso_code'].'-'.$location['state'];
            $state = \App\Http\Controllers\Front\CartController::getStateByCode($state_code);
            $mobile_code = \App\Http\Controllers\Front\CartController::getMobileCodeByIso($location['iso_code']);
            $cont = new \App\Http\Controllers\Front\CartController();
            $currency = $cont->currency();
            \Session::put('currency', $currency);
            if (!\Session::has('currency')) {
                \Session::put('currency', 'INR');
            }
            $pages = $this->page->find(1);
            $data = $pages->content;
            $product = new \App\Model\Product\Product();
            $groups = ProductGroup::get()->toArray();
            $template = [];
            $heading = [];
            $tagline = [];
            $trasform = [];
            if ($groups) {
                for ($i = 0; $i < count($groups); $i++) {
                    $products = $product->where('id', '!=', 1)
                ->where('group', '=', $groups[$i]['id'])
                ->where('hidden', '=', '0')
                ->orderBy('created_at', 'asc')
                ->get()
                ->toArray();
                    $heading1 = $groups[$i]['hidden'] == '0' ? $groups[$i]['headline'] : '';
                    $tagline1 = $groups[$i]['hidden'] == '0' ? $groups[$i]['tagline'] : '';
                    $template1 = ($this->getTemplateOne($products, $data, $trasform));

                    $templates = array_push($template, $template1);
                    $headings = array_push($heading, $heading1);
                    $taglines = array_push($tagline, $tagline1);
                }
            } else {
                $products = $product->where('id', '!=', 1)
                ->where('hidden', '=', '0')
                ->orderBy('created_at', 'asc')
                ->get()
                ->toArray();
                if ($products) {
                    $template1 = ($this->getTemplateOne($products, $data, $trasform));
                }
                $templates = array_push($template, $template1);
                $heading[0] = '';
                $tagline[0] = '';
            }

            return view('themes.default1.common.template.shoppingcart', compact('template1', 'heading', 'heading1', 'heading2', 'groups', 'template', 'heading', 'tagline'));
        } catch (\Exception $ex) {
            app('log')->error($ex->getMessage());
            Bugsnag::notifyException($ex);

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }
}
