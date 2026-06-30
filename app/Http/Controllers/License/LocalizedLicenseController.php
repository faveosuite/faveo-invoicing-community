<?php

namespace App\Http\Controllers\License;

use App\Http\Controllers\Controller;
use App\License\Services\InstallationService;
use App\Model\Order\Order;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LocalizedLicenseController extends Controller
{
    public function __construct(protected InstallationService $installationService)
    {
        $this->middleware('auth');
        $this->middleware('admin', ['except' => ['downloadFile', 'downloadPrivate']]);
    }

    /**
     * Downloads the license file.
     * */
    public function downloadFile(Request $request): BinaryFileResponse
    {
        if (! Auth::check()) {
            abort(401);
        }

        $orderNo = $request->get('orderNo');
        $fileName = 'faveo-license-{'.$orderNo.'}.txt';
        $filePath = storage_path('app/public/'.$fileName);

        return response()->download($filePath);
    }

    /**
     * Downloads the license file through admin.
     * */
    public function downloadFileAdmin(string $fileName): BinaryFileResponse
    {
        $filePath = storage_path('app/public/'.$fileName);

        return response()->download($filePath);
    }

    /**
     * Downloads the private key for the license.
     * */
    public function downloadPrivate(string $orderNo): BinaryFileResponse
    {
        $fileName = storage_path('app/public/privateKey-'.$orderNo.'.txt');

        return response()->download($fileName);
    }

    /**
     * Downloads the private key for the license through admin panel.
     * */
    public function downloadPrivateKeyAdmin(string $fileName): BinaryFileResponse
    {
        $value = explode('}', $fileName);
        $orderNo = substr($value[0], 15);
        $fileName = storage_path('app/public/privateKey-'.$orderNo.'.txt');

        return response()->download($fileName);
    }

    /**
     * Chooses which license mode is applicable File/Database.
     * */
    public function chooseLicenseMode(Request $request): JsonResponse
    {
        $orderNo = $request->input('orderNo');
        $chose = $request->boolean('choose');
        $order = Order::where('number', $orderNo);

        $order->update(['license_mode' => $chose ? 'File' : 'Database']);

        if ($chose) {
            resolve(EncryptDecryptController::class)->generateKeys($orderNo);
        } else {
            $files = [
                sprintf('publicKey-%s.txt', $orderNo),
                sprintf('privateKey-%s.txt', $orderNo),
                sprintf('faveo-license-%s.txt', $orderNo),
            ];
            Storage::disk('public')->delete($files);
        }

        return successResponse(__('message.status_change_successfully'));
    }

    public function filesApi(Request $request): JsonResponse
    {
        try {
            $searchQuery = $request->input('search-query', '');
            $sortOrder = $request->input('sort-order', 'asc');
            $sortField = $request->input('sort-field', 'file_name');
            $limit = (int) $request->input('limit', 10);
            $page = (int) $request->input('page', 1);

            $files = collect(Storage::disk('public')->files())
                ->filter(fn ($file) => Str::startsWith($file, 'faveo-license'))
                ->values()
                ->map(function (string $file): array {
                    $orderNo = null;
                    if (preg_match('/faveo-license-\{(.+)}\.txt/', $file, $matches)) {
                        $orderNo = $matches[1];
                    }

                    return [
                        'file_name' => $file,
                        'order_number' => $orderNo,
                        'download_url' => url('LocalizedLicense/downloadLicense/'.$file),
                        'private_key_url' => url('LocalizedLicense/downloadPrivateKey/'.$file),
                    ];
                });

            if ($searchQuery) {
                $files = $files->filter(fn ($f): bool => str_contains(strtolower($f['file_name']), strtolower((string) $searchQuery)) ||
                    str_contains(strtolower((string) $f['order_number']), strtolower((string) $searchQuery))
                )->values();
            }

            $files = $files->sortBy($sortField, SORT_REGULAR, $sortOrder === 'desc')->values();

            $total = $files->count();
            $items = $files->slice(($page - 1) * $limit, $limit)->values();
            $paginator = new LengthAwarePaginator($items, $total, $limit, $page, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);

            return successResponse('', $paginator);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function deleteFileApi(Request $request): JsonResponse
    {
        try {
            $fileName = $request->input('file_name');
            if (! $fileName || ! Str::startsWith($fileName, 'faveo-license')) {
                return errorResponse(__('message.invalid'));
            }

            Storage::disk('public')->delete($fileName);

            return successResponse(__('message.license_file_deleted', ['file' => $fileName]));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    /**
     * Generates a temporary link to download the license file with a time constraint.
     * */
    public function tempOrderLink(string $orderNo, int $userID): string
    {
        if ($userID === 0 || empty(Auth::user()->id)) {
            abort(401);
        }

        return URL::temporarySignedRoute('event.rsvp', now()->addSeconds(30), [
            'orderNo' => $orderNo,
        ]);
    }

    /**
     * Edits the license details without showing the pre-existing license data.
     * */

    // return an array with license data
    /**
     * @return array<mixed>
     */
    private function getLicenseData(string $fileName, string $orderNo): array // @phpstan-ignore method.unused
    {
        return $this->parseLicenseFile($fileName, $orderNo);
    }

    // parse license file and make an array with license data
    /**
     * @return string[]
     */
    private function parseLicenseFile(string $fileName, string $orderNo): array
    {
        $license_data_array = [];
        $stored = Storage::disk('public')->path($fileName);
        if (@is_readable($stored)) {
            $decrypt = new EncryptDecryptController;
            $contents = $decrypt->decrypt($orderNo);
            Storage::disk('public')->put($fileName, $contents);
            $stored = Storage::disk('public')->path($fileName);
            $file_content = file_get_contents($stored);
            preg_match_all("/<([a-z_]+)>(.*?)<\/([a-z_]+)>/", (string) $file_content, $matches, PREG_SET_ORDER);
            foreach ($matches as $value) {
                if (isset($value[1]) && $value[1] !== '0' && $value[1] == $value[3]) { // @phpstan-ignore isset.offset
                    $license_data_array[$value[1]] = $value[2];
                }
            }
        }

        return $license_data_array;
    }
}
