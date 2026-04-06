<?php

namespace App\License\Database\Seeders;

use App\License\Models\LicenseNotification;
use App\License\Models\LicenseScheme;
use App\License\Models\VersionNotification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class LicenseModuleSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedLicenseNotifications();
        $this->seedVersionNotifications();
        $this->seedLicenseSchemes();
    }

    private function seedLicenseNotifications(): void
    {
        if (! Schema::hasTable('license_notifications') || LicenseNotification::exists()) {
            return;
        }

        LicenseNotification::create([
            'notification_product_not_found' => 'Requested product not found',
            'notification_product_inactive' => 'Product %PRODUCT_TITLE% is inactive',
            'notification_license_ok' => 'License OK',
            'notification_license_not_found' => 'License with license code %LICENSE_CODE% not found (or product not found or is inactive)',
            'notification_invalid_ip' => '%PRODUCT_TITLE% installation on IP address %IP_ADDRESS% is not allowed',
            'notification_invalid_domain' => '%PRODUCT_TITLE% installation on domain %ROOT_URL% is not allowed',
            'notification_domain_required' => '%PRODUCT_TITLE% installation is only allowed on a real and working domain',
            'notification_domain_in_use' => 'Domain %ROOT_URL% is already in use by another client',
            'notification_license_suspended' => '%PRODUCT_TITLE% license suspended',
            'notification_license_expired' => '%PRODUCT_TITLE% license expired on %LICENSE_EXPIRE_DATE%, please renew it',
            'notification_updates_expired' => '%PRODUCT_TITLE% updates expired on %LICENSE_UPDATES_DATE%',
            'notification_support_expired' => '%PRODUCT_TITLE% support expired on %LICENSE_SUPPORT_DATE%',
            'notification_license_cancelled' => '%PRODUCT_TITLE% license cancelled on %LICENSE_CANCEL_DATE%',
            'notification_license_limit' => 'The maximum number of allowed %PRODUCT_TITLE% installations (%LICENSE_LIMIT% installation(s) total) reached',
            'notification_installation_not_found' => '%PRODUCT_TITLE% installation on domain %ROOT_URL% and/or IP address %IP_ADDRESS% not found',
            'notification_invalid_signature' => 'License signature is invalid',
            'notification_host_banned' => 'Hostname %IP_ADDRESS% is banned',
            'notification_unknown_error' => 'An unknown error occurred (probably database failure or unauthorized modification of data)',
        ]);
    }

    private function seedVersionNotifications(): void
    {
        if (! Schema::hasTable('version_notifications') || VersionNotification::exists()) {
            return;
        }

        VersionNotification::create([
            'notification_operation_ok' => 'Operation successful',
            'notification_product_not_found' => 'Requested product not found',
            'notification_product_inactive' => 'Product %PRODUCT_TITLE% is inactive',
            'notification_product_no_versions' => '%PRODUCT_TITLE% has no versions',
            'notification_version_not_found' => 'Requested %PRODUCT_TITLE% version not found',
            'notification_version_inactive' => 'Product %PRODUCT_TITLE% version %VERSION_NUMBER% is inactive',
            'notification_version_expired' => '%PRODUCT_TITLE% version %VERSION_NUMBER% expired on %VERSION_EXPIRE_DATE%',
            'notification_install_limit_reached' => '%PRODUCT_TITLE% version %VERSION_NUMBER% installations limit (%VERSION_INSTALL_LIMIT%) reached',
            'notification_upgrade_limit_reached' => '%PRODUCT_TITLE% version %VERSION_NUMBER% upgrades limit (%VERSION_UPGRADE_LIMIT%) reached',
            'notification_install_archive_not_found' => '%PRODUCT_TITLE% version %VERSION_NUMBER% installation archive not found',
            'notification_install_query_not_found' => '%PRODUCT_TITLE% version %VERSION_NUMBER% installation query not found',
            'notification_upgrade_archive_not_found' => '%PRODUCT_TITLE% version %VERSION_NUMBER% upgrade archive not found',
            'notification_upgrade_query_not_found' => '%PRODUCT_TITLE% version %VERSION_NUMBER% upgrade query not found',
            'notification_raw_install_query_not_found' => '%PRODUCT_TITLE% version %VERSION_NUMBER% installation raw query not found',
            'notification_raw_upgrade_query_not_found' => '%PRODUCT_TITLE% version %VERSION_NUMBER% upgrade raw query not found',
            'notification_installation_not_verified' => '%PRODUCT_TITLE% updates are only allowed for verified installations',
            'notification_invalid_parameter' => 'Invalid parameters',
            'notification_invalid_signature' => 'Script signature is invalid',
            'notification_host_banned' => 'Hostname %IP_ADDRESS% is banned',
            'notification_unknown_error' => 'An unknown error occurred (probably database failure or unauthorized modification of data)',
        ]);
    }

    private function seedLicenseSchemes(): void
    {
        if (! Schema::hasTable('license_schemes') || LicenseScheme::exists()) {
            return;
        }

        $schemes = [
            [
                'scheme_query' => "CREATE TABLE %APL_DATABASE_TABLE% (SETTING_ID TINYINT(1) NOT NULL AUTO_INCREMENT,ROOT_URL VARCHAR(250) NOT NULL,CLIENT_EMAIL VARCHAR(250) NOT NULL,LICENSE_CODE VARCHAR(250) NOT NULL,LCD VARCHAR(250) NOT NULL,LRD VARCHAR(250) NOT NULL,INSTALLATION_KEY VARCHAR(250) NOT NULL,INSTALLATION_HASH VARCHAR(250) NOT NULL,PRIMARY KEY (SETTING_ID)) DEFAULT CHARSET=utf8;INSERT INTO %APL_DATABASE_TABLE% (SETTING_ID, ROOT_URL, CLIENT_EMAIL, LICENSE_CODE, LCD, LRD, INSTALLATION_KEY, INSTALLATION_HASH) VALUES ('1', '%ROOT_URL%', '%CLIENT_EMAIL%', '%LICENSE_CODE%', '%LCD%', '%LRD%', '%INSTALLATION_KEY%', '%INSTALLATION_HASH%');",
                'scheme_status' => 1,
            ],
            [
                'scheme_query' => "CREATE TABLE %APL_PLUGIN_DATABASE_TABLE% (SETTING_ID TINYINT(1) NOT NULL AUTO_INCREMENT, ROOT_URL VARCHAR(250) NOT NULL, CLIENT_EMAIL VARCHAR(250) NOT NULL, LICENSE_CODE VARCHAR(250) NOT NULL, LCD VARCHAR(250) NOT NULL, LRD VARCHAR(250) NOT NULL, INSTALLATION_KEY VARCHAR(250) NOT NULL, INSTALLATION_HASH VARCHAR(250) NOT NULL, PLUGIN_NAME VARCHAR(250) NOT NULL, PRIMARY KEY (SETTING_ID)) DEFAULT CHARSET=utf8; INSERT INTO %APL_PLUGIN_DATABASE_TABLE% (ROOT_URL, CLIENT_EMAIL, LICENSE_CODE, LCD, LRD, INSTALLATION_KEY, INSTALLATION_HASH, PLUGIN_NAME) VALUES ('%ROOT_URL%', '%CLIENT_EMAIL%', '%LICENSE_CODE%', '%LCD%', '%LRD%', '%INSTALLATION_KEY%', '%INSTALLATION_HASH%', '%PLUGIN_NAME%');",
                'scheme_status' => 1,
            ],
            [
                'scheme_query' => "INSERT INTO %APL_PLUGIN_DATABASE_TABLE% (ROOT_URL, CLIENT_EMAIL, LICENSE_CODE, LCD, LRD, INSTALLATION_KEY, INSTALLATION_HASH, PLUGIN_NAME) VALUES ('%ROOT_URL%', '%CLIENT_EMAIL%', '%LICENSE_CODE%', '%LCD%', '%LRD%', '%INSTALLATION_KEY%', '%INSTALLATION_HASH%', '%PLUGIN_NAME%');",
                'scheme_status' => 1,
            ],
        ];

        foreach ($schemes as $scheme) {
            LicenseScheme::create($scheme);
        }
    }
}
