<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Model\Common\StatusSetting;
use App\Model\Front\Widgets;
use Illuminate\Http\Request;

class WidgetController extends Controller
{
    public $widget;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');

        $widget = new Widgets();
        $this->widget = $widget;
    }

    public function index()
    {
        try {
            return view('themes.default1.front.widgets.index');
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function getPages()
    {
        return \DataTables::of($this->widget->select('id', 'name', 'type', 'created_at', 'content'))
                       ->orderColumn('name', '-created_at $1')
                       ->orderColumn('type', '-created_at $1')
                       ->orderColumn('created_at', '-created_at $1')
                       ->addColumn('checkbox', function ($model) {
                           return "<input type='checkbox' class='widget_checkbox' 
                            value=".$model->id.' name=select[] id=check>';
                       })
                          ->addColumn('name', function ($model) {
                              return ucfirst($model->name);
                          })
                            ->addColumn('type', function ($model) {
                                return $model->type;
                            })
                              ->addColumn('created_at', function ($model) {
                                  return getDateHtml($model->created_at);
                              })
                        // ->showColumns('name', 'type', 'created_at')
                        ->addColumn('content', function ($model) {
                            return str_limit($model->content, 10, '...');
                        })
                        ->addColumn('action', function ($model) {
                            return '<a href='.url('widgets/'.$model->id.'/edit')."
                             class='btn btn-sm btn-secondary btn-xs'".tooltip(__('message.edit'))."<i class='fa fa-edit'
                                 style='color:white;'> </i></a>";
                        })
                         ->filterColumn('name', function ($query, $keyword) {
                             $sql = 'name like ?';
                             $query->whereRaw($sql, ["%{$keyword}%"]);
                         })
                        ->filterColumn('type', function ($query, $keyword) {
                            $sql = 'type like ?';
                            $query->whereRaw($sql, ["%{$keyword}%"]);
                        })
                        ->rawColumns(['checkbox', 'name', 'type', 'created_at', 'content', 'action'])
                        ->make(true);
        // ->searchColumns('name', 'content')
        // ->orderColumns('name')
        // ->make();
    }

    public function create()
    {
        try {
            $mailchimpStatus = StatusSetting::pluck('mailchimp_status')->first();
            $twitterStatus = StatusSetting::pluck('twitter_status')->first();

            return view('themes.default1.front.widgets.create', compact('mailchimpStatus', 'twitterStatus'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $mailchimpStatus = StatusSetting::pluck('mailchimp_status')->first();
            $twitterStatus = StatusSetting::pluck('twitter_status')->first();
            $widget = $this->widget->where('id', $id)->first();

            //dd($widget);
            return view('themes.default1.front.widgets.edit', compact('widget', 'mailchimpStatus', 'twitterStatus'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'publish' => 'required',
            // 'content' => 'required',
            'type' => 'required|unique:widgets',
        ],
            [
                'name.required' => __('validation.widget.name_required'),
                'name.max' => __('validation.widget.name_max'),
                'publish.required' => __('validation.widget.publish_required'),
                'type.required' => __('validation.widget.type_required'),
                'type.unique' => __('validation.widget.type_unique'),
            ]);

        try {
            $mailchimpTextBox = Widgets::where('allow_mailchimp', 1)->count();
            $allowsocialIcon = Widgets::where('allow_social_media', 1)->count();
            if ($mailchimpTextBox && $request->allow_mailchimp == 1) {
                throw new \Exception(__('message.mailchimp_footer_error'));
            }
            if ($allowsocialIcon && $request->allow_social_media == 1) {
                throw new \Exception(__('message.social_icon_footer_warning'));
            }
            $this->widget->fill($request->input())->save();

            return redirect()->back()->with('success', \Lang::get('message.saved-successfully'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function update($id, Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'publish' => 'required',
            // 'content' => 'required',
            'type' => 'required|unique:widgets,type,'.$id,
        ],
            [
                'name.required' => __('validation.widget.name_required'),
                'name.max' => __('validation.widget.name_max'),
                'publish.required' => __('validation.widget.publish_required'),
                'type.required' => __('validation.widget.type_required'),
                'type.unique' => __('validation.widget.type_unique'),
            ]);

        try {
            $mailchimpTextBox = Widgets::where('allow_mailchimp', 1)->where('id', '!=', $id)->count();
            $allowsocialIcon = Widgets::where('allow_social_media', 1)->where('id', '!=', $id)->count();
            if ($mailchimpTextBox && $request->input('allow_mailchimp')) {
                throw new \Exception(__('message.mailchimp_footer_error'));
            }
            if ($allowsocialIcon && $request->allow_social_media == 1) {
                throw new \Exception(__('message.social_icon_footer_warning'));
            }
            $widget = $this->widget->where('id', $id)->first();
            $widget->fill($request->input());
            $widget->allow_tweets = 0;
            $widget->save();  // Keeping allow_tweets set to 0 ensures that Twitter integration is disabled. If there's a future need to enable tweet fetching, it can be set to 1, and vice versa.

            return redirect()->back()->with('success', \Lang::get('message.updated-successfully'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Response
     */
    public function destroy(Request $request)
    {
        try {
            $ids = $request->input('select');
            if (! empty($ids)) {
                foreach ($ids as $id) {
                    $widget = $this->widget->where('id', $id)->first();
                    if ($widget) {
                        // dd($page);
                        $widget->delete();
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
                    /* @scrutinizer ignore-type */ \Lang::get('message.success').'
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
                    /* @scrutinizer ignore-type */\Lang::get('message.failed').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        '.$e->getMessage().'
                </div>';
        }
    }
}
