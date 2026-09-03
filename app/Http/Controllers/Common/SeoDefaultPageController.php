<?php

declare(strict_types=1);

namespace App\Http\Controllers\Common;

use App\Facades\Attach;
use App\Http\Controllers\Controller;
use App\Http\Requests\Common\SeoDefaultPageRequest;
use App\Model\Common\SeoDefaultPage;
use App\Services\Seo\SeoFileGenerator;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class SeoDefaultPageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * List the fixed default-page SEO rows, in their stable display order.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $searchQuery = $request->input('search-query', '');
            $limit = $request->input('limit', 10);

            $rows = SeoDefaultPage::when($searchQuery, function ($query) use ($searchQuery): void {
                $query->where(function ($q) use ($searchQuery): void {
                    $q->where('page_key', 'like', sprintf('%%%s%%', $searchQuery))
                        ->orWhere('meta_title', 'like', sprintf('%%%s%%', $searchQuery))
                        ->orWhere('meta_description', 'like', sprintf('%%%s%%', $searchQuery));
                });
            })
                ->orderBy('id')
                ->paginate($limit);

            return successResponse('', $rows);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function show(string $pageKey): JsonResponse
    {
        try {
            $row = SeoDefaultPage::where('page_key', $pageKey)->first();

            if (! $row) {
                return errorResponse(__('message.no-record'), 404);
            }

            $data = $row->toArray();
            $data['og_image'] = $row->og_image ? Attach::getUrlPath('images/'.$row->og_image) : null;

            return successResponse('', $data);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    /**
     * Edit-only: page_key is fixed by the seeded rows, never writable here.
     */
    public function update(string $pageKey, SeoDefaultPageRequest $request): JsonResponse
    {
        try {
            $row = SeoDefaultPage::where('page_key', $pageKey)->first();

            if (! $row) {
                return errorResponse(__('message.no-record'), 404);
            }

            $data = $request->validated();
            unset($data['og_image']);
            if ($request->hasFile('og_image')) {
                $data['og_image'] = basename((string) Attach::put('images', $request->file('og_image'), null, true));
            }

            $row->fill($data)->save();

            try {
                app(SeoFileGenerator::class)->generateAll();
            } catch (Throwable $throwable) {
                report($throwable);
            }

            return successResponse(__('message.updated-successfully'), $row);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }
}
