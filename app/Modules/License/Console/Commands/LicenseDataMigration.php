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

    private const CHUNK_SIZE = 200;

    private array $userMap = [];
    private array $productMap = [];
    private array $licenseMap = [];
    private array $versionMap = [];
    private int $skippedUsers = 0;

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
            if ($this->skippedUsers > 0) {
                $this->warn("  Licenses with no user assigned: {$this->skippedUsers}");
            }

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

        // Purge so the connection picks up the dynamic config
        DB::purge('license');
    }

    private function licenseDb(): \Illuminate\Database\ConnectionInterface
    {
        return DB::connection('license');
    }

    private function cleanDate(?string $date): ?string
    {
        if (! $date || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
            return null;
        }

        // Dates beyond MySQL timestamp range (year > 2037) can't be stored
        $year = (int) substr($date, 0, 4);
        if ($year > 2037 || $year < 1970) {
            return null;
        }

        return $date;
    }

    private function bulkInsert(string $table, array $rows, bool $ignoreDuplicates = false): void
    {
        if (empty($rows)) {
            return;
        }

        // Split into smaller batches to avoid max_allowed_packet limits
        foreach (array_chunk($rows, 50) as $chunk) {
            if ($ignoreDuplicates) {
                DB::table($table)->insertOrIgnore($chunk);
            } else {
                DB::table($table)->insert($chunk);
            }
        }
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
        $count = 0;

        $this->licenseDb()->table('afl_licenses')->orderBy('license_id')->chunk(self::CHUNK_SIZE, function ($licenses) use (&$count) {
            foreach ($licenses as $lic) {
                $newProductId = $this->productMap[$lic->product_id] ?? null;

                if (! $newProductId) {
                    $this->warn("  Skipping license #{$lic->license_id} - orphaned product #{$lic->product_id}");
                    continue;
                }

                if (empty($lic->license_code)) {
                    continue;
                }

                $newUserId = $this->userMap[$lic->client_id] ?? null;

                $newId = DB::table('licenses')->insertGetId([
                    'product_id' => $newProductId,
                    'user_id' => $newUserId,
                    'license_code' => $lic->license_code,
                    'license_order_number' => $lic->license_order_number,
                    'license_ip' => $lic->license_ip,
                    'license_domain' => $lic->license_domain,
                    'license_require_domain' => $lic->license_require_domain ?? 0,
                    'license_limit' => $lic->license_limit,
                    'license_date' => $this->cleanDate($lic->license_date),
                    'license_cancel_date' => $this->cleanDate($lic->license_cancel_date),
                    'license_expire_date' => $this->cleanDate($lic->license_expire_date),
                    'license_expire_email_date' => $this->cleanDate($lic->license_expire_email_date ?? null),
                    'license_updates_date' => $this->cleanDate($lic->license_updates_date),
                    'license_updates_email_date' => $this->cleanDate($lic->license_updates_email_date ?? null),
                    'license_support_date' => $this->cleanDate($lic->license_support_date),
                    'license_support_email_date' => $this->cleanDate($lic->license_support_email_date ?? null),
                    'license_comments' => $lic->license_comments,
                    'license_status' => $lic->license_status ?? 1,
                    'created_at' => $lic->created_at ?? now(),
                    'updated_at' => $lic->updated_at ?? now(),
                ]);
                $this->licenseMap[$lic->license_id] = $newId;
                $count++;

                if (! $newUserId) {
                    $this->skippedUsers++;
                }
            }
        });

        $this->info("  Migrated {$count} licenses");
    }

    private function migrateInstallations(): void
    {
        $count = 0;

        $this->licenseDb()->table('afl_installations')->orderBy('installation_id')->chunk(self::CHUNK_SIZE, function ($installations) use (&$count) {
            $batch = [];

            foreach ($installations as $inst) {
                $newProductId = $this->productMap[$inst->product_id] ?? null;
                if (! $newProductId) {
                    continue;
                }

                $batch[] = [
                    'product_id' => $newProductId,
                    'user_id' => $this->userMap[$inst->client_id] ?? null,
                    'license_code' => $inst->license_code,
                    'installation_ip' => $inst->installation_ip,
                    'installation_domain' => $inst->installation_domain,
                    'installation_date' => $this->cleanDate($inst->installation_date),
                    'installation_status' => $inst->installation_status ?? 1,
                    'installation_hash' => $inst->installation_hash,
                    'created_at' => $inst->created_at ?? now(),
                    'updated_at' => $inst->updated_at ?? now(),
                ];
            }

            if (! empty($batch)) {
                $this->bulkInsert('installations', $batch);
                $count += count($batch);
            }
        });

        $this->info("  Migrated {$count} installations");
    }

    private function migrateLicenseCallbacks(): void
    {
        $count = 0;

        $this->licenseDb()->table('afl_callbacks')->orderBy('callback_id')->chunk(self::CHUNK_SIZE, function ($callbacks) use (&$count) {
            $batch = [];

            foreach ($callbacks as $cb) {
                $newProductId = $this->productMap[$cb->product_id] ?? null;
                if (! $newProductId) {
                    continue;
                }

                $batch[] = [
                    'product_id' => $newProductId,
                    'user_id' => $this->userMap[$cb->client_id] ?? null,
                    'license_code' => $cb->license_code,
                    'callback_ip' => $cb->callback_ip,
                    'callback_domain' => $cb->callback_domain,
                    'callback_date_time' => $this->cleanDate($cb->callback_date_time),
                    'callback_status' => $cb->callback_status ?? 1,
                    'created_at' => $cb->created_at ?? now(),
                    'updated_at' => $cb->updated_at ?? now(),
                ];
            }

            if (! empty($batch)) {
                $this->bulkInsert('license_callbacks', $batch);
                $count += count($batch);
            }
        });

        $this->info("  Migrated {$count} callbacks");
    }

    private function migrateLicenseSchemes(): void
    {
        $count = 0;

        $this->licenseDb()->table('afl_license_schemes')->orderBy('scheme_id')->chunk(self::CHUNK_SIZE, function ($schemes) use (&$count) {
            $batch = [];

            foreach ($schemes as $scheme) {
                $batch[] = [
                    'scheme_query' => $scheme->scheme_query,
                    'scheme_status' => $scheme->scheme_status ?? 1,
                    'created_at' => $scheme->created_at ?? now(),
                    'updated_at' => $scheme->updated_at ?? now(),
                ];
            }

            if (! empty($batch)) {
                $this->bulkInsert('license_schemes', $batch);
                $count += count($batch);
            }
        });

        $this->info("  Migrated {$count} schemes");
    }

    private function migrateLicenseNotifications(): void
    {
        $n = $this->licenseDb()->table('afl_notifications')->first();
        if (! $n) {
            $this->info('  No notifications to migrate');

            return;
        }

        // Map source columns to the destination schema
        DB::table('license_notifications')->insert([
            'notification_product_not_found' => $n->notification_product_not_found ?? '',
            'notification_license_ok' => $n->notification_license_ok ?? '',
            'notification_license_not_found' => $n->notification_license_not_found ?? '',
            'notification_license_expired' => $n->notification_license_expired ?? '',
            'notification_license_suspended' => $n->notification_license_suspended ?? '',
            'notification_license_limit_exceeded' => $n->notification_license_limit ?? '',
            'notification_installation_ok' => '',
            'notification_installation_failed' => $n->notification_installation_not_found ?? '',
            'notification_updates_ok' => '',
            'notification_updates_not_found' => $n->notification_updates_expired ?? '',
            'notification_support_expired' => $n->notification_support_expired ?? '',
            'notification_domain_mismatch' => $n->notification_invalid_domain ?? '',
            'notification_ip_mismatch' => $n->notification_invalid_ip ?? '',
            'notification_invalid_request' => $n->notification_unknown_error ?? '',
            'notification_banned_host' => $n->notification_host_banned ?? '',
            'notification_connection_ok' => '',
            'notification_connection_failed' => '',
            'created_at' => $n->created_at ?? now(),
            'updated_at' => $n->updated_at ?? now(),
        ]);

        $this->info('  Migrated 1 notification record');
    }

    private function migrateBannedHosts(): void
    {
        $count = 0;

        $this->licenseDb()->table('afl_banned_hosts')->orderBy('banned_host_id')->chunk(self::CHUNK_SIZE, function ($hosts) use (&$count) {
            $batch = [];

            foreach ($hosts as $host) {
                $batch[] = [
                    'banned_host_ip' => $host->banned_host_ip,
                    'banned_host_comments' => $host->banned_host_comments ?? null,
                    'banned_host_date' => $this->cleanDate($host->banned_host_date ?? null),
                    'banned_host_blocks' => $host->banned_host_blocks ?? null,
                    'banned_host_last_block_date' => $this->cleanDate($host->banned_host_last_block_date ?? null),
                    'created_at' => $host->created_at ?? now(),
                    'updated_at' => $host->updated_at ?? now(),
                ];
            }

            if (! empty($batch)) {
                $this->bulkInsert('license_banned_hosts', $batch);
                $count += count($batch);
            }
        });

        $this->info("  Migrated {$count} banned hosts");
    }

    private function migrateWhitelistIps(): void
    {
        $count = 0;

        $this->licenseDb()->table('afl_whitelist_ips')->orderBy('whitelist_host_id')->chunk(self::CHUNK_SIZE, function ($ips) use (&$count) {
            $batch = [];

            foreach ($ips as $ip) {
                $batch[] = [
                    'whitelist_host_ip' => $ip->whitelist_host_ip,
                    'whitelist_host_comments' => $ip->whitelist_host_comments ?? null,
                    'created_at' => $ip->created_at ?? now(),
                    'updated_at' => $ip->updated_at ?? now(),
                ];
            }

            if (! empty($batch)) {
                $this->bulkInsert('license_whitelist_ips', $batch);
                $count += count($batch);
            }
        });

        $this->info("  Migrated {$count} whitelist IPs");
    }

    private function migrateLicenseReports(): void
    {
        $count = 0;

        $this->licenseDb()->table('afl_reports')->orderBy('report_id')->chunk(self::CHUNK_SIZE, function ($reports) use (&$count) {
            $batch = [];

            foreach ($reports as $report) {
                $newProductId = $this->productMap[$report->product_id] ?? null;
                if (! $newProductId) {
                    continue;
                }

                $batch[] = [
                    'product_id' => $newProductId,
                    'user_id' => $this->userMap[$report->account_id] ?? null,
                    'license_code' => $report->license_code,
                    'report_date_time' => $this->cleanDate($report->report_date_time),
                    'report_text' => $report->report_text,
                    'report_system' => $report->report_system ?? 0,
                    'report_status' => $report->report_status ?? 1,
                    'created_at' => $report->created_at ?? now(),
                    'updated_at' => $report->updated_at ?? now(),
                ];
            }

            if (! empty($batch)) {
                $this->bulkInsert('license_reports', $batch);
                $count += count($batch);
            }
        });

        $this->info("  Migrated {$count} reports");
    }

    private function migrateProductVersions(): void
    {
        $count = 0;

        $this->licenseDb()->table('afu_versions')->orderBy('version_id')->chunk(self::CHUNK_SIZE, function ($versions) use (&$count) {
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
                        'version_upgrade_file' => $ver->version_upgrade_file,
                        'version_changelog' => $ver->version_changelog,
                        'version_date' => $this->cleanDate($ver->version_date),
                        'version_expire_date' => $this->cleanDate($ver->version_expire_date),
                        'version_status' => $ver->version_status ?? 1,
                        'created_at' => $ver->created_at ?? now(),
                        'updated_at' => $ver->updated_at ?? now(),
                    ]);
                    $this->versionMap[$ver->version_id] = $newId;
                    $count++;
                } catch (\Exception $e) {
                    $this->warn("  Skipping version #{$ver->version_id}: ".$e->getMessage());
                }
            }
        });

        $this->info("  Migrated {$count} versions");
    }

    private function migrateVersionCallbacks(): void
    {
        $count = 0;

        $this->licenseDb()->table('afu_callbacks')->orderBy('callback_id')->chunk(self::CHUNK_SIZE, function ($callbacks) use (&$count) {
            $batch = [];

            foreach ($callbacks as $cb) {
                $newProductId = $this->productMap[$cb->product_id] ?? null;
                if (! $newProductId) {
                    continue;
                }

                $batch[] = [
                    'product_id' => $newProductId,
                    'version_id' => $this->versionMap[$cb->version_id] ?? null,
                    'callback_type' => $cb->callback_type,
                    'callback_ip' => $cb->callback_ip,
                    'callback_path' => $cb->callback_path,
                    'callback_date_time' => $this->cleanDate($cb->callback_date_time),
                    'callback_status' => $cb->callback_status ?? 1,
                    'created_at' => $cb->created_at ?? now(),
                    'updated_at' => $cb->updated_at ?? now(),
                ];
            }

            if (! empty($batch)) {
                $this->bulkInsert('version_callbacks', $batch);
                $count += count($batch);
            }
        });

        $this->info("  Migrated {$count} version callbacks");
    }

    private function migrateVersionInstallations(): void
    {
        $count = 0;

        $this->licenseDb()->table('afu_installations')->orderBy('installation_id')->chunk(self::CHUNK_SIZE, function ($installations) use (&$count) {
            $batch = [];

            foreach ($installations as $inst) {
                $newProductId = $this->productMap[$inst->product_id] ?? null;
                $newVersionId = $this->versionMap[$inst->version_id] ?? null;

                if (! $newProductId || ! $newVersionId) {
                    continue;
                }

                $batch[] = [
                    'product_id' => $newProductId,
                    'user_id' => null,
                    'version_id' => $newVersionId,
                    'installation_date' => $this->cleanDate($inst->installation_date),
                    'installation_status' => $inst->installation_status ?? 1,
                    'created_at' => $inst->created_at ?? now(),
                    'updated_at' => $inst->updated_at ?? now(),
                ];
            }

            if (! empty($batch)) {
                $this->bulkInsert('version_installations', $batch);
                $count += count($batch);
            }
        });

        $this->info("  Migrated {$count} version installations");
    }

    private function migrateVersionNotifications(): void
    {
        $n = $this->licenseDb()->table('afu_notifications')->first();
        if (! $n) {
            $this->info('  No version notifications to migrate');

            return;
        }

        // Map source columns to the destination schema
        DB::table('version_notifications')->insert([
            'notification_version_ok' => $n->notification_operation_ok ?? '',
            'notification_version_not_found' => $n->notification_version_not_found ?? '',
            'notification_update_available' => '',
            'notification_no_update' => $n->notification_product_no_versions ?? '',
            'notification_update_failed' => $n->notification_unknown_error ?? '',
            'notification_invalid_request' => $n->notification_invalid_parameter ?? '',
            'notification_banned_host' => $n->notification_host_banned ?? '',
            'notification_connection_ok' => '',
            'notification_connection_failed' => '',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->info('  Migrated 1 version notification record');
    }

    private function migrateLicensePlugins(): void
    {
        $count = 0;

        $this->licenseDb()->table('license_plugins')->orderBy('id')->chunk(self::CHUNK_SIZE, function ($plugins) use (&$count) {
            $batch = [];

            foreach ($plugins as $plugin) {
                $newLicenseId = $this->licenseMap[$plugin->license_id] ?? null;
                $newProductId = $this->productMap[$plugin->product_id] ?? null;

                if (! $newLicenseId || ! $newProductId) {
                    continue;
                }

                $batch[] = [
                    'license_id' => $newLicenseId,
                    'product_id' => $newProductId,
                    'created_at' => $plugin->created_at ?? now(),
                    'updated_at' => $plugin->updated_at ?? now(),
                ];
            }

            if (! empty($batch)) {
                $this->bulkInsert('license_plugins', $batch, true);
                $count += count($batch);
            }
        });

        $this->info("  Migrated {$count} plugins");
    }

    private function migrateLicenseOptions(): void
    {
        $count = 0;

        $this->licenseDb()->table('license_options')->orderBy('id')->chunk(self::CHUNK_SIZE, function ($options) use (&$count) {
            $batch = [];

            foreach ($options as $opt) {
                $newLicenseId = $this->licenseMap[$opt->license_id] ?? null;
                $newProductId = $this->productMap[$opt->product_id] ?? null;

                if (! $newLicenseId || ! $newProductId) {
                    continue;
                }

                $batch[] = [
                    'license_id' => $newLicenseId,
                    'product_id' => $newProductId,
                    'option_group' => $opt->option_group ?? '',
                    'option_name' => $opt->option_name ?? '',
                    'key' => $opt->key ?? '',
                    'value' => $opt->value ?? '',
                    'created_at' => $opt->created_at ?? now(),
                    'updated_at' => $opt->updated_at ?? now(),
                ];
            }

            if (! empty($batch)) {
                $this->bulkInsert('license_options', $batch);
                $count += count($batch);
            }
        });

        $this->info("  Migrated {$count} options");
    }

    private function migrateInstallationLogs(): void
    {
        $count = 0;

        $this->licenseDb()->table('installation_logs')->orderBy('id')->chunk(self::CHUNK_SIZE, function ($logs) use (&$count) {
            $batch = [];

            foreach ($logs as $log) {
                $batch[] = [
                    'license_code' => $log->license_code,
                    'version_number' => $log->version_number ?? null,
                    'installation_ip' => $log->installation_ip,
                    'installation_domain' => $log->installation_domain ?? '',
                    'installation_last_active_date' => $this->cleanDate($log->installation_last_active_date),
                    'installation_status' => $log->installation_status ?? 1,
                    'created_at' => $log->created_at ?? now(),
                    'updated_at' => $log->updated_at ?? now(),
                ];
            }

            if (! empty($batch)) {
                $this->bulkInsert('installation_logs', $batch);
                $count += count($batch);
            }
        });

        $this->info("  Migrated {$count} installation logs");
    }

    private function updateProductColumns(): void
    {
        // Pre-load afu_products keyed by SKU to avoid N+1 queries
        $afuProducts = $this->licenseDb()->table('afu_products')->get()->keyBy('product_sku');
        $count = 0;

        $this->licenseDb()->table('afl_products')->whereNull('deleted_at')->orderBy('product_id')->chunk(self::CHUNK_SIZE, function ($licenseProducts) use ($afuProducts, &$count) {
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

                $afuProduct = $afuProducts[$lp->product_sku] ?? null;

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
        });

        $this->info("  Updated {$count} products with license-specific columns");
    }
}
