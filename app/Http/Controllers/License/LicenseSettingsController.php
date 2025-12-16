<?php

namespace App\Http\Controllers\License;

use App\Model\License\LicenseType;
use Illuminate\Http\Request;

class LicenseSettingsController extends LicensePermissionsController
{
    private $licenseType;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');

        $licenseType = new LicenseType();
        $this->licenseType = $licenseType;
    }

    /*
    * Get All the categories
    */
    public function getLicenseTypes(Request $request)
    {
        try {
            $searchString = $request->input('search-query', '');
            $sortOrder = $request->input('sort-order', 'desc');
            $sortField = $request->input('sort-field', 'created_at');
            $limit = $request->input('limit', 10);

            $query = $this->licenseType
                          ->select('id', 'name')
                          ->when($searchString, function ($q) use ($searchString) {
                              $q->where('name', 'LIKE', "%$searchString%");
                });


            $licenseTypes = $query->orderBy($sortField, $sortOrder)
                ->simplePaginate($limit);
            $total = $licenseTypes->count();


            return successResponse('', [
                'license_types' => $licenseTypes,
                'total' => $total,
            ]);
        } catch (\Exception $ex) {
            return errorResponse(__('message.something_went_wrong_try_again'));
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createLicense(Request $request)
    {
        try {
            $productType = $this->licenseType->fill($request->input())->save();

            return successResponse(__('message.saved-successfully'));
        } catch (\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    public function updateLicense(Request $request, $id)
    {
        try {
            $type_name = $request->input('name');
            $type = $this->licenseType->find($id);

            if ($type) {
                $type->name = $type_name;
                $type->save();
            }

            return successResponse(__('message.updated-successfully'));
        } catch (\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteLicense(Request $request)
    {
        try {
            $ids = $request->input('select');

            if (empty($ids)) {
                return errorResponse(__('message.select-a-row'));
            }

            foreach ($ids as $id) {
                $type = $this->licenseType->find($id);
                if ($type) {
                    $type->delete();
                }
            }

            return successResponse(__('message.deleted-successfully'));
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }
}
