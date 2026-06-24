<?php

namespace App\Http\Controllers\Front;

use App\Facades\Attach;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\ProfileRequest;
use App\Model\Order\Invoice;
use App\User;
use Auth;
use DB;
use Exception;
use Hash;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Logger;

class BaseClientController extends Controller
{
    /**
     *  This function is to update profile.
     */
    public function postProfile(ProfileRequest $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();
            if ($request->hasFile('profile_pic')) {
                $path = Attach::put('common/images/users/', $request->file('profile_pic'), null, true);
                $user->profile_pic = basename((string) $path);
            }

            $user->first_name = strip_tags((string) $request->input('first_name'));
            $user->user_name = strip_tags((string) $request->input('user_name'));
            $user->last_name = strip_tags((string) $request->input('last_name'));
            // Email & mobile are changed only through the verified OTP flow
            // (ProfileVerificationController); the profile save must not alter them.
            $user->company = strip_tags((string) $request->input('company'));
            $user->gstin = strip_tags((string) $request->input('gstin'));
            $user->address = strip_tags((string) $request->input('address'));
            $user->town = strip_tags((string) $request->input('town'));
            $user->timezone_id = (int) $request->input('timezone_id');
            $user->state = $request->input('state');
            $user->zip = strip_tags($request->input('zipcode') ?? $request->input('zip'));
            $user->company_size = $request->input('company_size');
            $user->company_type = $request->input('company_type');
            $user->bussiness = $request->input('bussiness');
            $user->save();

            return successResponse(__('message.updated-successfully'));
        } catch (Exception) {
            return errorResponse(__('message.failed_to_update_profile'));
        }
    }

    /**
     *  This function is to update password.
     */
    public function postPassword(ProfileRequest $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();
            $oldPassword = $request->input('old_password');
            $newPassword = $request->input('new_password');

            if (! Hash::check($oldPassword, $user->getAuthPassword())) {
                return errorResponse(__('message.incorrect_old_password'));
            }

            $user->password = Hash::make($newPassword);
            $user->save();

            // Logout all other sessions if using web guard
            deleteUserSessions($user->id, $newPassword);

            // Remove password reset records
            DB::table('password_resets')->where('email', $user->email)->delete();

            return successResponse(__('message.updated-successfully'));
        } catch (Exception $exception) {
            Logger::exception($exception);

            return errorResponse(__('message.failed_to_update_password'));
        }
    }

    /**
     *  This function returns individual invoice opening link.
     */
    public function getInvoiceLinkUrl(string $invoiceId, mixed $admin = null): string
    {
        if ($admin == 'admin') {
            return '/invoices/show?invoiceid='.$invoiceId;
        }

        return 'my-invoice/'.$invoiceId;
    }

    public function subscriptions(): mixed
    {
        try {
            return view('themes.default1.front.clients.subscription'); // @phpstan-ignore argument.type
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    /**
     *  This returns to the client panel orders page.
     *
     * @return View|RedirectResponse
     *
     * @throws Exception
     */
    public function orders(Request $request)
    {
        try {
            return view('themes.default1.front.clients.order1', compact('request')); // @phpstan-ignore argument.type
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    /**
     *  This returns to the cloud popup deletion.
     *
     * @return View
     *
     * @throws Exception
     */
    public function deleteCloudPopup(mixed $orderNumber): Factory|View
    {
        return view('themes.default1.front.clients.delete-cloud-popup', compact('orderNumber')); // @phpstan-ignore argument.type
    }
}
