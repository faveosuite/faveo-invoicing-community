<?php

namespace App\Http\Controllers\User;

use App\Facades\Attach;
use App\Http\Controllers\Auth\BaseAuthController;
use App\Http\Requests\User\ProfileRequest;
use App\Model\Common\Bussiness;
use App\Model\Common\Timezone;
use App\User;
use Auth;
use DB;
use Exception;
use Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Lang;

class ProfileController extends BaseAuthController
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function profile(): JsonResponse|RedirectResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();
            $timezonesList = Timezone::get();
            $is2faEnabled = $user->is_2fa_enabled;
            $dateSinceEnabled = $user->google2fa_activation_date;
            $display = [];
            foreach ($timezonesList as $timezone) {
                $location = $timezone->location;
                if ($location) {
                    $start = strpos((string) $location, '(');
                    $end = strpos((string) $location, ')', $start + 1);
                    $length = $end - $start;
                    $result = substr((string) $location, $start + 1, $length - 1);
                    $display[] = ['id' => $timezone->id, 'name' => '('.$result.')'.' '.$timezone->name];
                }
            }

            // for display
            $timezones = array_column($display, 'name', 'id');
            $state = getStateByCode((string) $user->country, (string) $user->state);
            $states = findStateByRegionId($user->country);
            $bussinesses = Bussiness::pluck('name', 'short')->toArray();

            return successResponse('', ['bussinesses' => $bussinesses, 'user' => $user, 'timezones' => $timezones, 'state' => $state, 'states' => $states, 'is2faEnabled' => $is2faEnabled, 'dateSinceEnabled' => $dateSinceEnabled]);
            //            return view('themes.default1.user.profile', compact('bussinesses', 'user', 'timezones', 'state', 'states', 'is2faEnabled', 'dateSinceEnabled'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function updateProfile(ProfileRequest $request): JsonResponse|RedirectResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();
            if ($request->hasFile('profile_pic')) {
                $path = Attach::put('common/images/users/', $request->file('profile_pic'), null, true);
                $user->profile_pic = basename((string) $path);
            }

            $user->fill($request->input())->save();

            if ($request->expectsJson()) {
                return successResponse(__('message.updated-successfully'));
            }

            return successResponse(Lang::get('message.updated-successfully'));
        } catch (Exception $exception) {
            if ($request->expectsJson()) {
                return errorResponse($exception->getMessage());
            }

            return errorResponse($exception->getMessage());
        }
    }

    public function updatePassword(ProfileRequest $request): JsonResponse|RedirectResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();
            $oldpassword = $request->input('old_password');
            $currentpassword = $user->getAuthPassword();
            $newpassword = $request->input('new_password');
            if (Hash::check($oldpassword, $currentpassword)) {
                $user->password = Hash::make($newpassword);
                $user->save();

                deleteUserSessions($user->id, $newpassword);

                DB::table('password_resets')->where('email', $user->email)->delete();

                if ($request->expectsJson()) {
                    return successResponse(__('message.updated-successfully'));
                }

                return successResponse(Lang::get('message.updated-successfully'));
            }

            if ($request->expectsJson()) {
                return errorResponse(__('message.incorrect_old_password'));
            }

            return errorResponse(__('message.incorrect_old_password'));
        } catch (Exception $exception) {
            if ($request->expectsJson()) {
                return errorResponse($exception->getMessage());
            }

            return errorResponse($exception->getMessage());
        }
    }

    public function getCountries(): JsonResponse|RedirectResponse
    {
        $countries = getSupportedCountriesForIntlInput();
        $list = collect($countries)->map(fn ($name, $iso): array => ['id' => $iso, 'name' => $name])->values();

        return successResponse('', ['countries' => $list]);
    }

    public function getStatesByCountry(string $countryCode): JsonResponse|RedirectResponse
    {
        $states = findStateByRegionId($countryCode);
        $list = collect($states)->map(fn ($name, $iso): array => ['id' => $iso, 'name' => $name])->values();

        return successResponse('', ['states' => $list]);
    }
}
