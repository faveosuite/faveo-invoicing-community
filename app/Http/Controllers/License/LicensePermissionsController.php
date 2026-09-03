<?php

namespace App\Http\Controllers\License;

use App\Http\Controllers\Controller;
use App\Model\License\LicensePermission;
use App\Model\License\LicenseType;
use App\Model\Product\Product;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Logger;

/*
* Operations for License Permissions Module to be performed here
* @author Ashutosh Pathak <ashutosh.pathak@ladybirdweb.com>
*/
class LicensePermissionsController extends Controller
{
    /**
     * @var LicensePermission
     */
    public $licensePermission;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');

        $licensePermission = new LicensePermission;
        $this->licensePermission = $licensePermission;
    }

    /*
    * Get all the License  and their links with their permissions
    */
    public function getPermissions(Request $request): JsonResponse
    {
        try {
            $searchString = $request->input('search-query', '');
            $sortOrder = $request->input('sort-order', 'asc');
            $sortField = $request->input('sort-field', 'name');
            $limit = $request->input('limit', 10);

            $allPermissions = LicensePermission::select('id', 'permissions')->get();

            $licenseTypes = LicenseType::with('permissions:id,permissions')
                ->when($searchString, function ($query) use ($searchString): void {
                    $query->where('name', 'like', sprintf('%%%s%%', $searchString));
                })
                ->orderBy($sortField, $sortOrder)
                ->paginate($limit);

            $data = $licenseTypes->getCollection()->map(fn ($license): array => [ // @phpstan-ignore return.type
                'id' => $license->id,
                'name' => $license->name,
                'permissions' => $license->permissions->pluck('permissions'),
                'all_permissions' => $allPermissions->map(fn ($perm): array => [
                    'id' => $perm->id,
                    'permissions' => $perm->permissions,
                    'assigned' => $license->permissions->contains('id', $perm->id),
                ]),
            ]);

            $licenseTypes->setCollection($data); // @phpstan-ignore argument.type

            return successResponse(__('message.license_types_permissions_fetched'), $licenseTypes);
        } catch (Exception) {
            return errorResponse(__('message.something_went_wrong_try_again'));
        }
    }

    /*
    * Add Permission to License
    */
    public function addPermission(Request $request): JsonResponse
    {
        try {
            /** @var LicenseType|null $licenseType */
            $licenseType = LicenseType::find($request->input('licenseId'));

            if (! $licenseType) {
                return errorResponse(__('message.no_record_found'), 404);
            }

            $permissionIds = (array) $request->input('permissionid');
            $noPermissionsId = LicensePermission::where('permissions', 'No Permissions')->value('id');

            // "No Permissions" is mutually exclusive with every real permission —
            // if it's selected alongside others, it wins and the rest are dropped.
            if ($noPermissionsId && in_array($noPermissionsId, $permissionIds)) {
                $permissionIds = [$noPermissionsId];
            }

            $licenseType->permissions()->sync($permissionIds);

            return successResponse(__('message.permissions_updated_successfully'));
        } catch (Exception $exception) {
            Logger::exception($exception);

            return errorResponse($exception->getMessage());
        }
    }

    /*
    Show All Permission in Datatable
    */
    //            $html = '<ul>';
    //                $html .= '<li><b>'.$permission.'</b></li>';
    //            }
    //
    //            return $html.'</ul>';
    //            $html = 'No Permissions Selected';
    //
    //            return $html;
    //        }
    //    }

    /*
     For Ticking permission for a License Type
    */

    //        $allPermission = $licenseTypeInstance->permissions;
    //            $permissionsArray = $allPermission->pluck('id');
    //            $permissionsArray = [];
    //        }
    //
    //        return response()->json(['permissions' => $permissionsArray, 'message' => 'success']);
    //    }

    /**
     * Maps the human-readable permission labels stored in `license_permissions.permissions`
     * to the camelCase keys used throughout the codebase (e.g. `$permissions['downloadPermission']`).
     *
     * @return array<string, string>
     */
    public static function permissionMap(): array
    {
        return [
            'Generate Updates Expiry Date' => 'generateUpdatesxpiryDate',
            'Generate License Expiry Date' => 'generateLicenseExpiryDate',
            'Generate Support Expiry Date' => 'generateSupportExpiryDate',
            'Can be Downloaded' => 'downloadPermission',
            'No Permissions' => 'noPermissions',
            'Allow Downloads Before Updates Expire' => 'allowDownloadTillExpiry',
        ];
    }

    /**
     * Get All the Permissions Allowed for a Product.
     *
     * @param  int  $productid  Id of the Product
     * @return array<mixed> Returns all the Permissions in booleam Form.
     */
    public static function getPermissionsForProduct(int $productid)
    {
        try {
            $map = self::permissionMap();

            $result = array_fill_keys(array_values($map), 0);

            $product = Product::find($productid);

            if (! $product || ! $product->licenseType || ! $product->licenseType->permissions) { // @phpstan-ignore booleanNot.alwaysFalse
                return $result;
            }

            $permissions = $product->licenseType->permissions->pluck('permissions')->toArray();

            foreach ($permissions as $permission) {
                if (isset($map[$permission])) {
                    $result[$map[$permission]] = 1;
                }
            }

            return $result;
        } catch (Exception $exception) {
            Logger::exception($exception);
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
