<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Model\Common\Mailchimp\MailchimpSetting;
use App\Model\Common\Setting;
use App\Model\Common\SocialMedia;
use App\Model\Common\StatusSetting;
use App\Model\Front\Widgets;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WidgetController extends Controller
{
    /**
     * @var Widgets
     */
    public $widget;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');

        $widget = new Widgets;
        $this->widget = $widget;
    }

    public function getWidgetList(Request $request): JsonResponse
    {
        try {
            $searchString = $request->input('search-query', '');
            $sortOrder = $request->input('sort-order', 'desc');
            $sortField = $request->input('sort-field', 'created_at');
            $limit = $request->input('limit', 10);

            // Base query
            $widgets = $this->widget
                ->select('id', 'name', 'type', 'created_at', 'content')
                ->when($searchString, fn ($query) => $query->where(function ($q) use ($searchString): void {
                    $q->where('name', 'like', sprintf('%%%s%%', $searchString))
                        ->orWhere('type', 'like', sprintf('%%%s%%', $searchString));
                }))
                ->orderBy($sortField, $sortOrder)
                ->simplePaginate($limit);

            $total = $widgets->count();

            $widgets->getCollection()->transform(fn ($widget): array => [
                'id' => $widget->id,
                'name' => ucfirst((string) $widget->name),
                'type' => $widget->type,
                'created_at' => getDateHtml($widget->created_at),
                'content' => $widget->content,
                'action' => hyperLinkGenerator('widgets/show/'.$widget->id),
            ]);

            return successResponse(__('message.widget_fetched'), [
                'pages' => $widgets,
                'total' => $total,
            ]);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function getWidget(mixed $id): JsonResponse
    {
        try {
            $widget = $this->widget
                ->select('id', 'name', 'type', 'publish', 'content', 'allow_mailchimp', 'allow_social_media')
                ->find($id);

            if (! $widget) {
                return errorResponse(__('message.no-record'), 404);
            }

            $mailchimpStatus = StatusSetting::value('mailchimp_status');
            $twitterStatus = StatusSetting::value('twitter_status');

            return successResponse(__('message.widget_fetched_successfully'),
                [
                    'widget' => $widget,
                    'mailchimpStatus' => $mailchimpStatus,
                    'twitterStatus' => $twitterStatus,
                ],
                200
            );
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function createWidget(Request $request): JsonResponse
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
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function updateWidget(string $id, Request $request): JsonResponse
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
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function deleteWidget(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('select', []);

            if (! is_array($ids)) {
                $ids = explode(',', (string) $ids);
            }

            // Clean IDs - remove empty values & convert to integer
            $ids = array_filter(array_map(intval(...), array_map(trim(...), $ids)));

            if ($ids === []) {
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
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

}
