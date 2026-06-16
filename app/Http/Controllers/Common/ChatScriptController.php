<?php

namespace App\Http\Controllers\Common;

use Exception;
use Logger;
use Log;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Model\Common\ChatScript;
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

    public function getScriptList(Request $request)
    {
        try {
            $searchString = $request->input('search-query', '');
            $sortOrder = $request->input('sort-order', 'desc');
            $sortField = $request->input('sort-field', 'created_at');
            $limit = $request->input('limit', 10);

            $scripts = $this->script
                ->select('id', 'name')
                ->when($searchString, function ($query) use ($searchString): void {
                    $query->where('name', 'like', "%{$searchString}%");
                })
                ->orderBy($sortField, $sortOrder)
                ->simplePaginate($limit);

            $scripts->getCollection()->transform(fn($script) => [
                'id' => $script->id,
                'name' => $script->name,
                'checkbox' => $script->id,
                'action' => hyperLinkGenerator("chat/show/{$script->id}", __('message.edit')),
            ]);

            return successResponse(__('message.scripts_fetched'), $scripts);
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function createScript(Request $request)
    {
        $request->validate([
            'name' => ['required', 'max:50'],
            'script' => ['required'],
            'google_analytics_tag' => ['required_if:google_analytics,1'],
        ], [
            'name.required' => __('validation.widget.name_required'),
            'script.required' => __('message.script_required'),
            'google_analytics_tag.required_if' => __('message.google_analytics_tag_required_if'),
        ]);
        try {
            $request['on_every_page'] = $request->on_registration ? 0 : 1;

            $this->script->fill($request->all())->save();

            return successResponse(__('message.saved-successfully'));
        } catch (Exception $ex) {
            Logger::exception($ex);

            return errorResponse($ex->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function getScript($id)
    {
        try {
            $chat = $this->script->find($id);

            if (! $chat) {
                return errorResponse(__('message.no-record'), 404);
            }

            return successResponse(__('message.chat_fetched'), $chat);
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  int  $id
     * @return Response
     */
    public function updateScript(Request $request, $id)
    {
        $request->validate([
            'name' => ['required', 'max:50'],
            'script' => ['required'],
            'google_analytics_tag' => ['required_if:google_analytics,1'],
        ], [
            'script.required' => __('message.script_required'),
            'google_analytics_tag.required_if' => __('message.google_analytics_tag_required_if'),
        ]);

        try {
            $script = $this->script->find($id);

            if (! $script) {
                return errorResponse(__('message.record_not_found'), 404);
            }

            // Set on_every_page value
            $script->on_every_page = $request->on_registration ? 0 : 1;

            $script->fill($request->all());
            $script->save();

            return successResponse(__('message.updated-successfully'), $script);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());

            return errorResponse($ex->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function deleteScript(Request $request)
    {
        try {
            $ids = $request->input('select', []);

//                if (!is_array($ids)) {
//                    $ids = explode(',', $ids);
//                }

            $ids = array_filter(array_unique(array_map(intval(...), array_map(trim(...), $ids))));

            if (empty($ids)) {
                return errorResponse(__('message.select-a-row'), 400);
            }

            $scriptIds = $this->script->whereIn('id', $ids)->get();

            if ($scriptIds->isEmpty()) {
                return errorResponse(__('message.no-record'), 404);
            }

            foreach ($scriptIds as $script) {
                $script->delete();
            }

            $this->script->whereIn('id', $ids)->delete();

            return successResponse(__('message.deleted-successfully'));
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage(), 500);
        }
    }
}
