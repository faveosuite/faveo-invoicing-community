<?php

namespace App\Http\Controllers;

use App\ThirdPartyApp;
use Illuminate\Http\Request;

class ThirdPartyAppController extends Controller
{
    private $thirdParty;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');

        $thirdParty = new ThirdPartyApp();
        $this->thirdParty = $thirdParty;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('themes.default1.third-party.index');
    }

    /*
    * Get All the third party apps
    */
    public function getThirdPartyDetails()
    {
        try {
            $thirdPartyApps = $this->thirdParty->select('id', 'app_name', 'app_key', 'app_secret');

            return \DataTables::of($thirdPartyApps)
             ->orderColumn('app_name', '-created_at $1')
             ->orderColumn('app_key', '-created_at $1')
             ->orderColumn('app_secret', '-created_at $1')
            ->addColumn('checkbox', function ($model) {
                return "<input type='checkbox' class='type_checkbox' 
            value=".$model->id.' name=select[] id=check>';
            })
            ->addColumn('app_name', function ($model) {
                return $model->app_name;
            })
            ->addColumn('app_key', function ($model) {
                return $model->app_key;
            })
             ->addColumn('app_secret', function ($model) {
                 return $model->app_secret;
             })
            ->addColumn('action', function ($model) {
                return "<p><button data-toggle='modal' 
             data-id=".$model->id." data-appName='$model->app_name'. data-appKey='$model->app_key'. data-secret='$model->app_secret' class='btn btn-sm btn-secondary btn-xs editThirdPartyApp'".tooltip('Edit')."<i class='fa fa-edit'
             style='color:white;'> </i></button>&nbsp;</p>";
            })
             ->filterColumn('app_name', function ($query, $keyword) {
                 $sql = 'app_name like ?';
                 $query->whereRaw($sql, ["%{$keyword}%"]);
             })
             ->filterColumn('app_key', function ($query, $keyword) {
                 $sql = 'app_key like ?';
                 $query->whereRaw($sql, ["%{$keyword}%"]);
             })
            ->filterColumn('app_secret', function ($query, $keyword) {
                $sql = 'app_secret like ?';
                $query->whereRaw($sql, ["%{$keyword}%"]);
            })
                ->editColumn('app_secret', function ($user) {
                    return '*****';
                })
            ->rawColumns(['checkbox', 'app_name', 'app_key', 'app_secret', 'action'])
            ->make(true);
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'app_name' => 'required',
            'app_key' => 'required|size:32',
            'app_secret' => 'required',
        ]);
        $this->thirdParty->fill($request->all())->save();

        return redirect()->back()->with('success', \Lang::get('message.saved-successfully'));
    }

    public function getAppKey()
    {
        try {
            $code = str_random(32);
            echo $code;
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ThirdPartyApp  $thirdPartyApp
     * @return \Illuminate\Http\Response
     */
    public function show(ThirdPartyApp $thirdPartyApp)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ThirdPartyApp  $thirdPartyApp
     * @return \Illuminate\Http\Response
     */
    public function edit(ThirdPartyApp $thirdPartyApp)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ThirdPartyApp  $thirdPartyApp
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ThirdPartyApp $thirdPartyApp, $id)
    {
        $this->validate($request, [
            'app_name' => 'required',
            'app_key' => 'required|size:32',
            'app_secret' => 'required',
        ]);
        $app_name = $request->input('app_name');
        $app_key = $request->input('app_key');
        $app_secret = $request->input('app_secret');
        $this->thirdParty->where('id', $id)->update(['app_name' => $app_name, 'app_key' => $app_key, 'app_secret' => $app_secret]);

        return redirect()->back()->with('success', \Lang::get('message.updated-successfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ThirdPartyApp  $thirdPartyApp
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        try {
            $ids = $request->input('select');
            if (! empty($ids)) {
                foreach ($ids as $id) {
                    $app = $this->thirdParty->where('id', $id)->first();
                    if ($app) {
                        $app->delete();
                    } else {
                        echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */\Lang::get('message.failed').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */\Lang::get('message.no-record').'
                </div>';
                        //echo \Lang::get('message.no-record') . '  [id=>' . $id . ']';
                    }
                }
                echo "<div class='alert alert-success alert-dismissable'>
                    <i class='fa fa-ban'></i>

                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */\Lang::get('message.success').'

                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */\Lang::get('message.deleted-successfully').'
                </div>';
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
                    /* @scrutinizer ignore-type */
                    \Lang::get('message.failed').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        '.$e->getMessage().'
                </div>';
        }
    }
}
