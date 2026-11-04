<?php

namespace App\Http\Requests\User;

use App\Model\Common\State;
use App\Rules\PhoneNumber;
use App\User;
use Auth;
use Illuminate\Validation\Rule;

/**
 * Admin panel: the signed-in admin edits their own profile.
 */
class AdminProfileRequest extends ProfileRequest
{
    /**
     * @return array<mixed>
     */
    public function rules(): array
    {
        /** @var User $authUser */
        $authUser = Auth::user();
        $userid = $authUser->id;

        return [
            'first_name' => ['required'],
            'last_name' => ['required'],
            'company' => ['required', 'max:50'],
            'email' => ['required'],
            'mobile' => ['required', new PhoneNumber($this->mobile_country_iso)],
            'address' => ['required'],
            'user_name' => 'required|unique:users,user_name,'.$userid.'|unique:users,email,'.$userid.'|unique:settings,email|unique:settings,company_email',
            'timezone_id' => ['required', 'exists:timezone,id'],
            'profile_pic' => ['sometimes', 'mimes:jpeg,png,jpg', 'max:2048'],
            'country' => ['required'],
            'state' => [Rule::requiredIf(fn () => State::where('country_code', $this->country)->exists())],
            'gstin' => ['nullable', 'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/'],
        ];
    }
}
