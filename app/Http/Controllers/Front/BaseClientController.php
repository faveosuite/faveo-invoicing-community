<?php

namespace App\Http\Controllers\Front;

use App\Facades\Attach;
use App\Http\Controllers\Controller;
use App\Http\Controllers\License\LicensePermissionsController;
use App\Http\Requests\User\ProfileRequest;
use App\Model\Order\Invoice;
use App\Model\Order\Order;
use App\Model\Product\Product;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BaseClientController extends Controller
{
    /**
     * Get the version list popup for the Product.
     *
     * @author Ashutosh Pathak <ashutosh.pathak@ladybirdweb.com>
     *
     * @date   2019-01-06
     *
     * @param  Order  $orders  Order For the Client
     * @param  int  $productid  Product id for the Order
     * @return array Show Modal Popup if Condition Satisfies
     */
    public function getPopup($query, int $productid)
    {
        $listUrl = '';
        $permissions = LicensePermissionsController::getPermissionsForProduct($productid);
        if ($permissions['downloadPermission'] == 1) { //If the Product has doownlaod permission
            if ($query->github_owner && $query->github_repository) {
                $listUrl = $this->downloadGithubPopup($query->client, $query->invoice_id, $productid);
            } else {
                $listUrl = $this->downloadPopup($query->client, $query->invoice_number, $productid);
            }
        }

        return $listUrl;
    }

    public function deployPopup($orderNumber)
    {
        return view('themes.default1.front.clients.deploy-popup', compact('orderNumber'));
    }


    /**
     *  This returns the popup with different version download link.
     *
     * @param $clientid
     * @param $invoiceid
     * @param $productid
     *
     * @return \Illuminate\Contracts\View\View
     * @throws
     */
    public function downloadPopup($clientid, $invoiceid, $productid)
    {
        return view('themes.default1.front.clients.download-list',
            compact('clientid', 'invoiceid', 'productid'));

    }

    /**
     *  This returns the popup with different version of github download link.
     *
     * @param $clientid
     * @param $invoiceid
     * @param $productid
     *
     * @return \Illuminate\Contracts\View\View
     * @throws
     */
    public function downloadGithubPopup($clientid, $invoiceid, $productid)
    {
        return view('themes.default1.front.clients.download-github-list',
            compact('clientid', 'invoiceid', 'productid'));
    }


    /**
     *  This returns the renewal popup in client panel orders.
     *
     * @param $id
     * @param $productid
     * @param $agents
     * @param $planName
     *
     * @return \Illuminate\Contracts\View\View
     * @throws
     */
    public function renewPopup($id, $productid, $agents, $planName)
    {
        return view('themes.default1.renew.popup', compact('id', 'productid', 'agents', 'planName'));
    }

    /**
     *  This returns the action button for download links.
     *
     * @param $countExpiry
     * @param $productid
     * @param $countVersions
     * @param $link
     * @param $orderEndDate
     *
     * @return string
     * @throws
     */
    public function getActionButton($countExpiry, $countVersions, $link, $orderEndDate, $productid)
    {
        $downloadPermission = LicensePermissionsController::getPermissionsForProduct($productid);
        if ($downloadPermission['allowDownloadTillExpiry'] == 1) {
            if (strtotime($link['created_at']) < strtotime($orderEndDate->update_ends_at)) {
                $githubApi = new \App\Http\Controllers\Github\GithubApiController();
                $link1 = $githubApi->getCurl1($link['zipball_url']);
                if ($link1['body'] == null) {
                    return '<p><a href='.$link1['header']['location']." 
            class='btn btn-sm btn-primary'><i class='fa fa-download'>
            </i>&nbsp;&nbsp;Download</a>".'&nbsp;

      </p>';
                } else {
                    $string = $link1['body']['message'];
                    preg_match_all('/https:\/\/[^\s,"]+/', $string, $matches);
                    $url = $matches[0][0];

                    return '<p><a href="'.$url.'" class="btn btn-sm btn-primary">
                    <i class="fa fa-download"></i>&nbsp;&nbsp;Download</a>&nbsp;</p>';
                }
            } else {
                return '<button class="btn btn-primary btn-sm disabled tooltip">
             <span class="tooltiptex">Please Renew!!</span></button>';
            }
        } elseif ($downloadPermission['allowDownloadTillExpiry'] == 0) {
            if ($countExpiry == $countVersions) {
                $githubApi = new \App\Http\Controllers\Github\GithubApiController();
                $link1 = $githubApi->getCurl1($link['zipball_url']);
                if ($link1['body'] == null) {
                    return '<p><a href='.$link['zipball_url']." 
            class='btn btn-sm btn-primary'><i class='fa fa-download'>
            </i>&nbsp;&nbsp;Download</a>".'&nbsp;

      </p>';
                } else {
                    $string = $link1['body']['message'];
                    preg_match_all('/https:\/\/[^\s,"]+/', $string, $matches);
                    $url = $matches[0][0];

                    return '<p><a href="'.$url.'" class="btn btn-sm btn-primary">
                    <i class="fa fa-download"></i>&nbsp;&nbsp;Download</a>&nbsp;</p>';
                }
            } else {
                return '<button class="btn btn-primary btn-sm disabled tooltip">
            <span class="tooltiptex">Please Renew!!</span></button>';
            }
        }
    }

    /**
     *  This function is to update profile.
     *
     * @param ProfileRequest $request
     *
     * @return
     * @throws
     */
    public function postProfile(ProfileRequest $request)
    {
        try {
            $user = \Auth::user();
            if ($request->hasFile('profile_pic')) {
                $path = Attach::put('common/images/users/', $request->file('profile_pic'), null, true);
                $user->profile_pic = basename($path);
            }
            $user->first_name = strip_tags($request->input('first_name'));
            $user->user_name = strip_tags($request->input('user_name'));
            $user->last_name = strip_tags($request->input('last_name'));
            $user->email = strip_tags($request->input('email'));
            $user->company = strip_tags($request->input('company'));
            $user->mobile_code = strip_tags($request->input('mobile_code'));
            $user->mobile_country_iso = strip_tags($request->input('mobile_country_iso'));
            $user->gstin = strip_tags($request->input('gstin'));
            $user->mobile = strip_tags($request->input('mobile'));
            $user->address = strip_tags($request->input('address'));
            $user->town = strip_tags($request->input('town'));
            $user->timezone_id = strip_tags($request->input('timezone_id'));
            $user->state = $request->input('state');
            $user->zip = strip_tags($request->input('zip'));
            $user->company_size = $request->input('company_size');
            $user->company_type = $request->input('company_type');
            $user->bussiness = $request->input('bussiness');
            $user->save();
    /**
     *  Returns to client individual orders with payment details as datatable.
     *
     * @param $orderid
     * @param $userid
     *
     * @return \Yajra\DataTables\DataTableAbstract|RedirectResponse
     * @throws \Exception
     */
            return successResponse(__('message.updated-successfully'));
        } catch (Exception $ex) {
            return errorResponse('Failed to update profile');
        }
    }

    /**
     *  This function is to update password.
     *
     * @param ProfileRequest $request
     *
     * @return
     * @throws
     */
    public function postPassword(ProfileRequest $request)
    {
        try {
            $user = \Auth::user();
            $oldPassword = $request->input('old_password');
            $newPassword = $request->input('new_password');

            if (! \Hash::check($oldPassword, $user->getAuthPassword())) {
                return errorResponse('Incorrect old password');
            }

            $user->password = \Hash::make($newPassword);
            $user->save();

            // Logout all other sessions if using web guard
            \Auth::logoutOtherDevices($newPassword);

            // Remove password reset records
            \DB::table('password_resets')->where('email', $user->email)->delete();

            return successResponse(\Lang::get('message.updated-successfully'));
        } catch (\Exception $e) {
            app('log')->error($e->getMessage());

            return errorResponse('Failed to update password');
        }
    }

    /**
     *  This function returns invoice using order id.
     *
     * @param $orderid
     * @param $userid
     * @param $admin
     * @return \Yajra\DataTables\DataTableAbstract|RedirectResponse
     * @throws Exception
     */
    public function getInvoicesByOrderId($orderid, $userid, $admin = null)
    {
        try {
            $order = Order::where('id', $orderid)->where('client', $userid)->first();

            $relation = $order->invoiceRelation()->pluck('invoice_id')->toArray();
            $invoice = new Invoice();
            $invoices = $invoice->leftJoin('invoice_items', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->select('invoices.number', 'invoices.created_at', 'invoices.date', 'invoices.grand_total', 'invoices.currency', 'invoices.id', 'invoices.status', 'invoice_items.product_name as products')
            ->whereIn('invoices.id', $relation);

            if ($invoices->get()->count() == 0) {
                $invoices = $order->invoice()
                        ->select('number', 'created_at', 'grand_total', 'id', 'status');
            }

            return \DataTables::of($invoices)
            ->orderColumn('number', '-invoices.id $1')
            ->orderColumn('products', '-invoices.id $1')
            ->orderColumn('date', '-invoices.id $1')
            ->orderColumn('total', '-invoices.id $1')
             ->orderColumn('status', '-invoices.id $1')

             ->addColumn('number', function ($model) use ($admin) {
                 $url = $this->getInvoiceLinkUrl($model->id, $admin);

                 return '<a href='.url($url).'>'.$model->number.'</a>';
             })
            ->addColumn('products', function ($model) {
                return ucfirst($model->products);
            })
            ->addColumn('date', function ($model) {
                return getDateHtml($model->date);
            })
            ->addColumn('total', function ($model) {
                return currencyFormat($model->grand_total, $code = $model->currency);
            })
            ->addColumn('status', function ($model) {
                if (\Auth::user()->role == 'admin') {
                    return getStatusLabel($model->status);
                }

                return getStatusLabel($model->status, 'badge');
            })
            ->addColumn('action', function ($model) use ($admin) {
                $url = $this->getInvoiceLinkUrl($model->id, $admin);
                $status = $model->status;
                $payment = '';
                if ($status != 'Success' && $model->grand_total > 0) {
                    $payment = '  <a href='.url('autopaynow/'.$model->id).
                    " class='btn btn-light-scale-2 btn-sm text-dark'><i class='fa fa-credit-card'></i></a>";
                }

                return '<p><a href='.url($url)." 
                class='btn btn-light-scale-2 btn-sm text-dark'".tooltip('View')."<i class='fa fa-eye' 
                > </i></a>".$payment.'</p>';
            })
              ->filterColumn('number', function ($query, $keyword) {
                  $sql = 'number like ?';
                  $query->whereRaw($sql, ["%{$keyword}%"]);
              })
              ->filterColumn('products', function ($query, $keyword) {
                  $sql = 'invoice_items.product_name like ?';
                  $query->whereRaw($sql, ["%{$keyword}%"]);
              })

            ->rawColumns(['number', 'products', 'date', 'total', 'status', 'action'])
                            ->make(true);
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     *  This function returns individual invoice opening link.
     *
     * @param $invoiceId
     * @param $admin
     * @return string
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
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     *  This function returns download link when update end date is greater than created date, when download permission(allowDownloadTillExpiry) is 1.
     *
     * @param $updateEndDate
     * @param $productid
     * @param $versions
     * @param $clientid
     * @param $invoiceid
     * @return string
     * @throws
     */
    public function whenDownloadTillExpiry($updateEndDate, $productid, $versions, $clientid, $invoiceid)
    {
        if ($versions->created_at->toDateTimeString()
        < $updateEndDate->update_ends_at) {
            return '<p><a href='.url('download/'.$productid.'/'
            .$clientid.'/'.$invoiceid.'/'.$versions->id).
            " class='btn btn-light-scale-2 btn-sm text-dark download-btn'><i class='fa fa-download'>
            </i>&nbsp;&nbsp;Download</a>".'&nbsp;

       </p>';
        } else {
            return '<button class="btn btn-danger 
        btn-sm disabled">Please Renew </button>';
        }
    }


    /**
     *  This function returns download link when download permission(allowDownloadTillExpiry) is 0.
     *
     * @param $updatesEndDate
     * @param $productid
     * @param $versions
     * @param $clientid
     * @param $invoiceid
     * @param $countExpiry
     * @param $countVersions
     * @return string
     * @throws
     */
    public function whenDownloadExpiresAfterExpiry($countExpiry, $countVersions, $updatesEndDate, $productid, $versions, $clientid, $invoiceid)
    {
        if ($countExpiry == $countVersions) {
            return '<p><a href='.url('download/'.$productid.'/'
            .$clientid.'/'.$invoiceid.'/'.$versions->id).
            " class='btn btn-light-scale-2 btn-sm text-dark download-btn'><i class='fa fa-download'>
            </i>&nbsp;&nbsp;Download</a>".'&nbsp;

       </p>';
        } else {
            return '<button class="btn btn-danger 
        btn-sm disabled">Please Renew </button>';
        }
    }

    /**
     *  This returns to the client panel orders page.
     *
     * @param Request $request

     *
     * @return \Illuminate\Contracts\View\View|RedirectResponse
     * @throws Exception
     */
    public function orders(Request $request)
    {
        try {
            return view('themes.default1.front.clients.order1', compact('request'));
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Get the Delete instance popup for cloud.
     *
     * @param  Order  $orders  Order For the Client
     * @param  int  $productid  Product id for the Order
     * @return array Show Modal Popup if Condition Satisfies
     */
    public function getCloudDeletePopup($query, int $productid)
    {
        $permissions = LicensePermissionsController::getPermissionsForProduct($productid);
        if ($permissions['downloadPermission'] != 1) { //If the Product has doownlaod permission
            return $this->deleteCloudPopup($query->number);
        }
    }

    /**
     *  This returns to the cloud popup deletion.
     *
     * @param $orderNumber
     *
     * @return \Illuminate\Contracts\View\View
     * @throws Exception
     */
    public function deleteCloudPopup($orderNumber)
    {
        return view('themes.default1.front.clients.delete-cloud-popup', compact('orderNumber'));
    }

    public function changeDomain($query, int $productid)
    {
        $permissions = LicensePermissionsController::getPermissionsForProduct($productid);
        if ($permissions['downloadPermission'] != 1) { //If the Product has download permission
            return $this->changecloudDomain($query->number);
        }
    }

    public function changecloudDomain($orderNumber)
    {
        return view('themes.default1.front.clients.changeDomain-popup', compact('orderNumber'));
    }
}
