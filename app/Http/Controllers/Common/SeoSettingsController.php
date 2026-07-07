<?php

declare(strict_types=1);

namespace App\Http\Controllers\Common;

use App\Facades\Attach;
use App\Http\Controllers\Controller;
use App\Model\Common\CommonSettings;
use App\Model\Common\Setting;
use App\Services\Seo\SeoFileGenerator;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * Site-wide SEO settings (option_name='seo' rows in common_settings):
 * default title/description templates for Pages-module pages and Product
 * Groups, plus their fallback Open Graph images. See SeoTemplateFormatter.
 */
class SeoSettingsController extends Controller
{
    /**
     * @var list<string>
     */
    private const TEXT_FIELDS = [
        'general_description',
        'general_og_title',
        'general_og_description',
        'pages_title_format',
        'groups_title_format',
        'pages_description_format',
        'groups_description_format',
        'pages_og_title_format',
        'groups_og_title_format',
        'pages_og_description_format',
        'groups_og_description_format',
    ];

    /**
     * @var list<string>
     */
    private const IMAGE_FIELDS = ['general_og_image', 'pages_og_image', 'groups_og_image'];

    /**
     * @var list<string>
     */
    private const BOOLEAN_FIELDS = ['general_og_same_as_meta', 'pages_og_same_as_meta', 'groups_og_same_as_meta'];

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function show(): JsonResponse
    {
        try {
            $settings = CommonSettings::where('option_name', 'seo')->pluck('option_value', 'optional_field');

            $data = [];
            foreach (self::TEXT_FIELDS as $field) {
                $data[$field] = $settings->get($field, '');
            }
            foreach (self::IMAGE_FIELDS as $field) {
                $filename = $settings->get($field);
                $data[$field] = $filename ? Attach::getUrlPath('images/'.$filename) : null;
            }
            foreach (self::BOOLEAN_FIELDS as $field) {
                $data[$field] = $settings->get($field) === '1';
            }
            $set = Setting::find(1);
            $data['favicon_title'] = $set->favicon_title ?? '';
            $data['favicon_title_client'] = $set->favicon_title_client ?? '';

            return successResponse('', $data);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'favicon_title' => ['nullable', 'string', 'max:255'],
            'favicon_title_client' => ['nullable', 'string', 'max:255'],
            'general_description' => ['nullable', 'string', 'max:255'],
            'general_og_title' => ['nullable', 'string', 'max:255'],
            'general_og_description' => ['nullable', 'string', 'max:255'],
            'general_og_image' => ['sometimes', 'file', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'general_og_same_as_meta' => ['nullable', 'boolean'],
            'pages_title_format' => ['nullable', 'string', 'max:255'],
            'groups_title_format' => ['nullable', 'string', 'max:255'],
            'pages_description_format' => ['nullable', 'string', 'max:255'],
            'groups_description_format' => ['nullable', 'string', 'max:255'],
            'pages_og_title_format' => ['nullable', 'string', 'max:255'],
            'groups_og_title_format' => ['nullable', 'string', 'max:255'],
            'pages_og_description_format' => ['nullable', 'string', 'max:255'],
            'groups_og_description_format' => ['nullable', 'string', 'max:255'],
            'pages_og_image' => ['sometimes', 'file', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'groups_og_image' => ['sometimes', 'file', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'pages_og_same_as_meta' => ['nullable', 'boolean'],
            'groups_og_same_as_meta' => ['nullable', 'boolean'],
        ]);

        try {
            // Same underlying fields shown/edited on Company Settings
            // (Setting::favicon_title / favicon_title_client, id=1) — kept
            // in sync so they can be edited from either page.
            Setting::find(1)?->update([
                'favicon_title' => $request->input('favicon_title'),
                'favicon_title_client' => $request->input('favicon_title_client'),
            ]);

            $rows = [];
            foreach (self::TEXT_FIELDS as $field) {
                $rows[] = [
                    'option_name' => 'seo',
                    'optional_field' => $field,
                    'option_value' => (string) $request->input($field, ''),
                    'status' => '',
                ];
            }
            foreach (self::BOOLEAN_FIELDS as $field) {
                $rows[] = [
                    'option_name' => 'seo',
                    'optional_field' => $field,
                    'option_value' => $request->boolean($field) ? '1' : '0',
                    'status' => '',
                ];
            }
            CommonSettings::upsert($rows, ['option_name', 'optional_field'], ['option_value']);

            foreach (self::IMAGE_FIELDS as $field) {
                if ($request->hasFile($field)) {
                    $path = Attach::put('images', $request->file($field), null, true);
                    CommonSettings::upsert(
                        [['option_name' => 'seo', 'optional_field' => $field, 'option_value' => basename((string) $path), 'status' => '']],
                        ['option_name', 'optional_field'],
                        ['option_value']
                    );
                }
            }

            try {
                app(SeoFileGenerator::class)->generateAll();
            } catch (Throwable $throwable) {
                report($throwable);
            }

            return successResponse(__('message.updated-successfully'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }
}
