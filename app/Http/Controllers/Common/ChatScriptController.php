<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Model\Common\ChatScript;
use Exception;
use Illuminate\Http\Request;
use Log;
use Logger;

class ChatScriptController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');

        $script = new ChatScript();
        $this->script = $script; // @phpstan-ignore property.notFound
    }

    public function getScriptList(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $searchString = $request->input('search-query', '');
            $sortOrder = $request->input('sort-order', 'desc');
            $sortField = $request->input('sort-field', 'created_at');
            $limit = $request->input('limit', 10);

            $scripts = $this->script // @phpstan-ignore property.notFound
                ->select('id', 'name')
                ->when($searchString, function ($query) use ($searchString): void {
                    $query->where('name', 'like', sprintf('%%%s%%', $searchString));
                })
                ->orderBy($sortField, $sortOrder)
                ->simplePaginate($limit);

            $scripts->getCollection()->transform(fn ($script): array => [
                'id' => $script->id,
                'name' => $script->name,
                'checkbox' => $script->id,
                'action' => hyperLinkGenerator('chat/show/'.$script->id, __('message.edit')),
            ]);

            return successResponse(__('message.scripts_fetched'), $scripts);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function createScript(Request $request): \Illuminate\Http\JsonResponse
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

            $this->script->fill($request->all())->save(); // @phpstan-ignore property.notFound

            return successResponse(__('message.saved-successfully'));
        } catch (Exception $exception) {
            Logger::exception($exception);

            return errorResponse($exception->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     */
    public function getScript($id): \Illuminate\Http\JsonResponse
    {
        try {
            $chat = $this->script->find($id); // @phpstan-ignore property.notFound

            if (! $chat) {
                return errorResponse(__('message.no-record'), 404);
            }

            return successResponse(__('message.chat_fetched'), $chat);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     */
    public function updateScript(Request $request, $id): \Illuminate\Http\JsonResponse
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
            $script = $this->script->find($id); // @phpstan-ignore property.notFound

            if (! $script) {
                return errorResponse(__('message.record_not_found'), 404);
            }

            // Set on_every_page value
            $script->on_every_page = $request->on_registration ? 0 : 1;

            $script->fill($request->all());
            $script->save();

            return successResponse(__('message.updated-successfully'), $script);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());

            return errorResponse($exception->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function deleteScript(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $ids = $request->input('select', []);

            $ids = array_filter(array_unique(array_map(intval(...), array_map(trim(...), $ids))));

            if ($ids === []) {
                return errorResponse(__('message.select-a-row'), 400);
            }

            $scriptIds = $this->script->whereIn('id', $ids)->get(); // @phpstan-ignore property.notFound

            if ($scriptIds->isEmpty()) {
                return errorResponse(__('message.no-record'), 404);
            }

            foreach ($scriptIds as $script) {
                $script->delete();
            }

            $this->script->whereIn('id', $ids)->delete(); // @phpstan-ignore property.notFound

            return successResponse(__('message.deleted-successfully'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage(), 500);
        }
    }
}
