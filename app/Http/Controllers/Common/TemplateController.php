<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Model\Common\Template;
use App\Model\Common\TemplateType;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    /**
     * @var Template
     */
    public $template;

    /**
     * @var TemplateType
     */
    public $type;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');

        $template = new Template;
        $this->template = $template;

        $type = new TemplateType;
        $this->type = $type;
    }

    public function getTemplates(Request $request): JsonResponse
    {
        try {
            $search = $request->input('search-query', '');
            $sortField = $request->input('sort-field', 'name');
            $sortOrder = $request->input('sort-order', 'asc');
            $limit = (int) $request->input('limit', 10);

            $allowedSort = ['name', 'id'];
            if (! in_array($sortField, $allowedSort)) {
                $sortField = 'name';
            }

            $typeNames = TemplateType::pluck('name', 'id');

            $paginated = $this->template
                ->select('id', 'name', 'type')
                ->when($search, fn ($q) => $q->where('name', 'like', sprintf('%%%s%%', $search)))
                ->orderBy($sortField, $sortOrder === 'desc' ? 'desc' : 'asc')
                ->paginate($limit);

            $paginated->getCollection()->transform(fn ($t): array => [
                'id' => $t->id,
                'name' => $t->name,
                'type' => $typeNames[$t->type] ?? '',
            ]);

            return successResponse('', $paginated);
        } catch (Exception) {
            return errorResponse(__('message.something_went_wrong_fetch_templates'));
        }
    }

    public function showTemplate(int $id): JsonResponse
    {
        try {
            $shortcodes = config('transform');
            $tooltips = config('shortcodes');

            $template = $this->template->find($id);

            if (! $template) {
                return errorResponse(__('message.template_not_found'));
            }

            $type = $this->type->pluck('name', 'id')->toArray();
            /** @var TemplateType|null $templateType */
            $templateType = TemplateType::find($template->type);
            $shortcodeName = $templateType ? $templateType->name : null;
            $codes = null;
            if ($shortcodeName && array_key_exists($shortcodeName, $shortcodes)) {
                $codes = $shortcodes[$shortcodeName];
            }

            $templateIdData = [
                'type' => $type,
                'template' => $template,
                'codes' => $codes,
                'tooltips' => $tooltips,
            ];

            return successResponse(__('message.templates_fetched_successfully'), $templateIdData);
        } catch (Exception) {
            return errorResponse(__('message.something_went_wrong_fetch_particular_template'));
        }
    }

    public function updateTemplate(int $id, Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required'],
            'data' => ['required'],
            'type' => ['required'],
        ], [
            'name.required' => __('validation.auth_controller.name_required'),
            'data.required' => __('message.content_required'),
            'type.required' => __('message.template_type_required'),
        ]);
        try {
            $template = $this->template->find($id);
            if (! $template) {
                return errorResponse(__('message.template_not_found'));
            }

            $template->fill($request->all())->save();

            return successResponse(__('message.template_update_success'), $template);
        } catch (Exception) {
            return errorResponse(__('message.template_update_error'));
        }
    }

}
