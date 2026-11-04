<?php

declare(strict_types=1);

namespace App\Http\Controllers\Common;

use App\Facades\Attach;
use App\Http\Controllers\Controller;
use App\Model\Common\CommonSettings;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Brand panel on the guest auth pages (login, register, ...): show/hide, which side
 * the form sits on, title, description, tagline and image. Stored as option_name='auth_brand' rows in
 * common_settings. Empty text / no image = the client falls back to the
 * translated defaults and the built-in illustration.
 */
class AuthBrandController extends Controller
{
    private const OPTION = 'auth_brand';

    /**
     * @var list<string>
     */
    private const TEXT_FIELDS = ['title', 'description', 'tagline'];

    /**
     * Current values, shared by the admin form and the client SPA shell (client.blade.php).
     *
     * @return array{show: bool, form_position: 'left'|'right', title: string, description: string, tagline: string, image: ?string}
     */
    public static function settings(): array
    {
        $rows = CommonSettings::where('option_name', self::OPTION)->pluck('option_value', 'optional_field');
        $image = (string) $rows->get('image', '');

        return [
            'show' => $rows->get('show', '1') !== '0',
            'form_position' => $rows->get('form_position') === 'right' ? 'right' : 'left',
            'title' => (string) $rows->get('title', ''),
            'description' => (string) $rows->get('description', ''),
            'tagline' => (string) $rows->get('tagline', ''),
            'image' => $image !== '' ? Attach::getUrlPath('images/'.$image) : null,
        ];
    }

    public function show(): JsonResponse
    {
        return successResponse('', self::settings());
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'show' => ['required', 'boolean'],
            'form_position' => ['sometimes', 'in:left,right'],
            'title' => ['nullable', 'string', 'max:255'], // NOSONAR - Laravel validation rule, repeated by design
            'description' => ['nullable', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            // No SVG: an uploaded SVG opened directly could run script on our origin.
            'image' => ['sometimes', 'file', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'use_default_image' => ['nullable', 'boolean'],
        ]);

        try {
            $rows = [['option_name' => self::OPTION, 'optional_field' => 'show', 'option_value' => $request->boolean('show') ? '1' : '0', 'status' => '']];
            if ($request->has('form_position')) {
                $rows[] = ['option_name' => self::OPTION, 'optional_field' => 'form_position', 'option_value' => (string) $request->input('form_position'), 'status' => ''];
            }
            foreach (self::TEXT_FIELDS as $field) {
                $rows[] = ['option_name' => self::OPTION, 'optional_field' => $field, 'option_value' => trim((string) $request->input($field, '')), 'status' => ''];
            }

            if ($request->boolean('use_default_image')) {
                $rows[] = ['option_name' => self::OPTION, 'optional_field' => 'image', 'option_value' => '', 'status' => ''];
            } elseif ($request->hasFile('image')) {
                $path = Attach::put('images', $request->file('image'), null, true);
                $rows[] = ['option_name' => self::OPTION, 'optional_field' => 'image', 'option_value' => basename((string) $path), 'status' => ''];
            }

            CommonSettings::upsert($rows, ['option_name', 'optional_field'], ['option_value']);

            return successResponse(__('message.updated-successfully'));
        } catch (Exception $exception) {
            \Logger::exception($exception);

            return errorResponse(__('message.sorry_something_wrong'));
        }
    }
}
