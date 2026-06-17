<?php

namespace App\Http\Controllers\Front;

use app\Cart\UserCart;
use App\Facades\Attach;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Github\GithubApiController;
use App\Http\Controllers\License\LicensePermissionsController;
use App\Http\Requests\User\ProfileRequest;
use App\Model\Order\Invoice;
use App\Model\Order\Order;
use Auth;
use DB;
use Exception;
use Hash;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Lang;
use Logger;

class BaseClientController extends Controller
{

    /**
     *  This function is to update profile.
     *
     * @param  ProfileRequest  $request
     * @return
     *
     * @throws
     */
    public function postProfile(ProfileRequest $request)
    {
        try {
            $user = Auth::user();
            if ($request->hasFile('profile_pic')) {
                $path = Attach::put('common/images/users/', $request->file('profile_pic'), null, true);
                $user->profile_pic = basename($path);
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
            $user->timezone_id = strip_tags((string) $request->input('timezone_id'));
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
     *
     * @param  ProfileRequest  $request
     * @return
     *
     * @throws
     */
    public function postPassword(ProfileRequest $request)
    {
        try {
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

            return successResponse(Lang::get('message.updated-successfully'));
        } catch (Exception $e) {
            Logger::exception($e);

            return errorResponse(__('message.failed_to_update_password'));
        }
    }

    /**
     *  This function returns invoice using order id.
     *
     * @param  $orderid
     * @param  $userid
     * @param  $admin
     * @return \Yajra\DataTables\DataTableAbstract|RedirectResponse
     *
     * @throws Exception
     */
    public function getInvoicesByOrderId($orderid, $userid, $admin = null)
    {
        try {
            $order = Order::where('id', $orderid)
                ->where('client', $userid)
                ->firstOrFail();

            if (! authorizeOwnership($userid, true)) {
                return back()->with('fails', __('message.unauthorized_action'));
            }

            $invoiceIds = $order->invoiceRelation()->pluck('invoice_id');

            $query = Invoice::query()
                ->leftJoin('invoice_items', 'invoices.id', '=', 'invoice_items.invoice_id')
                ->select(
                    'invoices.number',
                    'invoices.created_at',
                    'invoices.date',
                    'invoices.grand_total',
                    'invoices.currency',
                    'invoices.id',
                    'invoices.is_renewed',
                    'invoices.status',
                    'invoice_items.product_name as products'
                );

            $invoices = $invoice->with(['invoiceItem'])->where('id', $relation)
                ->whereHas('invoiceItem', function ($query) use ($order): void {
                    $query->where('id', $order->invoice_item_id);
                });

            $limit = '10';
            $page = 'page';
            $sortField = 'created_at';
            $sortOrder = 'asc';
            $paginated = $invoices->orderBy($sortField, $sortOrder)
                ->simplePaginate($limit, ['*'], 'page', 1);

            // Map items
            $paginated->getCollection()->transform(function ($model) use ($admin) {
                $url = '';
                $status = '';
                $action = '';
                $url = $this->getInvoiceLinkUrl($model->id, $admin);
                if ($url) {
                    $url = '<a href='.url($url).'>'.$model->number.'</a>';
                }

                if (Auth::user()->role == 'admin') {
                    $status = getStatusLabel($model->status);
                } else {
                    $status = getStatusLabel($model->status);
                }

                if ($status != 'Success' && $model->grand_total > 0) {
                    $payment = '  <a href='.url('autopaynow/'.$model->id).
                        " class='btn btn-light-scale-2 btn-sm text-dark'><i class='fa fa-credit-card'></i></a>";

                    $action = '<p><a href='.url($url)."
                class='btn btn-light-scale-2 btn-sm text-dark'".tooltip(__('message.view'))."<i class='fa fa-eye'
                > </i></a>".$payment.'</p>';
                }

                return [
                    'number' => $url,
                    'products' => ucfirst((string) $model->invoiceItem->value('product_name')),
                    'date' => getDateHtml($model->date),
                    'total' => currencyFormat($model->grand_total, $code = $model->currency),
                    'status' => $status,
                    'action' => $action,
                ];
            });

            return successResponse('', $paginated);
        } catch (Exception $ex) {
            return back()->with('fails', $ex->getMessage());
        }
    }

    /**
     *  This function returns individual invoice opening link.
     *
     * @param  $invoiceId
     * @param  $admin
     * @return string
     *
     * @throws
     */
    public function getInvoiceLinkUrl($invoiceId, $admin = null)
    {
        $link = 'my-invoice/'.$invoiceId;
        if ($admin == 'admin') {
            $link = '/invoices/show?invoiceid='.$invoiceId;
        }

        return $link;
    }

    public function subscriptions()
    {
        try {
            return view('themes.default1.front.clients.subscription');
        } catch (Exception $ex) {
            return back()->with('fails', $ex->getMessage());
        }
    }

    /**
     *  This returns to the client panel orders page.
     *
     * @param  Request  $request
     * @return View|RedirectResponse
     *
     * @throws Exception
     */
    public function orders(Request $request)
    {
        try {
            return view('themes.default1.front.clients.order1', compact('request'));
        } catch (Exception $ex) {
            return back()->with('fails', $ex->getMessage());
        }
    }

    /**
     *  This returns to the cloud popup deletion.
     *
     * @param  $orderNumber
     * @return View
     *
     * @throws Exception
     */
    public function deleteCloudPopup($orderNumber)
    {
        return view('themes.default1.front.clients.delete-cloud-popup', compact('orderNumber'));
    }

    public function cartAccess(Request $request)
    {
        $method = $request->input('method');
        $deliverable = match ($method) {
            'clear' => UserCart::clear(),
            'getContent' => UserCart::getContent(),
            'getTotal' => UserCart::getTotal(),
            'isEmpty' => UserCart::isEmpty(),
            'getTotalQuantity' => UserCart::getTotalQuantity(),
            'default' => null,
        };

        return successResponse('success', $deliverable);
    }
}
