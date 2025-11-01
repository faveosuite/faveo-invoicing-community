<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Model\Common\SocialMedia;
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

    public function getWidgetList(Request $request)
    {
        try {
            $searchString = $request->input('search-query', '');
            $sortOrder = $request->input('sort-order', 'desc');
            $sortField = $request->input('sort-field', 'created_at');
            $limit = $request->input('limit', 10);

            // Base query
            $widgets = $this->widget
                ->select('id', 'name', 'type', 'created_at', 'content')
                ->when($searchString, function ($query) use ($searchString) {
                    return $query->where(function ($q) use ($searchString) {
                        $q->where('name', 'like', "%{$searchString}%")
                            ->orWhere('type', 'like', "%{$searchString}%");
                    });
                })
                ->orderBy($sortField, $sortOrder)
                ->simplePaginate($limit);

            $total = $widgets->count();

            $widgets->getCollection()->transform(function ($widget) {
                return [
                    'id' => $widget->id,
                    'name' => ucfirst($widget->name),
                    'type' => $widget->type,
                    'created_at' => getDateHtml($widget->created_at),
                    'content' => $widget->content,
                    'action' => hyperLinkGenerator("widgets/show/{$widget->id}", __('message.edit')),
                ];
            });

            return successResponse(__('message.widget_fetched'), [
                'pages' => $widgets,
                'total' => $total,
            ]);
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function getWidget($id)
    {
        try {
            $widget = $this->widget
                ->select('id', 'name', 'type', 'publish', 'content', 'allow_mailchimp', 'allow_social_media')
                ->find($id);

            if (! $widget) {
                return errorResponse(__('message.no-record'), 404);
            }

            $mailchimpStatus = StatusSetting::pluck('mailchimp_status')->first();
            $twitterStatus = StatusSetting::pluck('twitter_status')->first();

            return successResponse(__('message.widget_fetched_successfully'),
                [
                    'widget' => $widget,
                    'mailchimpStatus' => $mailchimpStatus,
                    'twitterStatus' => $twitterStatus,
                ],
                200
            );
        } catch (\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    public function createWidget(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'publish' => 'required',
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
            $mailchimpTextBox = $this->widget->where('allow_mailchimp', 1)->count();
            $allowsocialIcon = $this->widget->where('allow_social_media', 1)->count();

            if ($mailchimpTextBox && $request->allow_mailchimp == 1) {
                return errorResponse(__('message.mailchimp_footer_error'));
            }

            if ($allowsocialIcon && $request->allow_social_media == 1) {
                return errorResponse(__('message.social_icon_footer_warning'));
            }

            $this->widget->fill($request->input());
            $this->widget->save();

            return successResponse(__('message.saved-successfully'), '', 201);
        } catch (\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    public function updateWidget($id, Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'publish' => 'required',
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
            $widget = $this->widget->find($id);
            if (! $widget) {
                return errorResponse(__('message.no-record'), 404);
            }

            $fillableData = $request->only([
                'name',
                'publish',
                'type',
                'allow_mailchimp',
                'allow_social_media',
                'content',
            ]);

            $mailchimpExists = $this->widget->where('allow_mailchimp', 1)
                ->where('id', '!=', $id)
                ->exists();

            $socialExists = $this->widget->where('allow_social_media', 1)
                ->where('id', '!=', $id)
                ->exists();

            if ($mailchimpExists && $request->allow_mailchimp == 1) {
                return errorResponse(__('message.mailchimp_footer_error'), 400);
            }

            if ($socialExists && $request->allow_social_media == 1) {
                return errorResponse(__('message.social_icon_footer_warning'), 400);
            }

            $widget->fill($fillableData);
            $widget->allow_tweets = 0;
            $widget->save();

            return successResponse(__('message.updated-successfully'), ['widgets' => $widget], 200);
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Response
     */
    public function deleteWidget(Request $request)
    {
        try {
            $ids = $request->input('select', []);

            if (! is_array($ids)) {
                $ids = explode(',', $ids);
            }

            // Clean IDs - remove empty values & convert to integer
            $ids = array_filter(array_map('intval', array_map('trim', $ids)));

            if (empty($ids)) {
                return errorResponse(__('message.select-a-row'), 400);
            }

            $existingIds = $this->widget->whereIn('id', $ids)->get();

            if ($existingIds->isEmpty()) {
                return errorResponse(__('message.no-record'), 404);
            }
            foreach ($existingIds as $exist) {
                $exist->delete();
            }

            return successResponse(__('message.deleted-successfully'));
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    /**
     * This function returns the rendered widget.
     *
     * @param
     * @param
     * @return \HTTP
     *
     * @throws
     */
    public function footer1()
    {
        $set = new \App\Model\Common\Setting();
        $set = $set->findOrFail(1);
        $social = SocialMedia::get();
        $footerWidgetTypes = ['footer1', 'footer2', 'footer3'];
        $isV2RecaptchaEnabledForNewsletter = 0;
        $data = [];
        foreach ($footerWidgetTypes as $widgetType) {
            $widget = \App\Model\Front\Widgets::where('publish', 1)->where('type', $widgetType)->select('name', 'content', 'allow_tweets', 'allow_mailchimp', 'allow_social_media')->first();
            $mailchimpKey = \App\Model\Common\Mailchimp\MailchimpSetting::value('api_key');

            if ($widget) {
                $data[$widgetType] = $this->renderWidget($widget, $set, $social, $mailchimpKey);
                $data1[$widgetType] = ['widget' => $widget, 'settings' => $set, 'socialMedia' => $social, 'mailchimpKey' => $mailchimpKey];
            }
        }

        return successResponse('success', $data);
    }

    /**
     * This function renders the footer widget.
     *
     * @param  $widget
     * @param  $set
     * @param  $social
     * @param  $mailchimpKey
     * @return string
     */
    public function renderWidget($widget, $set, $social, $mailchimpKey)
    {
        $tweetDetails = $widget->allow_tweets == 1 ? '<div id="tweets" class="twitter"></div>' : '';

        $socialMedia = '';
        $socialMedia1 = [];
        if ($widget->allow_social_media) {
            // Social Media Icons
            $socialMedia .= '<ul class="list list-unstyled">';
            if ($set->company_email) {
                $socialMedia1['email'] = $set->company_email;
                $socialMedia .= '<li class="d-flex align-items-center mb-4">
                                    <i class="fa-regular fa-envelope fa-xl"></i>&nbsp;&nbsp;
                                    <a href="mailto:'.$set->company_email.'" class="d-inline-flex align-items-center text-decoration-none text-color-grey text-color-hover-primary font-weight-semibold text-4-5">'.$set->company_email.'</a>
                                </li>';
            }
            if ($set->phone) {
                $socialMedia1['phone'] = $set->phone;
                $socialMedia .= '<li class="d-flex align-items-center mb-4">
                                    <i class="fas fa-phone text-4 p-relative top-2"></i>&nbsp;
                                    <a href="tel:'.$set->phone.'" class="d-inline-flex align-items-center text-decoration-none text-color-grey text-color-hover-primary font-weight-semibold text-4-5">+'.$set->phone_code.' '.$set->phone.'</a>
                                </li>';
            }
            $socialMedia .= '</ul>';

            // Social Icons
            $socialMedia .= '<ul class="social-icons social-icons-clean social-icons-medium">';
            foreach ($social as $media) {
                $socialMedia1['socialMediaName'] = $media->name;
                $socialMedia1['socialMediaUrl'] = $media->link;
                $socialMedia .= '<li class="social-icons-'.strtolower($media->name).'">
                                    <a href="'.$media->link.'" target="_blank" data-bs-toggle="tooltip" title="'.ucfirst($media->name).'">
                                        <i class="fab fa-'.strtolower($media->name).' text-color-grey-lighten"></i>
                                    </a>
                                </li>';
            }
            $socialMedia .= '</ul>';
        }

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
                                                            <label>'.__('message.contact_leave').'</label>
                                                            <input type="text" name="mailhoneypot_field" value="">
                                                        </div>';
            $mailchimpSection .= '
                    <div class="row">
                        <div class="form-group col">
                            <div id="mailchimp_recaptcha"></div>
                        </div>
                    </div>';
            $mailchimpSection .= '<button class="btn btn-primary mb-3" id="mailchimp-subscription" type="submit"><strong>'.__('message.caps_go').'</strong></button>
                                            </form>
                                          </div>';
        }

        // Check if the 'menu' class exists in the widget content
        $hasMenuClass = strpos($widget->content, 'menu') !== false;

        // Add class if 'menu' class exists in the widget content
        if ($hasMenuClass) {
            $socialMedia1['widgetContent'] = $widget->content;
            $widget->content = str_replace('<ul', '<ul class="list list-styled columns-lg-2 px-2"', $widget->content);
        }
        $socialMedia1['tweetDetails'] = $tweetDetails;

//        return $socialMedia1;
        return '<div class="col-lg-4">
                    <div class="widget-container">
                        <h4 class="text-color-dark font-weight-bold mb-3">'.$widget->name.'</h4>
                        <div class="widget-content">
                            <p class="text-3-5 font-weight-medium pe-lg-2">'.$widget->content.'</p>
                            '.$tweetDetails.'
                            '.($widget->allow_social_media ? $socialMedia : '').'
                        </div>
                        '.$mailchimpSection.'
                    </div>
                </div>';
    }
}
