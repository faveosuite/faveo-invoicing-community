<?php

namespace App\Http\Controllers\License;

use App\ApiKey;
use App\Http\Controllers\Controller;
use App\Http\Requests\LocalizedLicenseRequest;
use App\Model\Order\Order;
use App\ThirdPartyApp;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class LocalizedLicenseController extends Controller
{
    private $api_key_secret;
    private $url;
    private $license;
    private $token;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin', ['except'=>['downloadFile', 'downloadPrivate', 'storeFile']]);
        $model = new ApiKey();
        $this->license = $model->first();

        $this->api_key_secret = $this->license->license_api_secret;
        $this->url = $this->license->license_api_url;

        $this->token = ThirdPartyApp::where('app_secret', 'LicenseSecret')->value('app_key');
    }

    private function postCurl($post_url, $post_info)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $post_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_info);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    /**
     * Downloads the license file.
     * */
    public function downloadFile(Request $request)
    {
        if (Auth::check()) {
            $orderNo = $request->get('orderNo');
            $fileName = 'faveo-license-{'.$orderNo.'}.txt';
            $filePath = storage_path('app/public/'.$fileName);

            return response()->download($filePath);
        } else {
            return redirect(url('login'));
        }
    }

    /**
     * Downloads the license file through admin.
     * */
    public function downloadFileAdmin($fileName)
    {
        $filePath = storage_path('app/public/'.$fileName);

        return response()->download($filePath);
    }

    /**
     * Downloads the private key for the license.
     * */
    public function downloadPrivate($orderNo)
    {
        $fileName = storage_path('app/public/privateKey-'.$orderNo.'.txt');

        return response()->download($fileName);
    }

    /**
     * Downloads the private key for the license through admin panel.
     * */
    public function downloadPrivateKeyAdmin($fileName)
    {
        $value = explode('}', $fileName);
        $orderNo = substr($value[0], 15);
        $fileName = storage_path('app/public/privateKey-'.$orderNo.'.txt');

        return response()->download($fileName);
    }

    /**
     * Chooses which license mode is applicable File/Database.
     * */
    public function chooseLicenseMode(Request $request)
    {
        $chose = $request->input('choose');
        $orderNo = $request->input('orderNo');
        if ($chose) {
            $encrypt = new EncryptDecryptController();
            $encrypt->generateKeys($orderNo);
            Order::where('number', $orderNo)->update(['license_mode' => 'File']);

            return response()->json(['success'=>'Status change successfully.']); //return redirect()->back()->with('success', Lang::get('Private and Public Keys generated for this order number: '.$orderNo));
        } else {
            Order::where('number', $orderNo)->update(['license_mode' => 'Database']);
            Storage::disk('public')->delete('publicKey-'.$orderNo.'.txt');
            Storage::disk('public')->delete('privateKey-'.$orderNo.'.txt');
            Storage::disk('public')->delete('faveo-license-{'.$orderNo.'}.txt');

            return response()->json(['success'=>'Status change successfully.']); //return redirect()->back()->with('success',Lang::get('Reverted back to database license mode' .$orderNo));
        }
    }

    /**
     * Stores the license file after the client has entered a domain and downloads the license.
     * */
    public function storeFile(LocalizedLicenseRequest $request)
    {
        if (Auth::check()) {
            $userID = $request->input('userId');
            if (! empty($userID) && ! empty(Auth::user()->id)) {
                $domain = $request->input('domain');
                $orderNo = $request->input('orderNo');
                $licenseCode = Order::where('number',$orderNo)->value('serial_key');
                $id = Order::where('number', $orderNo)->value('id');
                $licenseExpiry = DB::table('subscriptions')->where('order_id',$id)->value('ends_at');
                $updatesExpiry = DB::table('subscriptions')->where('order_id',$id)->value('update_ends_at');
                $supportExpiry = DB::table('subscriptions')->where('order_id',$id)->value('support_ends_at');
                if (Carbon::parse($licenseExpiry)->format('Y-m-d')<1) {
                    $licenseExpiry = '--';
                } 
                else{
                      $licenseExpiry = Carbon::parse($licenseExpiry)->format('Y-m-d');
                }
                if (Carbon::parse($updatesExpiry)->format('Y-m-d')<1) {
                    $updatesExpiry = '--';
                }
                  else{
                      $updatesExpiry = Carbon::parse($updatesExpiry)->format('Y-m-d');
                }
                if (Carbon::parse($supportExpiry)->format('Y-m-d')<1) {
                    $supportExpiry = '--';
                }
                  else{
                      $supportExpiry = Carbon::parse($supportExpiry)->format('Y-m-d');
                }
                DB::table('installation_details')->insertOrIgnore(['installation_path'=> $domain, 'order_id'=>$id, 'last_active'=>date('Y-m-d')]);
                $this->localizedLicenseInstallLM($orderNo, $domain, $licenseCode);

                $userData = '<root_url>'.$domain.'</root_url><license_code>'.$licenseCode.'</license_code><license_expiry>'.$licenseExpiry.'</license_expiry><updates_expiry>'.$updatesExpiry.'</updates_expiry><support_expiry>'.$supportExpiry.'</support_expiry>';

                $encrypt = new EncryptDecryptController();
                $encryptData = $encrypt->encrypt($userData, $orderNo);

                $fileName = 'faveo-license-{'.$orderNo.'}.txt';
                Storage::disk('public')->put($fileName, $encryptData);

                $link = $this->tempOrderLink($orderNo, $userID);

                return Redirect::to($link);
            } else {
                return redirect(url('login'));
            }
        } else {
            return redirect(url('login'));
        }
    }

    /**
     * Generates a temporary link to download the license file with a time constraint.
     * */
    public function tempOrderLink($orderNo, $userID)
    {
        if (! empty($userID) && ! empty(Auth::user()->id)) {
            $url = URL::temporarySignedRoute('event.rsvp', now()->addSeconds(30), [
                'orderNo'=>$orderNo,
            ]);

            return $url;
        } else {
            return redirect(url('login'));
        }
    }

    private function localizedLicenseInstallLM($orderNo, $domain, $licenseCode)
    {
        $client_email = '';
        $url = $this->url;
        $token = $this->token;
        $api_key_secret = $this->api_key_secret;
        $productId = Order::where('number', $orderNo)->value('product');
        $installation_date = date('Y-m-d');
        $installation_hash = hash('sha256', $domain.$client_email.$licenseCode);
        $addLocalizedInstallation = $this->postCurl($url.'api/admin/addInstallation', "api_key_secret=$api_key_secret&token=$token&product_id=$productId&license_code=$licenseCode&installation_domain=$domain&installation_date=$installation_date&installation_status=1&installation_hash=$installation_hash");
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
    public function deleteFile($fileName)
    {
        Storage::disk('public')->delete($fileName);

        return redirect()->back()->with('success', Lang::get('License File is deleted '.$fileName));
    }

    //return an array with license data
    private function getLicenseData($fileName, $orderNo)
    {
        $settings_row = [];
        $settings_row = $this->parseLicenseFile($fileName, $orderNo);

        return $settings_row;
    }

    //parse license file and make an array with license data
    private function parseLicenseFile($fileName, $orderNo)
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
            if (! empty($matches)) {
                foreach ($matches as $value) {
                    if (! empty($value[1]) && $value[1] == $value[3]) {
                        $license_data_array[$value[1]] = $value[2];
                    }
                }
            }
        }

        return $license_data_array;
    }
}
