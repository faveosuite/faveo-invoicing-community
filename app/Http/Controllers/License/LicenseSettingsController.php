<?php

namespace App\Http\Controllers\License;

use App\Model\License\LicenseType;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LicenseSettingsController extends LicensePermissionsController
{
    private LicenseType $licenseType;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');

        $licenseType = new LicenseType;
        $this->licenseType = $licenseType;
    }

    /*
    * Get All the categories
    */
    public function getLicenseTypes(Request $request): JsonResponse
    {
        try {
            $searchString = $request->input('search-query', '');
            $sortOrder = $request->input('sort-order', 'desc');
            $sortField = $request->input('sort-field', 'created_at');
            $limit = $request->input('limit', 10);

            $query = $this->licenseType
                ->select('id', 'name')
                ->when($searchString, function ($q) use ($searchString): void {
                    $q->where('name', 'LIKE', sprintf('%%%s%%', $searchString));
                });

            $licenseTypes = $query->orderBy($sortField, $sortOrder)
                ->paginate($limit);

            return successResponse('', $licenseTypes);
        } catch (Exception) {
            return errorResponse(__('message.something_went_wrong_try_again'));
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function createLicense(Request $request): JsonResponse
    {
        $this->validate($request, [
            'name' => ['required', Rule::unique('license_types', 'name')],
        ]);

        try {
            $this->licenseType->fill($request->input())->save();

            return successResponse(__('message.saved-successfully'));
        } catch (Exception $exception) {
            \Logger::exception($exception);
            return errorResponse(__('message.sorry_something_wrong'));
        }
    }

    public function updateLicense(Request $request, mixed $id): JsonResponse
    {
        $this->validate($request, [
            'name' => ['required', Rule::unique('license_types', 'name')->ignore($id)],
        ]);

        try {
            $type_name = $request->input('name');
            /** @var LicenseType|null $type */
            $type = $this->licenseType->find($id);

            if ($type) {
                $type->name = $type_name;
                $type->save();
            }

            return successResponse(__('message.updated-successfully'));
        } catch (Exception $exception) {
            \Logger::exception($exception);
            return errorResponse(__('message.sorry_something_wrong'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function deleteLicense(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('select');

            if (empty($ids)) {
                return errorResponse(__('message.select-a-row'));
            }

            foreach ($ids as $id) {
                /** @var LicenseType|null $type */
                $type = $this->licenseType->find($id);
                if ($type) {
                    $type->delete();
                }
            }

            return successResponse(__('message.deleted-successfully'));
        } catch (Exception $exception) {
            \Logger::exception($exception);
            return errorResponse(__('message.sorry_something_wrong'));
        }
    }

    public function getLicenseTypeById(mixed $id): JsonResponse
    {
        try {
            $type = $this->licenseType->select('id', 'name')->findOrFail($id);

            return successResponse('', $type);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            return errorResponse(__('message.record_not_found'));
        } catch (Exception $exception) {
            \Logger::exception($exception);
            return errorResponse(__('message.sorry_something_wrong'));
        }
    }
}
