<?php

namespace App\Http\Controllers\User;

use App\Facades\Attach;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\ProfileRequest;
use Hash;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function profile()
    {
        try {
            $user = \Auth::user();
            $timezonesList = \App\Model\Common\Timezone::get();
            $is2faEnabled = $user->is_2fa_enabled;
            $dateSinceEnabled = $user->google2fa_activation_date;
            foreach ($timezonesList as $timezone) {
                $location = $timezone->location;
                if ($location) {
                    $start = strpos($location, '(');
                    $end = strpos($location, ')', $start + 1);
                    $length = $end - $start;
                    $result = substr($location, $start + 1, $length - 1);
                    $display[] = ['id' => $timezone->id, 'name' => '('.$result.')'.' '.$timezone->name];
                }
            }
            //for display
            $timezones = array_column($display, 'name', 'id');
            $state = getStateByCode($user->state);
            $states = findStateByRegionId($user->country);
            $bussinesses = \App\Model\Common\Bussiness::pluck('name', 'short')->toArray();

            return view('themes.default1.user.profile', compact('bussinesses', 'user', 'timezones', 'state', 'states', 'is2faEnabled', 'dateSinceEnabled'));
        } catch (\Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    public function updateProfile(ProfileRequest $request)
    {
        try {
            $user = \Auth::user();
            if ($request->hasFile('profile_pic')) {
                $path = Attach::put('common/images/users/', $request->file('profile_pic'), null, true);
                $user->profile_pic = basename($path);
            }
            $user->fill($request->input())->save();

            return redirect()->back()->with('success', \Lang::get('message.updated-successfully'));
        } catch (\Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    public function updatePassword(ProfileRequest $request)
    {
        try {
            $user = \Auth::user();
            $oldpassword = $request->input('old_password');
            $currentpassword = $user->getAuthPassword();
            $newpassword = $request->input('new_password');
            if (Hash::check($oldpassword, $currentpassword)) {
                $user->password = Hash::make($newpassword);
                $user->save();

                //logout all other session when password is updated
                deleteUserSessions($user->id, $newpassword);

                \DB::table('password_resets')->where('email', $user->email)->delete();

                return redirect()->back()->with('success1', \Lang::get('message.updated-successfully'));
            } else {
                return redirect()->back()->with('fails1', __('message.incorrect_old_password'));
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }
}
