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
            $searchString = $request->input('search-query', '');
            $sortOrder = $request->input('sort-order', 'asc');
            $sortField = $request->input('sort-field', 'name');
            $limit = $request->input('limit', 10);

            $allPermissions = LicensePermission::select('id', 'permissions')->get();

            $licenseTypes = LicenseType::with('permissions:id,permissions')
                ->when($searchString, function ($query) use ($searchString) {
                    $query->where('name', 'like', "%$searchString%");
                })
                ->orderBy($sortField, $sortOrder)
                ->simplePaginate($limit);

            $data = $licenseTypes->getCollection()->map(function ($license) use ($allPermissions) {
                return [
                    'id' => $license->id,
                    'name' => $license->name,
                    'permissions' => $license->permissions->pluck('permissions'),
                    'all_permissions' => $allPermissions->map(function ($perm) use ($license) {
                        return [
                            'id' => $perm->id,
                            'permissions' => $perm->permissions,
                            'assigned' => $license->permissions->contains('id', $perm->id),
                        ];
                    }),
                ];
            });

            $licenseTypes->setCollection($data);

            return successResponse(__('message.license_types_permissions_fetched'), [
                'license_types' => $licenseTypes,
            ]);
        } catch (\Exception $ex) {
            return errorResponse($ex->getMessage());
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
            $licenseType = LicenseType::find($request->input('licenseId'));

            $licenseType->permissions()->sync($request->input('permissionid'));

            return successResponse(__('message.permissions_updated_successfully'));
        } catch (\Exception $ex) {
            \Logger::exception($ex);

            return errorResponse($ex->getMessage());
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
            $map = [
                'Generate Updates Expiry Date' => 'generateUpdatesxpiryDate',
                'Generate License Expiry Date' => 'generateLicenseExpiryDate',
                'Generate Support Expiry Date' => 'generateSupportExpiryDate',
                'Can be Downloaded' => 'downloadPermission',
                'No Permissions' => 'noPermissions',
                'Allow Downloads Before Updates Expire' => 'allowDownloadTillExpiry',
            ];

            $result = array_fill_keys(array_values($map), 0);

            $product = Product::find($productid);

            if (! $product || ! $product->licenseType || ! $product->licenseType->permissions) {
                return $result;
            }

            $permissions = $product->licenseType->permissions->pluck('permissions')->toArray();

            foreach ($permissions as $permission) {
                if (isset($map[$permission])) {
                    $result[$map[$permission]] = 1;
                }
            }

            return $result;
        } catch (\Exception $ex) {
            \Logger::exception($ex);

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }
}
