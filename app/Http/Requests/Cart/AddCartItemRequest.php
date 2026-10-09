<?php

declare(strict_types=1);

namespace App\Http\Requests\Cart;

use App\Http\Controllers\Tenancy\CloudExtraActivities;
use App\Model\Common\FaveoCloud;
use App\Traits\RequestJsonValidation;
use GuzzleHttp\Client;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AddCartItemRequest extends FormRequest
{
    use RequestJsonValidation;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<mixed>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'plan_id' => ['nullable', 'integer'],
            'quantity' => ['sometimes', 'integer', 'min:1'],
            'agents' => ['sometimes', 'integer', 'min:1'],
            'domain' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9]([a-zA-Z0-9-]*[a-zA-Z0-9])?$/'],
            'data_center_id' => ['nullable', 'integer', 'exists:cloud_data_centers,id'],
            'billing_cycle' => ['sometimes', 'in:monthly,yearly,onetime'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['domain.regex' => __('message.domain_alphanumeric_hyphen_only')];
    }

    /**
     * A cloud product's domain must still be free on the cloud server — checked
     * before checkout so the buyer can pick another name instead of being given
     * a random one after paying.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $domain = (string) $this->input('domain');

            if ($domain === '' || $validator->errors()->isNotEmpty() || ! in_array($this->integer('product_id'), cloudPopupProducts())) {
                return;
            }

            if (! new CloudExtraActivities(new Client, new FaveoCloud)->checkDomain(strtolower($domain).'.'.cloudSubDomain())) {
                $validator->errors()->add('domain', __('message.domain_taken'));
            }
        }];
    }
}
