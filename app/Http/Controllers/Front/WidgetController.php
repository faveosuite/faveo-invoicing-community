<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Model\Common\StatusSetting;
use App\Model\Common\SocialMedia;
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
            $widgetsCount = Widgets::count();

            return view('themes.default1.front.widgets.index', compact('widgetsCount'));
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


    /**
     * This function returns the rendered widget.
     *
     * @param
     * @param
     * @return \HTTP
     * @throws
     */
    public function footer1(){

        $set = new \App\Model\Common\Setting();
        $set = $set->findOrFail(1);
        $social = SocialMedia::get();
        $footerWidgetTypes = ['footer1','footer2','footer3'];
        $isV2RecaptchaEnabledForNewsletter=0;
        foreach ($footerWidgetTypes as $widgetType) {
            $widget = \App\Model\Front\Widgets::where('publish', 1)->where('type', $widgetType)->select('name', 'content', 'allow_tweets', 'allow_mailchimp', 'allow_social_media')->first();
            $mailchimpKey = \App\Model\Common\Mailchimp\MailchimpSetting::value('api_key');

            if ($widget) {
                $data[$widgetType]=$this->renderWidget($widget, $set, $social, $mailchimpKey);
            }
        }
        return successResponse('success', $data);
    }

    /**
     * This function renders the footer widget.
     *
     * @param $widget
     * @param $set
     * @param $social
     * @param $mailchimpKey
     * @return string
     */
    function renderWidget($widget, $set, $social, $mailchimpKey)
    {
        $tweetDetails = $widget->allow_tweets == 1 ? '<div id="tweets" class="twitter"></div>' : '';

        $socialMedia = '';
        if ($widget->allow_social_media) {
            // Social Media Icons
            $socialMedia .= '<ul class="list list-unstyled">';
            if ($set->company_email) {
                $socialMedia .= '<li class="d-flex align-items-center mb-4">
                                    <i class="fa-regular fa-envelope fa-xl"></i>&nbsp;&nbsp;
                                    <a href="mailto:' . $set->company_email . '" class="d-inline-flex align-items-center text-decoration-none text-color-grey text-color-hover-primary font-weight-semibold text-4-5">' . $set->company_email . '</a>
                                </li>';
            }
            if ($set->phone) {
                $socialMedia .= '<li class="d-flex align-items-center mb-4">
                                    <i class="fas fa-phone text-4 p-relative top-2"></i>&nbsp;
                                    <a href="tel:' . $set->phone . '" class="d-inline-flex align-items-center text-decoration-none text-color-grey text-color-hover-primary font-weight-semibold text-4-5">+' . $set->phone_code . ' ' . $set->phone . '</a>
                                </li>';
            }
            $socialMedia .= '</ul>';

            // Social Icons
            $socialMedia .= '<ul class="social-icons social-icons-clean social-icons-medium">';
            foreach ($social as $media) {
                $socialMedia .= '<li class="social-icons-' . strtolower($media->name) . '">
                                    <a href="' . $media->link . '" target="_blank" data-bs-toggle="tooltip" title="' . ucfirst($media->name) . '">
                                        <i class="fab fa-' . strtolower($media->name) . ' text-color-grey-lighten"></i>
                                    </a>
                                </li>';
            }
            $socialMedia .= '</ul>';
        }

        $status =  StatusSetting::select('recaptcha_status','v3_recaptcha_status', 'msg91_status', 'emailverification_status', 'terms')->first();

        $mailchimpSection = '';
        if ($mailchimpKey !== null && $widget->allow_mailchimp == 1) {
            $mailchimpSection .= '<div id="mailchimp-message" style="width: 86%;"></div>
                                                <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center">
                                                    <form id="newsletterForm" class="form-style-3 w-100">
                                                        <div class="input-group mb-3">
                                                            <input class="custom-input newsletterEmail" placeholder="Email Address" name="newsletterEmail" id="newsletterEmail" type="email">
                                                        </div>
                                                        <!-- Honeypot fields (hidden) -->
                                                        <div class="mb-3" style="display: none;">
                                                            <label>Leave this field empty</label>
                                                            <input type="text" name="mailhoneypot_field" value="">
                                                        </div>';
            if ($status->recaptcha_status === 1 || $status->v3_recaptcha_status === 1) {

                if ($status->recaptcha_status === 1) {
                    $mailchimpSection .= '
            <div class="mb-3">
                <div id="mailchimp_recaptcha"></div>
                <div class="robot-verification mb-3" id="mailchimpcaptcha"></div>
                <span id="mailchimpcaptchacheck"></span>
            </div>
        ';
                } elseif ($status->v3_recaptcha_status === 1) {
                    $mailchimpSection .= '
                <input type="hidden" id="g-recaptcha-mailchimp" class="g-recaptcha-token" name="g-recaptcha-response">
        ';
                }
            }
            $mailchimpSection .= '<button class="btn btn-primary mb-3" id="mailchimp-subscription" type="submit"><strong>GO!</strong></button>
                                            </form>
                                          </div>';
        }

        // Check if the 'menu' class exists in the widget content
        $hasMenuClass = strpos($widget->content, 'menu') !== false;

        // Add class if 'menu' class exists in the widget content
        if ($hasMenuClass) {
            $widget->content = str_replace('<ul', '<ul class="list list-styled columns-lg-2 px-2"', $widget->content);
        }

        return '<div class="col-lg-4">
                    <div class="widget-container">
                        <h4 class="text-color-dark font-weight-bold mb-3">' . $widget->name . '</h4>
                        <div class="widget-content">
                            <p class="text-3-5 font-weight-medium pe-lg-2">' . $widget->content . '</p>
                            ' . $tweetDetails . '
                            ' . ($widget->allow_social_media ? $socialMedia : '') . '
                        </div>
                        ' . $mailchimpSection . '
                    </div>
                </div>';
    }

}
