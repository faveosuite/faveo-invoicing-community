<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Model\Common\ChatScript;
use Bugsnag;
use Illuminate\Http\Request;

class ChatScriptController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');

        $script = new ChatScript();
        $this->script = $script;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('themes.default1.common.chat.index');
    }

    public function getScript()
    {
        try {
            return \DataTables::of($this->script->select('id', 'name'))
                        ->addColumn('checkbox', function ($model) {
                            return "<input type='checkbox' class='chat_checkbox' 
                            value=".$model->id.' name=select[] id=check>';
                        })
                        ->orderColumn('name', '-created_at $1')
                         ->addColumn('name', function ($model) {
                             return $model->name;
                         })

                        ->addColumn('action', function ($model) {
                            return '<a href='.url('chat/'.$model->id.'/edit').
                            " class='btn btn-sm btn-secondary btn-xs'".tooltip( __('message.edit'))."<i class='fa fa-edit'
                                 style='color:white;'> </i></a>";
                        })
                             ->filterColumn('name', function ($query, $keyword) {
                                 $sql = 'name like ?';
                                 $query->whereRaw($sql, ["%{$keyword}%"]);
                             })
                        ->rawColumns(['checkbox', 'name',  'action'])
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
        return view('themes.default1.common.chat.create');
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
            'name' => 'required|max:50',
            'script' => 'required',
            'google_analytics_tag' => 'required_if:google_analytics,1',

        ], [
            'script.required' => __('message.script_required'),
            'google_analytics_tag.required_if' => __('message.google_analytics_tag_required_if'),
        ]);

        try {
            $request['on_every_page'] = $request->on_registration ? 0 : 1;
            $this->script->fill($request->input())->save();

            return redirect()->back()->with('success', \Lang::get('message.saved-successfully'));
        } catch (Exception $ex) {
            Bugsnag::notifyException($ex);
            app('log')->error($ex->getMessage());

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $chat = $this->script->where('id', $id)->first();

        return view('themes.default1.common.chat.edit', compact('chat'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'script' => 'required',
            'google_analytics_tag' => 'required_if:google_analytics,1',

        ], [
            'script.required' => __('message.script_required'),
            'google_analytics_tag.required_if' => __('message.google_analytics_tag_required_if'),
        ]);

        try {
            $script = $this->script->where('id', $id)->first();
            $script->on_every_page = $request->on_registration ? 0 : 1;
            $script->fill($request->input())->save();

            return redirect()->back()->with('success', \Lang::get('message.updated-successfully'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        try {
            $ids = $request->input('select');
            if (! empty($ids)) {
                foreach ($ids as $id) {
                    $script = $this->script->where('id', $id)->first();
                    if ($script) {
                        $script->delete();
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
                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').
                    '!</b> './* @scrutinizer ignore-type */\Lang::get('message.success').'
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
}
