<?php

namespace App\Http\Controllers\User;

use App\Events\UserOrderDelete;
use App\User;
use Illuminate\Http\Request;

class SoftDeleteController extends ClientController
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
        return view('themes.default1.user.client.softDelete', compact('request'));
    }

    public function softDeletedUsers(Request $request)
    {
        $baseQuery = User::leftJoin('countries', 'users.country', '=', 'countries.country_code_char2')
            ->select('id', 'first_name', 'last_name', 'email',
                \DB::raw("CONCAT('+', mobile_code, ' ', mobile) as mobile"),
                \DB::raw("CONCAT(first_name, ' ', last_name) as name"),
                'country_name as country', 'created_at', 'active', 'mobile_verified', 'is_2fa_enabled', 'role', 'position'
            )->onlyTrashed();

        return \DataTables::of($baseQuery)
                        ->orderColumn('name', '-users.created_at $1')
                        ->orderColumn('email', '-users.created_at $1')
                        ->orderColumn('mobile', '-users.created_at $1')
                        ->orderColumn('email', '-users.created_at $1')
                        ->orderColumn('mobile', '-users.created_at $1')
                        ->orderColumn('country', '-users.created_at $1')
                        ->orderColumn('company', '-users.created_at $1')
                         ->orderColumn('created_at', '-users.created_at $1')
                          ->orderColumn('active', '-users.created_at $1')

                        ->addColumn('checkbox', function ($model) {
                            return "<input type='checkbox' class='user_checkbox' value=".$model->id.' name=select[] id=check>';
                        })
                        ->addColumn('name', function ($model) {
                            return ucfirst($model->name);
                        })
                         ->addColumn('email', function ($model) {
                             return $model->email;
                         })
                        ->addColumn('mobile', function ($model) {
                            return $model->mobile;
                        })
                        ->addColumn('country', function ($model) {
                            return ucfirst(strtolower($model->country));
                        })
                        ->addColumn('company', function ($model) {
                            return $model->company;
                        })
                        ->addColumn('created_at', function ($model) {
                            return getDateHtml($model->created_at);
                        })
                        ->addColumn('active', function ($model) {
                            return $this->getActiveLabel($model->mobile_verified, $model->active, $model->is_2fa_enabled);
                        })
                        ->addColumn('action', function ($model) {
                            return '<a href='.url('clients/'.$model->id.'/restore')
                            ." class='btn btn-sm btn-secondary btn-xs'".tooltip('Restore')."
                            <i class='fas fa-sync-alt' style='color:white;'> </i></a>";
                        })

                           ->filterColumn('name', function ($model, $keyword) {
                               // removing all white spaces so that it can be searched irrespective of number of spaces
                               $model->whereRaw("CONCAT(first_name, ' ',last_name) like ?", ["%$keyword%"]);
                           })
                        ->filterColumn('email', function ($model, $keyword) {
                            $model->whereRaw('email like ?', ["%$keyword%"]);
                        })
                        ->filterColumn('country', function ($model, $keyword) {
                            // removing all white spaces so that it can be searched in a single query
                            $searchQuery = str_replace(' ', '', $keyword);
                            $model->whereRaw('country_name like ?', ["%$searchQuery%"]);
                        })
                        ->filterColumn('mobile', function ($model, $keyword) {
                            // removing all white spaces so that it can be searched in a single query
                            $searchQuery = str_replace(' ', '', $keyword);
                            $model->whereRaw("CONCAT('+', mobile_code, mobile) like ?", ["%$searchQuery%"]);
                        })

                        ->rawColumns(['checkbox', 'name', 'email',  'created_at', 'active', 'action'])
                        ->make(true);
    }

    public function restoreUser($id)
    {
        $user = User::onlyTrashed()->find($id);
        if (! is_null($user)) {
            $user->restore();
        }

        return redirect()->back()->with('success', 'User restored successfully');
    }

    public function permanentDeleteUser(Request $request)
    {
        try {
            $ids = $request->input('select');
            if (! empty($ids)) {
                foreach ($ids as $id) {
                    $user = User::onlyTrashed()->find($id);
                    if (! is_null($user)) {
                        $tenants = $user->order()->pluck('domain');

                        foreach ($tenants as $tenant) {
                            if ($tenant !== '') {
                                event(new UserOrderDelete($tenant));
                            }
                        }
                        $user->invoiceItem()->delete();
                        $user->orderRelation()->delete();
                        $user->invoice()->delete();
                        $user->order()->delete();
                        $user->subscription()->delete();
                        $user->comments()->delete();
                        $user->auto_renewal()->delete();
                        $user->export_details()->delete();
                        $user->userLinkReports()->delete();
                        $user->forceDelete();
                    } else {
                        echo "<div class='alert alert-success alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */
                    \Lang::get('message.success').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */\Lang::get('message.no-record').'
                </div>';
                        //echo \Lang::get('message.no-record') . '  [id=>' . $id . ']';
                    }
                }
                echo "<div class='alert alert-success alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert')
                    .'!</b> './* @scrutinizer ignore-type */
                    \Lang::get('message.success').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */\Lang::get('message.deleted-successfully').'
                </div>';
            } else {
                echo "<div class='alert alert-success alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '
                    ./* @scrutinizer ignore-type */\Lang::get('message.success').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */\Lang::get('message.select-a-row').'
                </div>';
            }
        } catch (\Exception $e) {
            echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        '.$e->getMessage().'
                </div>';
        }
    }
}
