<?php

namespace App\Modules\License\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LicenseDataMigration extends Command
{
    protected $signature = 'license:migrate-data 
        {--host= : License database host}
        {--port= : License database port}
        {--database= : License database name}
        {--username= : License database username}
        {--password= : License database password}
        {--socket= : License database socket}
        {--fresh : Truncate all license tables before migration}';
    protected $description = 'Migrate data from the external license database into the billing database';

    private array $userMap = [];
    private array $productMap = [];
    private array $licenseMap = [];
    private array $versionMap = [];

    public function handle(): int
    {
        // Dynamically configure the license database connection
        $this->configureLicenseConnection();

        if ($this->option('fresh')) {
            $this->warn('Truncating existing license tables...');
            $this->truncateLicenseTables();
        }

        $this->info('Running license system data migration...');

        // Verify license DB connection is configured
        if (! config('database.connections.license.database')) {
            $this->error('License database connection not configured. Provide --database option');

            return Command::FAILURE;
        }

        try {
            // Step 1: Build user mapping (match by email)
            $this->info('Step 1: Building user mapping...');
            $this->buildUserMapping();
            $this->info('  Mapped '.count($this->userMap).' users');

            // Step 2: Build product mapping (match by SKU)
            $this->info('Step 2: Building product mapping...');
            $this->buildProductMapping();
            $this->info('  Mapped '.count($this->productMap).' products');

            // Step 3: Migrate licenses
            $this->info('Step 3: Migrating licenses...');
            $this->migrateLicenses();

            // Step 4: Migrate installations
            $this->info('Step 4: Migrating installations...');
            $this->migrateInstallations();

            // Step 5: Migrate license callbacks
            $this->info('Step 5: Migrating license callbacks...');
            $this->migrateLicenseCallbacks();

            // Step 6: Migrate standalone tables (no FK remapping needed)
            $this->info('Step 6: Migrating license schemes...');
            $this->migrateLicenseSchemes();

            $this->info('Step 7: Migrating license notifications...');
            $this->migrateLicenseNotifications();

            $this->info('Step 8: Migrating banned hosts...');
            $this->migrateBannedHosts();

            $this->info('Step 9: Migrating whitelist IPs...');
            $this->migrateWhitelistIps();

            // Step 10: Migrate reports
            $this->info('Step 10: Migrating license reports...');
            $this->migrateLicenseReports();

            // Step 12: Migrate product versions (build version map)
            $this->info('Step 12: Migrating product versions...');
            $this->migrateProductVersions();

            // Step 13: Migrate version callbacks (uses version map)
            $this->info('Step 13: Migrating version callbacks...');
            $this->migrateVersionCallbacks();

            // Step 14: Migrate version installations
            $this->info('Step 14: Migrating version installations...');
            $this->migrateVersionInstallations();

            // Step 15: Migrate version notifications
            $this->info('Step 15: Migrating version notifications...');
            $this->migrateVersionNotifications();

            // Step 16: Migrate license plugins (uses license map)
            $this->info('Step 16: Migrating license plugins...');
            $this->migrateLicensePlugins();

            // Step 17: Migrate license options (uses license map)
            $this->info('Step 17: Migrating license options...');
            $this->migrateLicenseOptions();

            // Step 18: Migrate installation logs
            $this->info('Step 18: Migrating installation logs...');
            $this->migrateInstallationLogs();

            // Step 19: Update product columns from license products
            $this->info('Step 19: Updating product columns...');
            $this->updateProductColumns();

            $this->info('');
            $this->info('Data migration completed successfully!');
            $this->info('  Users mapped: '.count($this->userMap));
            $this->info('  Products mapped: '.count($this->productMap));
            $this->info('  Licenses migrated: '.count($this->licenseMap));
            $this->info('  Versions migrated: '.count($this->versionMap));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Migration failed: '.$e->getMessage());
            Log::error('License data migration failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }

    private function configureLicenseConnection(): void
    {
        $host = $this->option('host') ?: config('database.connections.mysql.host', 'localhost');
        $port = $this->option('port') ?: config('database.connections.mysql.port', '');
        $database = $this->option('database') ?: config('database.connections.mysql.database', '');
        $username = $this->option('username') ?: config('database.connections.mysql.username', 'root');
        $password = $this->option('password') ?: config('database.connections.mysql.password', '');
        $socket = $this->option('socket') ?: config('database.connections.mysql.unix_socket', '');

        Config::set('database.connections.license', [
            'driver' => 'mysql',
            'host' => $host,
            'port' => $port,
            'database' => $database,
            'username' => $username,
            'password' => $password,
            'unix_socket' => $socket,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => config('database.connections.mysql.engine', null),
        ]);
    }

    private function licenseDb(): \Illuminate\Database\ConnectionInterface
    {
        // Purge existing connection so it picks up the dynamic config
        DB::purge('license');

        return DB::connection('license');
    }

    private function truncateLicenseTables(): void
    {
        $tables = [
            'installation_logs', 'license_options', 'license_plugins',
            'version_installations', 'version_callbacks',
            'product_versions', 'license_reports', 'license_whitelist_ips',
            'license_banned_hosts', 'license_notifications',
            'version_notifications', 'license_schemes',
            'license_callbacks', 'installations', 'licenses',
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach ($tables as $table) {
            if (\Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    // =========================================================================
    // MAPPING BUILDERS
    // =========================================================================

    private function buildUserMapping(): void
    {
        $billingUsers = DB::table('users')->get()->keyBy('email');
        $licenseUsers = $this->licenseDb()->table('users')->get();

        foreach ($licenseUsers as $lu) {
            $email = $lu->client_email ?? null;
            if (! $email) {
                continue;
            }

            if (isset($billingUsers[$email])) {
                $this->userMap[$lu->client_id] = $billingUsers[$email]->id;
            } else {
                $newId = DB::table('users')->insertGetId([
                    'user_name' => $lu->client_username ?? $email,
                    'first_name' => $lu->client_fname ?? '',
                    'last_name' => $lu->client_lname ?? '',
                    'email' => $email,
                    'password' => $lu->client_password ?? bcrypt('changeme'),
                    'role' => $lu->client_role === 'admin' ? 'admin' : 'client',
                    'active' => ($lu->client_status ?? 1) ? 1 : 0,
                    'mobile' => $lu->client_mobile ?? null,
                    'timezone_id' => $lu->client_timezone_id ?? null,
                    'profile_pic' => $lu->client_profile_pic ?? null,
                    'address' => $lu->client_address ?? null,
                    'company' => $lu->client_organization ?? null,
                    'country' => $lu->client_iso2 ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->userMap[$lu->client_id] = $newId;
                $this->warn("  Created new user: {$email} (ID: {$newId})");
            }
        }
    }

    private function buildProductMapping(): void
    {
        $billingProducts = DB::table('products')->get()->keyBy('product_sku');
        $licenseProducts = $this->licenseDb()->table('afl_products')->whereNull('deleted_at')->get();

        foreach ($licenseProducts as $lp) {
            $sku = $lp->product_sku ?? null;

            if ($sku && isset($billingProducts[$sku])) {
                $this->productMap[$lp->product_id] = $billingProducts[$sku]->id;
            } else {
                $matched = DB::table('products')->where('name', $lp->product_title)->first();
                if ($matched) {
                    $this->productMap[$lp->product_id] = $matched->id;
                } else {
                    $newId = DB::table('products')->insertGetId([
                        'name' => $lp->product_title,
                        'description' => $lp->product_description ?? '',
                        'product_sku' => $sku,
                        'status' => $lp->product_status ?? 1,
                        'created_at' => $lp->created_at ?? now(),
                        'updated_at' => now(),
                    ]);
                    $this->productMap[$lp->product_id] = $newId;
                    $this->warn("  Created new product: {$lp->product_title} (ID: {$newId})");
                }
            }
        }
    }

    // =========================================================================
    // TABLE MIGRATIONS
    // =========================================================================

    private function migrateLicenses(): void
    {
        $licenses = $this->licenseDb()->table('afl_licenses')->get();
        $count = 0;

        foreach ($licenses as $lic) {
            $newUserId = $this->userMap[$lic->client_id] ?? null;
            $newProductId = $this->productMap[$lic->product_id] ?? null;

            if (! $newProductId) {
                $this->warn("  Skipping license #{$lic->license_id} - orphaned product #{$lic->product_id}");
                continue;
            }

            $newId = DB::table('licenses')->insertGetId([
                'product_id' => $newProductId,
                'user_id' => $newUserId,
                'license_code' => $lic->license_code,
                'license_order_number' => $lic->license_order_number,
                'license_ip' => $lic->license_ip,
                'license_domain' => $lic->license_domain,
                'license_require_domain' => $lic->license_require_domain ?? 0,
                'license_limit' => $lic->license_limit,
                'license_date' => $lic->license_date,
                'license_cancel_date' => $lic->license_cancel_date,
                'license_expire_date' => $lic->license_expire_date,
                'license_expire_email_date' => $lic->license_expire_email_date ?? null,
                'license_updates_date' => $lic->license_updates_date,
                'license_updates_email_date' => $lic->license_updates_email_date ?? null,
                'license_support_date' => $lic->license_support_date,
                'license_support_email_date' => $lic->license_support_email_date ?? null,
                'license_comments' => $lic->license_comments,
                'license_status' => $lic->license_status ?? 1,
                'created_at' => $lic->created_at ?? now(),
                'updated_at' => $lic->updated_at ?? now(),
            ]);
            $this->licenseMap[$lic->license_id] = $newId;
            $count++;
        }

        $this->info("  Migrated {$count} licenses");
    }

    private function migrateInstallations(): void
    {
        $installations = $this->licenseDb()->table('afl_installations')->get();
        $count = 0;

        foreach ($installations as $inst) {
            $newUserId = $this->userMap[$inst->client_id] ?? null;
            $newProductId = $this->productMap[$inst->product_id] ?? null;

            if (! $newProductId) {
                continue;
            }

            DB::table('installations')->insert([
                'product_id' => $newProductId,
                'user_id' => $newUserId ?? 0,
                'license_code' => $inst->license_code,
                'installation_ip' => $inst->installation_ip,
                'installation_domain' => $inst->installation_domain,
                'installation_disable_ip_verification' => $inst->installation_disable_ip_verification ?? 0,
                'installation_date' => $inst->installation_date,
                'installation_status' => $inst->installation_status ?? 1,
                'installation_hash' => $inst->installation_hash,
                'created_at' => $inst->created_at ?? now(),
                'updated_at' => $inst->updated_at ?? now(),
            ]);
            $count++;
        }

        $this->info("  Migrated {$count} installations");
    }

    private function migrateLicenseCallbacks(): void
    {
        $callbacks = $this->licenseDb()->table('afl_callbacks')->get();
        $count = 0;

        foreach ($callbacks as $cb) {
            $newProductId = $this->productMap[$cb->product_id] ?? null;
            if (! $newProductId) {
                continue;
            }

            DB::table('license_callbacks')->insert([
                'product_id' => $newProductId,
                'client_id' => $this->userMap[$cb->client_id] ?? $cb->client_id,
                'license_code' => $cb->license_code,
                'callback_ip' => $cb->callback_ip,
                'callback_domain' => $cb->callback_domain,
                'callback_date_time' => $cb->callback_date_time,
                'callback_status' => $cb->callback_status ?? 1,
                'created_at' => $cb->created_at ?? now(),
                'updated_at' => $cb->updated_at ?? now(),
            ]);
            $count++;
        }

        $this->info("  Migrated {$count} callbacks");
    }

    private function migrateLicenseSchemes(): void
    {
        $schemes = $this->licenseDb()->table('afl_license_schemes')->get();
        $count = 0;

        foreach ($schemes as $scheme) {
            DB::table('license_schemes')->insert([
                'scheme_query' => $scheme->scheme_query,
                'scheme_status' => $scheme->scheme_status ?? 1,
                'created_at' => $scheme->created_at ?? now(),
                'updated_at' => $scheme->updated_at ?? now(),
            ]);
            $count++;
        }

        $this->info("  Migrated {$count} schemes");
    }

    private function migrateLicenseNotifications(): void
    {
        $n = $this->licenseDb()->table('afl_notifications')->first();
        if (! $n) {
            $this->info('  No notifications to migrate');

            return;
        }

        // Map columns exactly as they exist in the original afl_notifications table
        DB::table('license_notifications')->insert([
            'notification_product_not_found' => $n->notification_product_not_found ?? '',
            'notification_product_inactive' => $n->notification_product_inactive ?? '',
            'notification_license_ok' => $n->notification_license_ok ?? '',
            'notification_license_not_found' => $n->notification_license_not_found ?? '',
            'notification_invalid_ip' => $n->notification_invalid_ip ?? '',
            'notification_invalid_domain' => $n->notification_invalid_domain ?? '',
            'notification_domain_required' => $n->notification_domain_required ?? '',
            'notification_domain_in_use' => $n->notification_domain_in_use ?? '',
            'notification_license_suspended' => $n->notification_license_suspended ?? '',
            'notification_license_expired' => $n->notification_license_expired ?? '',
            'notification_updates_expired' => $n->notification_updates_expired ?? '',
            'notification_support_expired' => $n->notification_support_expired ?? '',
            'notification_license_cancelled' => $n->notification_license_cancelled ?? '',
            'notification_license_limit' => $n->notification_license_limit ?? '',
            'notification_installation_not_found' => $n->notification_installation_not_found ?? '',
            'notification_invalid_signature' => $n->notification_invalid_signature ?? '',
            'notification_host_banned' => $n->notification_host_banned ?? '',
            'notification_unknown_error' => $n->notification_unknown_error ?? '',
            'created_at' => $n->created_at ?? now(),
            'updated_at' => $n->updated_at ?? now(),
        ]);

        $this->info('  Migrated 1 notification record');
    }

    private function migrateBannedHosts(): void
    {
        $hosts = $this->licenseDb()->table('afl_banned_hosts')->get();
        $count = 0;

        foreach ($hosts as $host) {
            DB::table('license_banned_hosts')->insert([
                'banned_host_ip' => $host->banned_host_ip,
                'banned_host_comments' => $host->banned_host_comments ?? null,
                'banned_host_date' => $host->banned_host_date ?? null,
                'banned_host_blocks' => $host->banned_host_blocks ?? null,
                'banned_host_last_block_date' => $host->banned_host_last_block_date ?? null,
                'created_at' => $host->created_at ?? now(),
                'updated_at' => $host->updated_at ?? now(),
            ]);
            $count++;
        }

        $this->info("  Migrated {$count} banned hosts");
    }

    private function migrateWhitelistIps(): void
    {
        $ips = $this->licenseDb()->table('afl_whitelist_ips')->get();
        $count = 0;

        foreach ($ips as $ip) {
            DB::table('license_whitelist_ips')->insert([
                'whitelist_host_ip' => $ip->whitelist_host_ip,
                'whitelist_host_comments' => $ip->whitelist_host_comments ?? null,
                'created_at' => $ip->created_at ?? now(),
                'updated_at' => $ip->updated_at ?? now(),
            ]);
            $count++;
        }

        $this->info("  Migrated {$count} whitelist IPs");
    }

    private function migrateLicenseReports(): void
    {
        $reports = $this->licenseDb()->table('afl_reports')->get();
        $count = 0;

        foreach ($reports as $report) {
            $newUserId = $this->userMap[$report->account_id] ?? 0;
            $newProductId = $this->productMap[$report->product_id] ?? null;

            if (! $newProductId) {
                continue;
            }

            DB::table('license_reports')->insert([
                'product_id' => $newProductId,
                'user_id' => $newUserId,
                'license_code' => $report->license_code,
                'report_date_time' => $report->report_date_time,
                'report_text' => $report->report_text,
                'report_system' => $report->report_system ?? 0,
                'report_status' => $report->report_status ?? 1,
                'created_at' => $report->created_at ?? now(),
                'updated_at' => $report->updated_at ?? now(),
            ]);
            $count++;
        }

        $this->info("  Migrated {$count} reports");
    }

    private function migrateProductVersions(): void
    {
        $versions = $this->licenseDb()->table('afu_versions')->get();
        $count = 0;

        foreach ($versions as $ver) {
            $newProductId = $this->productMap[$ver->product_id] ?? null;
            if (! $newProductId) {
                continue;
            }

            try {
                $newId = DB::table('product_versions')->insertGetId([
                    'product_id' => $newProductId,
                    'version_number' => $ver->version_number,
                    'version_install_file' => $ver->version_install_file,
                    'version_install_query' => $ver->version_install_query,
                    'version_raw_install_query' => $ver->version_raw_install_query,
                    'version_upgrade_file' => $ver->version_upgrade_file,
                    'version_upgrade_query' => $ver->version_upgrade_query,
                    'version_raw_upgrade_query' => $ver->version_raw_upgrade_query,
                    'version_install_limit' => $ver->version_install_limit,
                    'version_install_count' => $ver->version_install_count ?? 0,
                    'version_upgrade_limit' => $ver->version_upgrade_limit,
                    'version_upgrade_count' => $ver->version_upgrade_count ?? 0,
                    'version_changelog' => $ver->version_changelog,
                    'version_date' => $ver->version_date,
                    'version_expire_date' => $ver->version_expire_date,
                    'version_comments' => $ver->version_comments,
                    'version_status' => $ver->version_status ?? 1,
                    'expired' => $ver->expired ?? null,
                    'created_at' => $ver->created_at ?? now(),
                    'updated_at' => $ver->updated_at ?? now(),
                ]);
                $this->versionMap[$ver->version_id] = $newId;
                $count++;
            } catch (\Exception $e) {
                $this->warn("  Skipping version #{$ver->version_id}: ".$e->getMessage());
            }
        }

        $this->info("  Migrated {$count} versions");
    }

    private function migrateVersionCallbacks(): void
    {
        $callbacks = $this->licenseDb()->table('afu_callbacks')->get();
        $count = 0;

        foreach ($callbacks as $cb) {
            $newProductId = $this->productMap[$cb->product_id] ?? null;
            $newVersionId = $this->versionMap[$cb->version_id] ?? null;

            if (! $newProductId) {
                continue;
            }

            DB::table('version_callbacks')->insert([
                'product_id' => $newProductId,
                'version_id' => $newVersionId,
                'callback_type' => $cb->callback_type,
                'callback_ip' => $cb->callback_ip,
                'callback_path' => $cb->callback_path,
                'callback_date_time' => $cb->callback_date_time,
                'callback_status' => $cb->callback_status ?? 1,
                'created_at' => $cb->created_at ?? now(),
                'updated_at' => $cb->updated_at ?? now(),
            ]);
            $count++;
        }

        $this->info("  Migrated {$count} version callbacks");
    }

    private function migrateVersionInstallations(): void
    {
        $installations = $this->licenseDb()->table('afu_installations')->get();
        $count = 0;

        foreach ($installations as $inst) {
            $newProductId = $this->productMap[$inst->product_id] ?? null;
            $newVersionId = $this->versionMap[$inst->version_id] ?? null;

            if (! $newProductId || ! $newVersionId) {
                continue;
            }

            DB::table('version_installations')->insert([
                'product_id' => $newProductId,
                'version_id' => $newVersionId,
                'installation_ip' => $inst->installation_ip,
                'installation_path' => $inst->installation_path ?? null,
                'installation_date' => $inst->installation_date,
                'installation_status' => $inst->installation_status ?? 1,
                'created_at' => $inst->created_at ?? now(),
                'updated_at' => $inst->updated_at ?? now(),
            ]);
            $count++;
        }

        $this->info("  Migrated {$count} version installations");
    }

    private function migrateVersionNotifications(): void
    {
        $n = $this->licenseDb()->table('afu_notifications')->first();
        if (! $n) {
            $this->info('  No version notifications to migrate');

            return;
        }

        // Map columns exactly as they exist in afu_notifications
        DB::table('version_notifications')->insert([
            'notification_operation_ok' => $n->notification_operation_ok ?? '',
            'notification_product_not_found' => $n->notification_product_not_found ?? '',
            'notification_product_inactive' => $n->notification_product_inactive ?? '',
            'notification_product_no_versions' => $n->notification_product_no_versions ?? '',
            'notification_version_not_found' => $n->notification_version_not_found ?? '',
            'notification_version_inactive' => $n->notification_version_inactive ?? '',
            'notification_version_expired' => $n->notification_version_expired ?? '',
            'notification_install_limit_reached' => $n->notification_install_limit_reached ?? '',
            'notification_upgrade_limit_reached' => $n->notification_upgrade_limit_reached ?? '',
            'notification_install_archive_not_found' => $n->notification_install_archive_not_found ?? '',
            'notification_install_query_not_found' => $n->notification_install_query_not_found ?? '',
            'notification_upgrade_archive_not_found' => $n->notification_upgrade_archive_not_found ?? '',
            'notification_upgrade_query_not_found' => $n->notification_upgrade_query_not_found ?? '',
            'notification_raw_install_query_not_found' => $n->notification_raw_install_query_not_found ?? '',
            'notification_raw_upgrade_query_not_found' => $n->notification_raw_upgrade_query_not_found ?? '',
            'notification_installation_not_verified' => $n->notification_installation_not_verified ?? '',
            'notification_invalid_parameter' => $n->notification_invalid_parameter ?? '',
            'notification_invalid_signature' => $n->notification_invalid_signature ?? '',
            'notification_host_banned' => $n->notification_host_banned ?? '',
            'notification_unknown_error' => $n->notification_unknown_error ?? '',
        ]);

        $this->info('  Migrated 1 version notification record');
    }

    private function migrateLicensePlugins(): void
    {
        $plugins = $this->licenseDb()->table('license_plugins')->get();
        $count = 0;

        foreach ($plugins as $plugin) {
            $newLicenseId = $this->licenseMap[$plugin->license_id] ?? null;
            $newProductId = $this->productMap[$plugin->product_id] ?? null;

            if (! $newLicenseId || ! $newProductId) {
                continue;
            }

            DB::table('license_plugins')->insert([
                'license_id' => $newLicenseId,
                'product_id' => $newProductId,
                'created_at' => $plugin->created_at ?? now(),
                'updated_at' => $plugin->updated_at ?? now(),
            ]);
            $count++;
        }

        $this->info("  Migrated {$count} plugins");
    }

    private function migrateLicenseOptions(): void
    {
        $options = $this->licenseDb()->table('license_options')->get();
        $count = 0;

        foreach ($options as $opt) {
            $newLicenseId = $this->licenseMap[$opt->license_id] ?? null;
            $newProductId = $this->productMap[$opt->product_id] ?? null;

            if (! $newLicenseId || ! $newProductId) {
                continue;
            }

            DB::table('license_options')->insert([
                'license_id' => $newLicenseId,
                'product_id' => $newProductId,
                'option_group' => $opt->option_group ?? '',
                'option_name' => $opt->option_name ?? '',
                'key' => $opt->key ?? '',
                'value' => $opt->value ?? '',
                'created_at' => $opt->created_at ?? now(),
                'updated_at' => $opt->updated_at ?? now(),
            ]);
            $count++;
        }

        $this->info("  Migrated {$count} options");
    }

    private function migrateInstallationLogs(): void
    {
        $logs = $this->licenseDb()->table('installation_logs')->get();
        $count = 0;

        foreach ($logs as $log) {
            DB::table('installation_logs')->insert([
                'license_code' => $log->license_code,
                'version_number' => $log->version_number ?? null,
                'installation_ip' => $log->installation_ip,
                'installation_domain' => $log->installation_domain ?? '',
                'installation_last_active_date' => $log->installation_last_active_date,
                'installation_status' => $log->installation_status ?? 1,
                'created_at' => $log->created_at ?? now(),
                'updated_at' => $log->updated_at ?? now(),
            ]);
            $count++;
        }

        $this->info("  Migrated {$count} installation logs");
    }

    private function updateProductColumns(): void
    {
        $licenseProducts = $this->licenseDb()->table('afl_products')->whereNull('deleted_at')->get();
        $count = 0;

        foreach ($licenseProducts as $lp) {
            $billingProductId = $this->productMap[$lp->product_id] ?? null;
            if (! $billingProductId) {
                continue;
            }

            $updateData = array_filter([
                'product_url_homepage' => $lp->product_url_homepage,
                'product_url_download' => $lp->product_url_download,
                'product_envato_id' => $lp->product_envato_id,
            ], fn ($v) => $v !== null);

            // Also check afu_products for product_key and max_active_versions
            $afuProduct = $this->licenseDb()->table('afu_products')
                ->where('product_sku', $lp->product_sku)
                ->first();

            if ($afuProduct) {
                if ($afuProduct->product_key) {
                    $updateData['product_key'] = $afuProduct->product_key;
                }
                if ($afuProduct->product_max_active_versions) {
                    $updateData['product_max_active_versions'] = $afuProduct->product_max_active_versions;
                }
            }

            if (! empty($updateData)) {
                DB::table('products')->where('id', $billingProductId)->update($updateData);
                $count++;
            }
        }

        $this->info("  Updated {$count} products with license-specific columns");
    }
}
