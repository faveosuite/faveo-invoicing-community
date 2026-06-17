<?php

namespace App\Http\Controllers\License;

use App\Http\Controllers\Controller;
use App\Http\Requests\LocalizedLicenseRequest;
use App\License\Models\Installation;
use App\License\Services\InstallationService;
use App\Model\Order\Order;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class LocalizedLicenseController extends Controller
{
    public function __construct(protected InstallationService $installationService)
    {
        $this->middleware('auth');
        $this->middleware('admin', ['except' => ['downloadFile', 'downloadPrivate', 'storeFile']]);
    }

    /** @param non-empty-string $post_url */
    private function postCurl(string $post_url, mixed $post_info, string $token = ''): bool|string
    {
        if ($token !== '') {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $post_url);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BEARER);
            curl_setopt($ch, CURLOPT_XOAUTH2_BEARER, $token);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_info);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            $result = curl_exec($ch);
            curl_close($ch);

            return $result;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $post_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_info);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    /**
     * Downloads the license file.
     * */
    public function downloadFile(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
    {
        if (Auth::check()) {
            $orderNo = $request->get('orderNo');
            $fileName = 'faveo-license-{'.$orderNo.'}.txt';
            $filePath = storage_path('app/public/'.$fileName);

            return response()->download($filePath);
        }

        return redirect(url('login'));
    }

    /**
     * Downloads the license file through admin.
     * */
    public function downloadFileAdmin(string $fileName): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $filePath = storage_path('app/public/'.$fileName);

        return response()->download($filePath);
    }

    /**
     * Downloads the private key for the license.
     * */
    public function downloadPrivate(string $orderNo): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $fileName = storage_path('app/public/privateKey-'.$orderNo.'.txt');

        return response()->download($fileName);
    }

    /**
     * Downloads the private key for the license through admin panel.
     * */
    public function downloadPrivateKeyAdmin(string $fileName): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $value = explode('}', $fileName);
        $orderNo = substr($value[0], 15);
        $fileName = storage_path('app/public/privateKey-'.$orderNo.'.txt');

        return response()->download($fileName);
    }

    /**
     * Chooses which license mode is applicable File/Database.
     * */
    public function chooseLicenseMode(Request $request): \Illuminate\Http\JsonResponse
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

    public function filesApi(Request $request): \Illuminate\Http\JsonResponse
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
                $files = $files->filter(fn ($f): bool => str_contains(strtolower($f['file_name'] ?? ''), strtolower((string) $searchQuery)) ||
                    str_contains(strtolower($f['order_number'] ?? ''), strtolower((string) $searchQuery))
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

    public function deleteFileApi(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $fileName = $request->input('file_name');
            if (! $fileName || ! Str::startsWith($fileName, 'faveo-license')) {
                return errorResponse(__('message.invalid'));
            }

            Storage::disk('public')->delete($fileName);

            return successResponse(Lang::get('message.license_file_deleted', ['file' => $fileName]));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    /**
     * Stores the license file after the client has entered a domain and downloads the license.
     * */
    public function storeFile(LocalizedLicenseRequest $request): \Illuminate\Http\RedirectResponse
    {
        if (Auth::check()) {
            $userID = $request->input('userId');
            if (! empty($userID) && ! empty(Auth::user()->id)) {
                $domain = $request->input('domain');
                $orderNo = $request->input('orderNo');
                $licenseCode = Order::where('number', $orderNo)->value('serial_key');
                $id = Order::where('number', $orderNo)->value('id');
                $productId = DB::table('subscriptions')->where('order_id', $id)->value('product_id');
                $Latestversion = DB::table('product_uploads')->where('product_id', $productId)->latest()->value('version');

                $licenseExpiry = DB::table('subscriptions')->where('order_id', $id)->value('ends_at');
                $updatesExpiry = DB::table('subscriptions')->where('order_id', $id)->value('update_ends_at');
                $supportExpiry = DB::table('subscriptions')->where('order_id', $id)->value('support_ends_at');
                if (Date::parse($licenseExpiry)->format('Y-m-d') < 1) {
                    $licenseExpiry = '--';
                } else {
                    $licenseExpiry = Date::parse($licenseExpiry)->format('Y-m-d');
                }

                if (Date::parse($updatesExpiry)->format('Y-m-d') < 1) {
                    $updatesExpiry = '--';
                } else {
                    $updatesExpiry = Date::parse($updatesExpiry)->format('Y-m-d');
                }

                if (Date::parse($supportExpiry)->format('Y-m-d') < 1) {
                    $supportExpiry = '--';
                } else {
                    $supportExpiry = Date::parse($supportExpiry)->format('Y-m-d');
                }

                Installation::updateOrCreate(
                    ['license_code' => $licenseCode, 'installation_domain' => $domain],
                    ['installation_path' => $domain, 'version' => $Latestversion, 'installation_status' => 1]
                );
                $this->localizedLicenseInstallLM($orderNo);

                $userData = '<root_url>'.$domain.'</root_url><license_code>'.$licenseCode.'</license_code><license_expiry>'.$licenseExpiry.'</license_expiry><updates_expiry>'.$updatesExpiry.'</updates_expiry><support_expiry>'.$supportExpiry.'</support_expiry>';

                $encrypt = new EncryptDecryptController();
                $encryptData = $encrypt->encrypt($userData, $orderNo);

                $fileName = 'faveo-license-{'.$orderNo.'}.txt';
                Storage::disk('public')->put($fileName, $encryptData);

                $link = $this->tempOrderLink($orderNo, $userID);

                return Redirect::to($link);
            }

            return redirect(url('login'));
        }

        return redirect(url('login'));
    }

    /**
     * Generates a temporary link to download the license file with a time constraint.
     * */
    public function tempOrderLink(string $orderNo, int $userID): string|\Illuminate\Http\RedirectResponse
    {
        if (! empty($userID) && ! empty(Auth::user()->id)) {
            return URL::temporarySignedRoute('event.rsvp', now()->addSeconds(30), [
                'orderNo' => $orderNo,
            ]);
        }

        return redirect(url('login'));
    }

    private function localizedLicenseInstallLM(string $orderNo): void
    {
        Order::where('number', $orderNo)->value('product');
        date('Y-m-d');
        // Registration is now handled internally - no API call needed
    }

    /**
     * Edits the license details without showing the pre-existing license data.
     * */
    /*public function fileEdit(Request $request,$fileName)
    {
      $value = explode("}",$fileName);
      $orderNo = substr($value[0], 15);
      $fileName = "faveo-license-{".$orderNo."}.txt";
      dd($orderNo,$fileName);
      extract($this->getLicenseData($fileName,$orderNo));

      if(!is_null($request->get('root_url')))
      {
        $root_url = $request->get('root_url');
      }
      if(!is_null($request->get('license_expiry')))
      {
        $license_expiry = $request->get('license_expiry');
      }
      if(!is_null($request->get('updates_expiry')))
      {
        $updates_expiry = $request->get('updates_expiry');
      }
      if(!is_null($request->get('support_expiry')))
      {
        $support_expiry = $request->get('support_expiry');
      }

      $stored=Storage::disk('public')->path($fileName);
      $handle=@fopen($stored, "w+");
       $fwrite=@fwrite($handle,"<root_url>$root_url</root_url><license_code>$license_code</license_code><license_expiry>$license_expiry</license_expiry><updates_expiry>$updates_expiry</updates_expiry><support_expiry>$support_expiry</support_expiry>");
          if ($fwrite===false) //updating file failed
           {
            echo "Update was not performed";
            exit();
            }
       $encrypt = new EncryptDecryptController();
       $data=$encrypt->encrypt($fileName,$orderNo);
       Storage::disk('public')->put($fileName,$data);
       @fclose($handle);
       return redirect()->back()->with('success', Lang::get('License data is updated'.$orderNo));
    }*/

    /**
     * Deletes the license file.
     * */
    public function deleteFile(string $fileName): \Illuminate\Http\RedirectResponse
    {
        Storage::disk('public')->delete($fileName);

        return back()->with('success', Lang::get('message.license_file_deleted', ['file' => $fileName]));
    }

    //return an array with license data
    private function getLicenseData(string $fileName, string $orderNo): array
    {
        return $this->parseLicenseFile($fileName, $orderNo);
    }

    //parse license file and make an array with license data
    /**
     * @return string[]
     */
    private function parseLicenseFile(string $fileName, string $orderNo): array
    {
        $license_data_array = [];
        $stored = Storage::disk('public')->path($fileName);
        if (@is_readable($stored)) {
            $decrypt = new EncryptDecryptController();
            $contents = $decrypt->decrypt($orderNo);
            Storage::disk('public')->put($fileName, $contents);
            $stored = Storage::disk('public')->path($fileName);
            $file_content = file_get_contents($stored);
            preg_match_all("/<([a-z_]+)>(.*?)<\/([a-z_]+)>/", $file_content, $matches, PREG_SET_ORDER);
            foreach ($matches as $value) {
                if (isset($value[1]) && ($value[1] !== '' && $value[1] !== '0') && $value[1] == $value[3]) {
                    $license_data_array[$value[1]] = $value[2];
                }
            }
        }

        return $license_data_array;
    }
}
