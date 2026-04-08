# License Module

This module handles all license-related functionality that was previously in a separate license application. It has been merged into the billing system to eliminate inter-app API calls.

## Directory Structure

```
app/Modules/License/
├── Controllers/
│   ├── CallbackController.php      # External client callbacks (APL/AFU)
│   └── LicenseAdminController.php  # Admin license management
├── Models/                         # 16 Eloquent models for license tables
├── Services/
│   ├── LicenseService.php          # License CRUD, search, deactivation
│   ├── InstallationService.php     # Installation tracking & logs
│   ├── VersionService.php          # Version management & updates
│   └── CallbackService.php         # Process external callbacks
├── Helpers/                        # Utility functions
├── Routes/
│   └── license.php                 # Module routes
├── Database/
│   └── Migrations/
│       ├── 2026_04_08_000001_add_license_columns_to_products_table.php
│       ├── 2026_04_08_000002_create_license_system_tables.php
│       └── 2026_04_08_000003_remove_deprecated_api_columns.php
└── LicenseServiceProvider.php      # Module registration
```

## Migrations

The migrations are automatically loaded by the `LicenseServiceProvider`. When you run `php artisan migrate`, these will be included.

### Migration 1: Add License Columns to Products
Adds columns from the license system's `afl_products` and `afu_products` tables:
- `product_url_homepage`
- `product_url_download`
- `product_envato_id`
- `product_key`
- `product_max_active_versions`

### Migration 2: Create License System Tables
Creates 16 new tables:
- `licenses` - Core license records
- `installations` - Where licenses are installed
- `license_callbacks` - License verification event logs
- `license_schemes` - SQL schemas for client-side license storage
- `license_notifications` - Response templates for license checks
- `license_banned_hosts` - IP ban list
- `license_whitelist_ips` - IP whitelist
- `license_reports` - Audit/piracy reports
- `product_versions` - Product version releases
- `version_callbacks` - Update callback logs
- `version_installations` - Update installation tracking
- `version_notifications` - Response templates for update checks
- `license_plugins` - Plugin-license associations
- `license_options` - Key-value options
- `installation_logs` - Detailed installation activity logs

### Migration 3: Remove Deprecated API Columns
Removes inter-app OAuth credentials:
- From `api_keys`: `license_api_secret`, `license_api_url`, `license_client_id`, `license_client_secret`, `license_grant_type`
- From `status_settings`: `license_status`

## Data Migration

To migrate data from the external license database:

1. Configure the license database connection in `.env`:
```env
LICENSE_DB_HOST=localhost
LICENSE_DB_PORT=3306
LICENSE_DB_DATABASE=license_db_name
LICENSE_DB_USERNAME=root
LICENSE_DB_PASSWORD=your_password
```

2. Run the migration command:
```bash
php artisan license:migrate-data
```

This will:
- Build user mapping (match by email, insert new users)
- Build product mapping (match by SKU, insert new products)
- Migrate all license-related tables with proper FK remapping
- Log any orphaned records or conflicts

## Routes

### External Callbacks (APL - Active Product License)
- `POST /apl_callbacks/license_verify.php` - License verification
- `POST /apl_callbacks/license_install.php` - Installation registration
- `POST /apl_callbacks/license_scheme.php` - License scheme query
- `POST /apl_callbacks/connection_test.php` - Connection test

### External Callbacks (AFU - Auto File Update)
- `POST /aus_callbacks/download_file.php` - Version download
- `POST /aus_callbacks/get_versions.php` - Update check
- `POST /aus_callbacks/fetch_query.php` - Version fetch

### Admin Routes (requires auth)
- `POST /admin/license/add` - Create license
- `POST /admin/license/edit` - Update license
- `POST /admin/license/deactivate` - Deactivate license
- `POST /admin/license/reactivate` - Reactivate license
- `POST /admin/license/search` - Search licenses/products/users
- `POST /admin/license/syncAddonLicense` - Sync addon licenses
- `POST /admin/license/updateLicenseCode` - Update license code
- `POST /admin/license/getInstallationLogs` - Get installation logs
- `POST /admin/license/updateInstallationLogs` - Update installation logs
- `GET /admin/license/{licenseCode}` - Get license by code
- `GET /admin/license/{licenseCode}/installations` - Get installations
- `GET /admin/viewApiKeys` - View license API keys
- `GET /admin/getProductIdbyKey` - Get product ID by key

## Usage

### In Controllers

```php
use App\Modules\License\Services\LicenseService;
use App\Modules\License\Services\InstallationService;

class YourController extends Controller
{
    public function __construct(
        protected LicenseService $licenseService,
        protected InstallationService $installationService
    ) {}

    public function createLicense(Request $request)
    {
        $license = $this->licenseService->create([
            'product_id' => $request->product_id,
            'user_id' => $request->user_id,
            'license_code' => $request->license_code,
            'license_expire_date' => $request->expire_date,
            // ...
        ]);
        
        return response()->json($license);
    }
}
```

### Using Models Directly

```php
use App\Modules\License\Models\License;
use App\Modules\License\Models\Installation;

// Find active licenses for a product
$licenses = License::where('product_id', $productId)
    ->active()
    ->with('user')
    ->get();

// Count installations
$count = Installation::where('license_code', $licenseCode)
    ->where('installation_status', 'active')
    ->count();
```

## Architecture

The module follows a service-layer pattern:

- **Models**: Data access, relationships, scopes, casts
- **Services**: Business logic, validation, orchestration
- **Controllers**: HTTP layer only, delegate to services
- **CallbackService**: Processes external license verification requests

## Notes

- All cURL/OAuth calls to the old license API have been removed
- The old `LicenseService.php` (OAuth client) has been deleted
- External client installations still work via callback endpoints
- DNS should be updated to point old license domain to billing app
