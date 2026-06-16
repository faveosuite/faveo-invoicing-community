<?php

namespace App\Http\Controllers\Common;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Common\SocialMediaRequest;
use App\Model\Common\SocialMedia;
use Exception;
use Illuminate\Http\Request;

class SocialMediaController extends Controller
{
    protected $social;

    public function __construct()
    {
        $this->middleware('auth', ['except' => 'getTweets']);
        $this->middleware('admin', ['except' => 'getTweets']);

        $social = new SocialMedia();
        $this->social = $social;
    }

    /**
     * Get Social Media List.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function getSocialList(Request $request)
    {
        try {
            // Filters & pagination inputs
            $searchString = $request->input('search-query', '');
            $sortOrder = $request->input('sort-order', 'desc');
            $sortField = $request->input('sort-field', 'created_at');
            $limit = $request->input('limit', 10);

            $socials = $this->social
                ->select('id', 'name', 'link')
                ->when($searchString, function ($query) use ($searchString): void {
                    $query->where(function ($q) use ($searchString): void {
                        $q->where('name', 'like', "%{$searchString}%");
                    });
                })
                ->orderBy($sortField, $sortOrder)
                ->simplePaginate($limit);

            $socials->getCollection()->transform(fn($social) => [
                'id' => $social->id,
                'name' => ucfirst((string) $social->name),
                'link' => $social->link,
                'action' => hyperLinkGenerator("social-media/show/{$social->id}", __('message.edit')),
            ]);

            return successResponse(__('message.social_media_fetched'), $socials);
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    /**
     * Store a newly created social media account in storage.
     *
     * @param SocialMediaRequest $request
     * @return JsonResponse
     */
    public function createSocialMedia(SocialMediaRequest $request)
    {
        try {
            $social = $this->social->fill($request->validated());
            $social->save();

            return successResponse(__('message.saved-successfully'), $social);
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    /**
     * Display the specified social media account.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function getSocialMedia($id)
    {
        try {
            $social = $this->social->find($id);

            if (! $social) {
                return errorResponse(__('message.no-record'), 404);
            }

            return successResponse(__('message.social_media_fetched'), $social, 200);
        } catch (Exception $ex) {
            return $this->errorResponse($ex->getMessage());
        }
    }

    /**
     * Update the specified social media account in storage.
     *
     * @param Request $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function updateSocial($id, SocialMediaRequest $request)
    {
        try {
            $social = $this->social->find($id);

            if (is_null($social)) {
                return errorResponse(__('message.no-record'), 404);
            }

            $social->fill($request->validated())->save();

            return successResponse(__('message.updated-successfully'));
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Response
     */
    public function deleteSocialMedia(Request $request)
    {
        try {
            $ids = $request->input('select', []);

            if (empty($ids)) {
                return errorResponse(__('message.select-a-row'), 400);
            }

            $socials = $this->social->whereIn('id', $ids)->get();

            if ($socials->isEmpty()) {
                return errorResponse(__('message.no-record'), 404);
            }

            foreach ($socials as $social) {
                $social->delete();
            }

            return successResponse(__('message.deleted-successfully'));
        } catch (Exception $e) {
            return errorResponse($e->getMessage());
        }
    }
}
