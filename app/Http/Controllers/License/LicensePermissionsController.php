<?php

namespace App\Http\Controllers\License;

use App\Http\Controllers\Controller;
use App\Model\License\LicensePermission;
use App\Model\License\LicenseType;
use App\Model\Product\Product;
use Illuminate\Http\Request;

/*
* Operations for License Permissions Module to be performed here
* @author Ashutosh Pathak <ashutosh.pathak@ladybirdweb.com>
*/
class LicensePermissionsController extends Controller
{
    public $licensePermission;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');

        $licensePermission = new LicensePermission();
        $this->licensePermission = $licensePermission;
    }

    public function index()
    {
        $allPermissions = $this->licensePermission->select('id', 'permissions')->get();
        $allLicense = LicenseType::select('name', 'id')->get();

        return view('themes.default1.licence.permissions.index', compact('allPermissions', 'allLicense'));
    }

    /*
    * Get all the License  and their links with their permissions
    */
    public function getPermissions(Request $request)
    {
        try {
            $allPermissions = $this->licensePermission->select('id', 'permissions')->get();
            // $licenseType = LicenseType::select('id', 'name');

            $licenseType = LicenseType::leftJoin('license_license_permissions', 'license_license_permissions.license_type_id', '=', 'license_types.id')
              ->leftJoin('license_permissions', 'license_permissions.id', '=', 'license_license_permissions.license_permission_id')
              ->select('license_types.id', 'license_types.name', 'license_permissions.permissions', 'license_license_permissions.license_permission_id')->groupBy('license_types.id');
            //   dd($licenseType->first());

            return \DataTables::of($licenseType)
            ->orderColumn('license_type', '-license_types.id $1')
            ->orderColumn('permissions', '-license_types.id $1')
            ->addColumn('checkbox', function ($model) {
                return "<input type='checkbox' class='type_checkbox' 
            value=".$model->id.' name=select[] id=check>';
            })

            ->addColumn('license_type', function ($model) {
                return ucfirst($model->name);
            })
            ->addColumn('permissions', function ($model) {
                $permissions = $model->permissions()->pluck('permissions');
                $allPermissions = $this->showPermissions($permissions);

                return $allPermissions;
            })
            ->addColumn('action', function ($model) {
                $selectedPermission = $model->license_permission_id;

                return "<p><button data-toggle='modal' 
             data-id=".$model->id." data-permission= '$selectedPermission' 
             class='btn btn-sm btn-secondary get-license-type addPermission'><i class='fa fa-plus'
             style='color:white;'> </i>&nbsp;&nbsp;".__('message.add-permissions').'</button>&nbsp;</p>';
            })
              ->filterColumn('license_type', function ($query, $keyword) {
                  $sql = 'name like ?';
                  $query->whereRaw($sql, ["%{$keyword}%"]);
              })
                ->filterColumn('permissions', function ($query, $keyword) {
                    $sql = 'license_permissions.permissions like ?';
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })

            ->rawColumns(['checkbox', 'type_name', 'permissions', 'action'])

            ->make(true);
        } catch (\Exception $ex) {
            app('log')->error($ex->getMessage());

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /*
    Show All Permission in Datatable
    */
    public function showPermissions($permissions)
    {
        if (count($permissions) > 0) {
            $html = '<ul>';
            foreach ($permissions as $permission) {
                $html .= '<li><b>'.$permission.'</b></li>';
            }

            return $html.'</ul>';
        } else {
            $html = 'No Permissions Selected';

            return $html;
        }
    }

    /*
    * Add Permission to License
    */
    public function addPermission(Request $request)
    {
        try {
            $license = $request->input('licenseId');
            //Delete all the relation before Updating
            \DB::table('license_license_permissions')->where('license_type_id', $license)->delete();
            $licenseType = LicenseType::find($request->input('licenseId'));
            $licenseType->permissions()->attach($request->input('permissionid'));

            return ['message' => 'success', 'update' => __('message.permissions_updated_successfully')];
        } catch (\Exception $ex) {
            app('log')->error($ex->getMessage());
            $result = [$ex->getMessage()];

            return response()->json(compact('result'), 500);
        }
    }

    /*
     For Ticking permission for a License Type
    */

    public function tickPermission(Request $request)
    {
        $licenseTypeInstance = LicenseType::find($request->input('license'));
        $allPermission = $licenseTypeInstance->permissions;
        if (count($allPermission) > 0) {
            $permissionsArray = $allPermission->pluck('id');
        } else {
            $permissionsArray = [];
        }

        return response()->json(['permissions' => $permissionsArray, 'message' => 'success']);
    }

    /**
     * Get All the Permissions Allowed for a Product.
     *
     * @param  int  $productid  Id of the Product
     * @return [array] Returns all the Permissions in booleam Form.
     */
    public static function getPermissionsForProduct(int $productid)
    {
        try {
            $permissions = Product::find($productid)->licenseType->permissions->pluck('permissions'); //Get All the permissions related to patrticular Product
            $generateUpdatesxpiryDate = 0;
            $generateLicenseExpiryDate = 0;
            $generateSupportExpiryDate = 0;
            $downloadPermission = 0;
            $noPermissions = 0;
            $allowDownloadTillExpiry = 0;
            $retireAllDownloads = 0;
            foreach ($permissions as $permission) {
                if ($permission == 'Generate Updates Expiry Date') {
                    $generateUpdatesxpiryDate = 1; //Has Permission for generating Updates Expiry
                }
                if ($permission == 'Generate License Expiry Date') {
                    $generateLicenseExpiryDate = 1; //Has Permission for generating License Expiry
                }
                if ($permission == 'Generate Support Expiry Date') {
                    $generateSupportExpiryDate = 1; //Has Permission for generating Support Expiry
                }
                if ($permission == 'Can be Downloaded') {
                    $downloadPermission = 1; //Has Permission for Download
                }
                if ($permission == 'No Permissions') {
                    $noPermissions = 1;  //Has No Permission
                }
                if ($permission == 'Allow Downloads Before Updates Expire') {
                    $allowDownloadTillExpiry = 1;  //allow download after Expiry
                }
            }

            return ['generateUpdatesxpiryDate' => $generateUpdatesxpiryDate, 'generateLicenseExpiryDate' => $generateLicenseExpiryDate,
                'generateSupportExpiryDate' => $generateSupportExpiryDate, 'downloadPermission' => $downloadPermission, 'noPermissions' => $noPermissions,
                'allowDownloadTillExpiry' => $allowDownloadTillExpiry, ];
        } catch (\Exception $ex) {
            app('log')->error($ex->getMessage());

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }
}
