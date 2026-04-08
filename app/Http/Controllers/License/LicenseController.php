<?php

namespace App\Http\Controllers\License;

use App\Http\Controllers\Controller;
use App\Modules\License\Services\LicenseService;
use App\Modules\License\Services\InstallationService;
use App\Model\Order\Order;
use App\Model\Product\Product;

class LicenseController extends Controller
{
    private ?LicenseService $licenseService = null;
    private ?InstallationService $installationService = null;

    public function __construct(
        ?LicenseService $licenseService = null,
        ?InstallationService $installationService = null
    ) {
        $this->licenseService = $licenseService;
        $this->installationService = $installationService;
    }

    private function getLicenseService(): LicenseService
    {
        return $this->licenseService ?? app(LicenseService::class);
    }

    private function getInstallationService(): InstallationService
    {
        return $this->installationService ?? app(InstallationService::class);
    }

    /**
     * Get the Ip and domain that is to be entered in License Manager.
     */
    protected function getIpAndDomain($domain)
    {
        if ($domain != '') {
            if (ip2long($domain)) {
                return ['ip' => $domain, 'domain' => '', 'requireDomain' => 0];
            } else {
                return ['ip' => '', 'domain' => $domain, 'requireDomain' => 1];
            }
        } else {
            return ['ip' => '', 'domain' => '', 'requireDomain' => 0];
        }
    }

    /*
     *  Add New Product - No longer needed, products are local only
     *  @deprecated Product sync to remote license system is removed
     */
    public function addNewProduct($product_name, $product_sku)
    {
        // Products are now stored locally, no remote sync needed
    }

    /*
     *  Add New User - No longer needed, users are local only
     *  @deprecated User sync to remote license system is removed
     */
    public function addNewUser($first_name, $last_name, $email)
    {
        // Users are now stored locally, no remote sync needed
    }

    /*
     *  Edit Product - No longer needed, products are local only
     *  @deprecated Product sync to remote license system is removed
     */
    public function editProduct($product_name, $product_sku)
    {
        // Products are now stored locally, no remote sync needed
    }

    /*
     *  Search for product id - No longer needed
     *  @deprecated Remote product search is removed
     */
    public function searchProductId($product_sku)
    {
        return null;
    }

    public function deleteProductFromAPL($product)
    {
        // Products are now stored locally, no remote delete needed
    }

    /*
     *  Edit User - No longer needed, users are local only
     *  @deprecated User sync to remote license system is removed
     */
    public function editUserInLicensing($first_name, $last_name, $email)
    {
        // Users are now stored locally, no remote sync needed
    }

    /*
     *  Search for user id - No longer needed
     *  @deprecated Remote user search is removed
     */
    public function searchForUserId($email)
    {
        return null;
    }

    /*
     *  Create New License For User
     */
    public function createNewLicene($orderid, $product, $user_id, $licenseExpiry, $updatesExpiry, $supportExpiry, $serial_key)
    {
        try {
            $sku = Product::where('id', $product)->first()->product_sku;

            $licenseExpiry = ($licenseExpiry != '') ? $licenseExpiry->toDateString() : null;
            $updatesExpiry = ($updatesExpiry != '') ? $updatesExpiry->toDateString() : null;
            $supportExpiry = ($supportExpiry != '') ? $supportExpiry->toDateString() : null;
            $order = Order::where('id', $orderid)->first();

            $orderNo = $order->number;
            $domain = $order->domain;
            $ipAndDomain = $this->getIpAndDomain($domain);
            $ip = $ipAndDomain['ip'];
            $domain = $ipAndDomain['domain'];
            $requireDomain = $ipAndDomain['requireDomain'];

            $this->getLicenseService()->create([
                'product_id'             => $product,
                'user_id'                => $user_id,
                'license_code'           => $serial_key,
                'license_order_number'   => $orderNo,
                'license_domain'         => $domain,
                'license_ip'             => $ip,
                'license_require_domain' => $requireDomain,
                'license_limit'          => 1,
                'license_expire_date'    => $licenseExpiry,
                'license_updates_date'   => $updatesExpiry,
                'license_support_date'   => $supportExpiry,
                'license_status'         => 1,
            ]);
        } catch (\Exception $ex) {
            throw new \Exception(__('message.configure_valid_license'));
        }
    }

    /*
     *  Edit Existing License
     */
    public function updateLicensedDomain($licenseCode, $domain, $productId, $licenseExpiry, $updatesExpiry, $supportExpiry, $orderNo, $license_limit = 2, $requiredomain = 1)
    {
        try {
            $l_expiry = '';
            $s_expiry = '';
            $u_expiry = '';
            if (strtotime($licenseExpiry) > 1) {
                $l_expiry = date('Y-m-d', strtotime($licenseExpiry));
            }
            if (strtotime($updatesExpiry) > 1) {
                $u_expiry = date('Y-m-d', strtotime($updatesExpiry));
            }
            if (strtotime($supportExpiry) > 1) {
                $s_expiry = date('Y-m-d', strtotime($supportExpiry));
            }
            $ipAndDomain = $this->getIpAndDomain($domain);
            $ip = $ipAndDomain['ip'];
            $domain = $ipAndDomain['domain'];
            $requireDomain = $ipAndDomain['requireDomain'];

            $license = $this->getLicenseService()->findByCode($licenseCode);
            if ($license) {
                $this->getLicenseService()->update($license->id, [
                    'license_order_number'   => $orderNo,
                    'license_require_domain' => $requireDomain,
                    'license_expire_date'    => $l_expiry ?: $license->license_expire_date,
                    'license_updates_date'   => $u_expiry ?: $license->license_updates_date,
                    'license_support_date'   => $s_expiry ?: $license->license_support_date,
                    'license_domain'         => $domain,
                    'license_ip'             => $ip,
                    'license_limit'          => $license_limit,
                ]);
            }
        } catch (\Exception $ex) {
            throw new \Exception(__('message.configure_valid_license'));
        }
    }

    // Update the Installation status as Inactive after Licensed Domain Is Changed
    public function updateInstalledDomain($licenseCode, $productId)
    {
        try {
            $this->getInstallationService()->updateByLicenseCode($licenseCode, [
                'installation_status' => 0,
            ]);
        } catch (\Exception $ex) {
            throw new \Exception(__('message.configure_valid_license'));
        }
    }

    public function searchInstallationsId($licenseCode)
    {
        $installations = $this->getInstallationService()->getByLicenseCode($licenseCode);
        return $installations->toJson();
    }

    public function searchInstallationPath($licenseCode, $productId)
    {
        $installation_domain = [];
        $installation_ip = [];
        $installation_date = [];
        $installation_status = [];
        
        $installations = $this->getInstallationService()->getByLicenseCode($licenseCode);
        
        foreach ($installations as $detail) {
            if ($detail->product_id == $productId) {
                $installation_domain[] = $detail->installation_domain;
                $installation_ip[] = $detail->installation_ip;
                $installation_date[] = $detail->installation_date;
                $installation_status[] = $detail->installation_status;
            }
        }

        return [
            'installed_path' => $installation_domain,
            'installed_ip' => $installation_ip,
            'installation_date' => $installation_date,
            'installation_status' => $installation_status,
        ];
    }

    // Update Expiration Date After Renewal
    public function updateExpirationDate($licenseCode, $expiryDate, $productId, $domain, $orderNo, $licenseExpiry, $supportExpiry, $license_limit = 2, $requiredomain = 1)
    {
        try {
            $ipAndDomain = $this->getIpAndDomain($domain);
            $ip = $ipAndDomain['ip'];
            $domain = $ipAndDomain['domain'];
            $requireDomain = $ipAndDomain['requireDomain'];

            $license = $this->getLicenseService()->findByCode($licenseCode);
            if ($license) {
                $this->getLicenseService()->update($license->id, [
                    'license_order_number'   => $orderNo,
                    'license_domain'         => $domain,
                    'license_ip'             => $ip,
                    'license_require_domain' => $requireDomain,
                    'license_expire_date'    => $licenseExpiry,
                    'license_updates_date'   => $expiryDate,
                    'license_support_date'   => $supportExpiry,
                    'license_limit'          => $license_limit,
                ]);
            }
        } catch (\Exception $ex) {
            throw new \Exception(__('message.configure_valid_license'));
        }
    }

    public function getNoOfAllowedInstallation($licenseCode, $productId)
    {
        return $this->getInstallationService()->countActiveInstallations($licenseCode);
    }

    public function getInstallPreference($licenseCode, $productId)
    {
        $license = $this->getLicenseService()->findByCode($licenseCode);
        return $license ? $license->license_require_domain : 1;
    }

    public function deActivateTheLicense($licenseCode)
    {
        try {
            $this->getLicenseService()->deactivate($licenseCode);
        } catch (\Exception $ex) {
            \Logger::exception($ex);
            return;
        }
    }

    public function reissueDomain($installationPath)
    {
        $this->getInstallationService()->reissue($installationPath);
    }

    public function updateLicense($license_code, $oldLicense)
    {
        $this->getLicenseService()->updateLicenseCode($oldLicense, $license_code);
    }

    public function licenseRedirect($orderNumber)
    {
        return redirect('/orders/'.Order::where('number', $orderNumber)->value('id'));
    }

    public function syncTheAddonForALicense($product_ids, $license_code, $options = [])
    {
        $this->getLicenseService()->syncAddons($license_code, $product_ids);
    }

    public function getInstallationLogsDetails($license_code)
    {
        $result = $this->getInstallationService()->getLogs($license_code);

        // Return the page_message array from the service response
        return $result['page_message'] ?? [];
    }

    public function updateInstallationLogs($root_url, $version_number, $installation_ip, $licenseCode)
    {
        try {
            $this->getInstallationService()->updateLogs([
                'license_code'    => $licenseCode,
                'root_url'        => $root_url,
                'version_number'  => $version_number,
                'installation_ip' => $installation_ip,
            ]);
        } catch (\Exception $ex) {
            throw new \Exception(__('message.configure_valid_license'));
        }
    }

    public function searchProductUsingLicense($licenseCode)
    {
        try {
            $license = $this->getLicenseService()->findByCode($licenseCode);
            
            if ($license) {
                return collect([$license])->toArray();
            }

            return [];
        } catch (\Exception $ex) {
            throw new \Exception(__('message.configure_valid_license'));
        }
    }

    public function searchProductUsingProductKey($productKey)
    {
        try {
            $product = Product::where('product_key', $productKey)->first();
            
            if ($product) {
                return ['status' => 'success', 'product_id' => $product->id];
            }

            return ['status' => 'error', 'message' => 'Product not found'];
        } catch (\Exception $ex) {
            throw new \Exception(__('message.configure_valid_license'));
        }
    }
}
