<?php

namespace App\License\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class LicenseDataMigration extends Command
{
    protected $signature = 'license:migrate-data
        {--host= : License database host}
        {--port= : License database port}
        {--database= : License database name}
        {--username= : License database username}
        {--password= : License database password}
        {--socket= : License database socket}
        {--sql-file= : Path to a SQL dump file; imports into a temporary DB then migrates from it}
        {--fresh : Truncate all license tables before migration}
        {--include-codes= : Comma-separated license codes to include even without an order mapping}';

    protected $description = 'Migrate data from the external license database into the billing database';

    private const CHUNK_SIZE = 200;
    private const INSERT_BATCH_SIZE = 50;

    private array $productMap = [];
    private array $licenseMap = [];
    private array $versionMap = [];
    private array $licenseCodeUserMap = [];
    private array $includedCodes = [];
    private int $skippedUsers = 0;
    private int $resolvedViaOrder = 0;
    private ?string $tempDatabase = null;
    private string $now;

    public function handle(): int
    {
        $this->now = now()->toDateTimeString();

        try {
            $this->setup();

            if (! config('database.connections.license.database')) {
                $this->error('License database connection not configured. Provide --database or --sql-file option.');

                return Command::FAILURE;
            }

            $this->info('Running license system data migration...');
            $this->newLine();

            DB::transaction(fn () => $this->executeSteps());

            $this->printSummary();

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Migration failed: '.$e->getMessage());
            Log::error('License data migration failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        } finally {
            $this->dropTempDatabase();
        }
    }

    private function setup(): void
    {
        if ($this->option('sql-file')) {
            $this->importSqlFile($this->option('sql-file'));
        }

        $this->configureLicenseConnection();

        if ($this->option('include-codes')) {
            $this->includedCodes = array_filter(array_map(
                'trim',
                explode(',', $this->option('include-codes'))
            ));
        }

        if ($this->option('fresh')) {
            $this->warn('Truncating existing license tables...');
            $this->truncateLicenseTables();
        }
    }

    private function executeSteps(): void
    {
        $steps = [
            ['Building product mapping', fn () => $this->buildProductMapping()],
            ['Migrating licenses', fn () => $this->migrateLicenses()],
            ['Migrating installations', fn () => $this->migrateInstallations()],
            ['Migrating license callbacks', fn () => $this->migrateLicenseCallbacks()],
            ['Migrating license schemes', fn () => $this->migrateLicenseSchemes()],
            ['Migrating license notifications', fn () => $this->migrateLicenseNotifications()],
            ['Migrating banned hosts', fn () => $this->migrateBannedHosts()],
            ['Migrating whitelist IPs', fn () => $this->migrateWhitelistIps()],
            ['Migrating license reports', fn () => $this->migrateLicenseReports()],
            ['Migrating product versions', fn () => $this->migrateProductVersions()],
            ['Migrating version callbacks', fn () => $this->migrateVersionCallbacks()],
            ['Migrating version installations', fn () => $this->migrateVersionInstallations()],
            ['Migrating version notifications', fn () => $this->migrateVersionNotifications()],
            ['Migrating license plugins', fn () => $this->migrateLicensePlugins()],
            ['Migrating license options', fn () => $this->migrateLicenseOptions()],
            ['Migrating installation logs', fn () => $this->migrateInstallationLogs()],
            ['Updating product columns', fn () => $this->updateProductColumns()],
        ];

        $total = count($steps);

        foreach ($steps as $i => [$label, $action]) {
            $step = $i + 1;
            $this->info("[{$step}/{$total}] {$label}...");
            $result = $action();

            if (is_int($result)) {
                $this->line("  Migrated {$result} records");
            }
        }
    }

    private function printSummary(): void
    {
        $this->newLine();
        $this->info('Data migration completed successfully!');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Products mapped', count($this->productMap)],
                ['Licenses migrated', count($this->licenseMap)],
                ['Licenses user-mapped via order', $this->resolvedViaOrder],
                ['Versions migrated', count($this->versionMap)],
                ...($this->skippedUsers > 0
                    ? [['Licenses skipped (no order mapping)', $this->skippedUsers]]
                    : []),
            ]
        );
    }

    /**
     * Migrate a source table into a destination table using lazyById for
     * memory-efficient streaming and batch inserts.
     *
     * The transformer closure receives each source row and returns the
     * destination row array, or null to skip the row.
     */
    private function migrateTable(
        string $sourceTable,
        string $destTable,
        string $primaryKey,
        \Closure $transformer,
        ?string $productKey = null,
        bool $ignoreDuplicates = false,
    ): int {
        $count = 0;

        $this->licenseDb()->table($sourceTable)
            ->lazyById(self::CHUNK_SIZE, $primaryKey)
            ->filter(fn (object $row) => ! $productKey || isset($this->productMap[$row->{$productKey}]))
            ->map($transformer)
            ->filter()
            ->chunk(self::INSERT_BATCH_SIZE)
            ->each(function ($batch) use ($destTable, $ignoreDuplicates, &$count) {
                $rows = $batch->all();
                if ($ignoreDuplicates) {
                    DB::table($destTable)->insertOrIgnore($rows);
                } else {
                    DB::table($destTable)->insert($rows);
                }
                $count += count($rows);
            });

        return $count;
    }

    private function migrateInstallations(): int
    {
        return $this->migrateTable(
            'afl_installations', 'installations', 'installation_id',
            fn (object $r) => [
                'product_id' => $this->productMap[$r->product_id],
                'user_id' => $this->resolveUserIdForLicense($r->license_code),
                'license_code' => $r->license_code,
                'installation_ip' => $r->installation_ip,
                'installation_domain' => $r->installation_domain,
                'installation_date' => $this->cleanDate($r->installation_date),
                'installation_status' => ($r->installation_status ?? 1) ? 1 : 0,
                'installation_hash' => $r->installation_hash,
                'installation_disable_ip_verification' => $r->installation_disable_ip_verification ?? 0,
                ...$this->timestamps($r),
            ],
            productKey: 'product_id',
        );
    }

    private function migrateLicenseCallbacks(): int
    {
        return $this->migrateTable(
            'afl_callbacks', 'license_callbacks', 'callback_id',
            fn (object $r) => [
                'product_id' => $this->productMap[$r->product_id],
                'user_id' => $this->resolveUserIdForLicense($r->license_code),
                'license_code' => $r->license_code,
                'callback_ip' => $r->callback_ip,
                'callback_domain' => $r->callback_domain,
                'callback_date_time' => $this->cleanDate($r->callback_date_time),
                'callback_status' => $r->callback_status ?? 1,
                ...$this->timestamps($r),
            ],
            productKey: 'product_id',
        );
    }

    private function migrateLicenseSchemes(): int
    {
        return $this->migrateTable(
            'afl_license_schemes', 'license_schemes', 'scheme_id',
            fn (object $r) => [
                'scheme_query' => $r->scheme_query,
                'scheme_status' => $r->scheme_status ?? 1,
                ...$this->timestamps($r),
            ],
        );
    }

    private function migrateBannedHosts(): int
    {
        return $this->migrateTable(
            'afl_banned_hosts', 'license_banned_hosts', 'banned_host_id',
            fn (object $r) => [
                'banned_host_ip' => $r->banned_host_ip,
                'banned_host_comments' => $r->banned_host_comments ?? null,
                'banned_host_date' => $this->cleanDate($r->banned_host_date ?? null),
                'banned_host_blocks' => $r->banned_host_blocks ?? null,
                'banned_host_last_block_date' => $this->cleanDate($r->banned_host_last_block_date ?? null),
                ...$this->timestamps($r),
            ],
        );
    }

    private function migrateWhitelistIps(): int
    {
        return $this->migrateTable(
            'afl_whitelist_ips', 'license_whitelist_ips', 'whitelist_host_id',
            fn (object $r) => [
                'whitelist_host_ip' => $r->whitelist_host_ip,
                'whitelist_host_comments' => $r->whitelist_host_comments ?? null,
                ...$this->timestamps($r),
            ],
        );
    }

    private function migrateLicenseReports(): int
    {
        return $this->migrateTable(
            'afl_reports', 'license_reports', 'report_id',
            function (object $r) {
                if ($r->product_id > 0 && ! isset($this->productMap[$r->product_id])) {
                    return null;
                }

                return [
                    'product_id' => $this->productMap[$r->product_id] ?? null,
                    'user_id' => $this->resolveUserIdForLicense($r->license_code),
                    'license_code' => $r->license_code ?: null,
                    'report_date_time' => $this->cleanDate($r->report_date_time),
                    'report_text' => $r->report_text,
                    'report_system' => $r->report_system ?? 0,
                    'report_status' => $r->report_status ?? 1,
                    ...$this->timestamps($r),
                ];
            },
        );
    }

    private function migrateVersionCallbacks(): int
    {
        return $this->migrateTable(
            'afu_callbacks', 'version_callbacks', 'callback_id',
            fn (object $r) => [
                'product_id' => $this->productMap[$r->product_id],
                'version_id' => $this->versionMap[$r->version_id] ?? null,
                'callback_type' => $r->callback_type,
                'callback_ip' => $r->callback_ip,
                'callback_path' => $r->callback_path,
                'callback_date_time' => $this->cleanDate($r->callback_date_time),
                'callback_status' => $r->callback_status ?? 1,
                ...$this->timestamps($r),
            ],
            productKey: 'product_id',
        );
    }

    private function migrateVersionInstallations(): int
    {
        return $this->migrateTable(
            'afu_installations', 'version_installations', 'installation_id',
            function (object $r) {
                $newVersionId = $this->versionMap[$r->version_id] ?? null;
                if (! $newVersionId) {
                    return null;
                }

                return [
                    'product_id' => $this->productMap[$r->product_id],
                    'user_id' => null,
                    'version_id' => $newVersionId,
                    'installation_date' => $this->cleanDate($r->installation_date),
                    'installation_status' => $r->installation_status ?? 1,
                    ...$this->timestamps($r),
                ];
            },
            productKey: 'product_id',
        );
    }

    private function migrateLicensePlugins(): int
    {
        return $this->migrateTable(
            'license_plugins', 'license_plugins', 'id',
            function (object $r) {
                $newLicenseId = $this->licenseMap[$r->license_id] ?? null;
                $newProductId = $this->productMap[$r->product_id] ?? null;
                if (! $newLicenseId || ! $newProductId) {
                    return null;
                }

                return [
                    'license_id' => $newLicenseId,
                    'product_id' => $newProductId,
                    ...$this->timestamps($r),
                ];
            },
            ignoreDuplicates: true,
        );
    }

    private function migrateLicenseOptions(): int
    {
        return $this->migrateTable(
            'license_options', 'license_options', 'id',
            function (object $r) {
                $newLicenseId = $this->licenseMap[$r->license_id] ?? null;
                $newProductId = $this->productMap[$r->product_id] ?? null;
                if (! $newLicenseId || ! $newProductId) {
                    return null;
                }

                return [
                    'license_id' => $newLicenseId,
                    'product_id' => $newProductId,
                    'option_group' => $r->option_group ?? '',
                    'option_name' => $r->option_name ?? '',
                    'key' => $r->key ?? '',
                    'value' => $r->value ?? '',
                    ...$this->timestamps($r),
                ];
            },
        );
    }

    private function migrateInstallationLogs(): int
    {
        return $this->migrateTable(
            'installation_logs', 'installation_logs', 'id',
            fn (object $r) => [
                'license_code' => $r->license_code,
                'version_number' => $r->version_number ?? null,
                'installation_ip' => $r->installation_ip,
                'installation_domain' => $r->installation_domain ?? '',
                'installation_last_active_date' => $this->cleanDate($r->installation_last_active_date),
                'installation_status' => $r->installation_status ?? 1,
                ...$this->timestamps($r),
            ],
        );
    }

    private function buildProductMapping(): void
    {
        $billingBySku = DB::table('products')->whereNotNull('product_sku')->pluck('id', 'product_sku');
        $billingByName = DB::table('products')->pluck('id', 'name');

        $this->licenseDb()->table('afl_products')
            ->whereNull('deleted_at')
            ->lazyById(self::CHUNK_SIZE, 'product_id')
            ->each(function (object $lp) use ($billingBySku, $billingByName) {
                $sku = $lp->product_sku ?? null;

                if ($sku && $billingBySku->has($sku)) {
                    $this->productMap[$lp->product_id] = $billingBySku[$sku];

                    return;
                }

                if ($billingByName->has($lp->product_title)) {
                    $this->productMap[$lp->product_id] = $billingByName[$lp->product_title];

                    return;
                }

                $newId = DB::table('products')->insertGetId([
                    'name' => $lp->product_title,
                    'description' => $lp->product_description ?? '',
                    'product_sku' => $sku,
                    'status' => $lp->product_status ?? 1,
                    'created_at' => $lp->created_at ?? $this->now,
                    'updated_at' => $this->now,
                ]);

                $this->productMap[$lp->product_id] = $newId;
                if ($sku) {
                    $billingBySku[$sku] = $newId;
                }
                $billingByName[$lp->product_title] = $newId;
                $this->warn("  Created new product: {$lp->product_title} (ID: {$newId})");
            });

        $this->line('  Mapped '.count($this->productMap).' products');
    }

    private function migrateLicenses(): int
    {
        $count = 0;
        $orderUserMap = DB::table('orders')
            ->whereNotNull('number')
            ->pluck('client', 'number')
            ->all();

        $this->licenseDb()->table('afl_licenses')
            ->lazyById(self::CHUNK_SIZE, 'license_id')
            ->each(function (object $lic) use (&$count, $orderUserMap) {
                $newProductId = $this->productMap[$lic->product_id] ?? null;
                if (! $newProductId) {
                    $this->warn("  Skipping license #{$lic->license_id} - orphaned product #{$lic->product_id}");

                    return;
                }

                if (empty($lic->license_code)) {
                    return;
                }

                $orderNumber = $lic->license_order_number;
                $newUserId = null;

                if ($orderNumber !== null && $orderNumber !== '' && isset($orderUserMap[$orderNumber])) {
                    $newUserId = (int) $orderUserMap[$orderNumber];
                    $this->resolvedViaOrder++;
                }

                if (! $newUserId && ! in_array($lic->license_code, $this->includedCodes, true)) {
                    $this->skippedUsers++;
                    $this->warn("  Skipping license {$lic->license_code} - no order mapping (use --include-codes to force)");

                    return;
                }

                $this->licenseCodeUserMap[$lic->license_code] = $newUserId;

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
                    ...$this->timestamps($lic),
                ]);

                $this->licenseMap[$lic->license_id] = $newId;
                $count++;
            });

        return $count;
    }

    private function migrateProductVersions(): int
    {
        $count = 0;

        $this->licenseDb()->table('afu_versions')
            ->lazyById(self::CHUNK_SIZE, 'version_id')
            ->each(function (object $ver) use (&$count) {
                $newProductId = $this->productMap[$ver->product_id] ?? null;
                if (! $newProductId) {
                    return;
                }

                try {
                    $newId = DB::table('product_uploads')->insertGetId([
                        'product_id' => $newProductId,
                        'title' => $ver->version_number,
                        'description' => $ver->version_changelog ?? '',
                        'version' => $ver->version_number,
                        'file' => $ver->version_install_file ?? '',
                        'version_expire_date' => $this->cleanDate($ver->version_expire_date ?? null),
                        'version_install_count' => $ver->version_install_count ?? 0,
                        'status' => ($ver->version_status === 'inactive' || $ver->version_status === 0 || $ver->version_status === '0') ? 0 : 1,
                        ...$this->timestamps($ver),
                    ]);
                    $this->versionMap[$ver->version_id] = $newId;
                    $count++;
                } catch (\Exception $e) {
                    $this->warn("  Skipping version #{$ver->version_id}: ".$e->getMessage());
                }
            });

        return $count;
    }

    private function migrateLicenseNotifications(): void
    {
        $n = $this->licenseDb()->table('afl_notifications')->first();
        if (! $n) {
            $this->line('  No notifications to migrate');

            return;
        }

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
            ...$this->timestamps($n),
        ]);

        $this->line('  Migrated 1 notification record');
    }

    private function migrateVersionNotifications(): void
    {
        $n = $this->licenseDb()->table('afu_notifications')->first();
        if (! $n) {
            $this->line('  No version notifications to migrate');

            return;
        }

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

        $this->line('  Migrated 1 version notification record');
    }

    private function updateProductColumns(): int
    {
        $afuProducts = $this->licenseDb()->table('afu_products')->get()->keyBy('product_sku');
        $count = 0;

        $this->licenseDb()->table('afl_products')
            ->whereNull('deleted_at')
            ->lazyById(self::CHUNK_SIZE, 'product_id')
            ->each(function (object $lp) use ($afuProducts, &$count) {
                $billingProductId = $this->productMap[$lp->product_id] ?? null;
                if (! $billingProductId) {
                    return;
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
            });

        return $count;
    }

    private function configureLicenseConnection(): void
    {
        $host = $this->option('host') ?: config('database.connections.mysql.host', 'localhost');
        $port = $this->option('port') ?: config('database.connections.mysql.port', '');
        $database = $this->tempDatabase
            ?: ($this->option('database') ?: config('database.connections.mysql.database', ''));
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

        DB::purge('license');
    }

    private function licenseDb(): \Illuminate\Database\ConnectionInterface
    {
        return DB::connection('license');
    }

    private function importSqlFile(string $filePath): void
    {
        if (! file_exists($filePath) || ! is_readable($filePath)) {
            throw new \RuntimeException("SQL file not found or not readable: {$filePath}");
        }

        $host = $this->option('host') ?: config('database.connections.mysql.host', 'localhost');
        $port = $this->option('port') ?: config('database.connections.mysql.port', '3306');
        $username = $this->option('username') ?: config('database.connections.mysql.username', 'root');
        $password = $this->option('password') ?: config('database.connections.mysql.password', '');
        $socket = $this->option('socket') ?: config('database.connections.mysql.unix_socket', '');

        $this->tempDatabase = 'license_migration_tmp_'.time();
        $this->info("Creating temporary database: {$this->tempDatabase}");

        DB::statement("CREATE DATABASE `{$this->tempDatabase}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        $cmd = ['mysql'];
        if ($socket) {
            $cmd[] = "--socket={$socket}";
        } else {
            $cmd[] = "--host={$host}";
            if ($port) {
                $cmd[] = "--port={$port}";
            }
        }
        $cmd[] = "--user={$username}";
        $cmd[] = $this->tempDatabase;

        $env = array_merge(getenv() ?: [], ['MYSQL_PWD' => $password]);

        $this->info('Importing SQL file into temporary database...');

        $descriptors = [
            0 => ['file', $filePath, 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($cmd, $descriptors, $pipes, null, $env);

        if (! is_resource($process)) {
            DB::statement("DROP DATABASE IF EXISTS `{$this->tempDatabase}`");
            $this->tempDatabase = null;
            throw new \RuntimeException('Failed to start mysql process. Ensure the mysql CLI is installed and in PATH.');
        }

        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            DB::statement("DROP DATABASE IF EXISTS `{$this->tempDatabase}`");
            $this->tempDatabase = null;
            throw new \RuntimeException("SQL import failed (exit code {$exitCode}): {$stderr}");
        }

        $this->info("SQL file imported successfully into '{$this->tempDatabase}'.");
    }

    private function dropTempDatabase(): void
    {
        if (! $this->tempDatabase) {
            return;
        }

        $this->warn("Dropping temporary database: {$this->tempDatabase}");
        try {
            DB::purge('license');
            DB::statement("DROP DATABASE IF EXISTS `{$this->tempDatabase}`");
        } catch (\Exception $e) {
            $this->warn("Could not drop temporary database '{$this->tempDatabase}': ".$e->getMessage());
        }
        $this->tempDatabase = null;
    }

    private function truncateLicenseTables(): void
    {
        $tables = [
            'installation_logs', 'license_options', 'license_plugins',
            'version_installations', 'version_callbacks',
            'product_uploads', 'license_reports', 'license_whitelist_ips',
            'license_banned_hosts', 'license_notifications',
            'version_notifications', 'license_schemes',
            'license_callbacks', 'installations', 'licenses',
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function resolveUserIdForLicense(?string $licenseCode): ?int
    {
        if ($licenseCode === null || $licenseCode === '') {
            return null;
        }

        return $this->licenseCodeUserMap[$licenseCode] ?? null;
    }

    private function cleanDate(?string $date): ?string
    {
        $parsed = rescue(fn () => Carbon::parse($date), null, false);

        return $parsed?->year > 0 ? $parsed->toDateTimeString() : null;
    }

    private function timestamps(object $row): array
    {
        return [
            'created_at' => $row->created_at ?? $this->now,
            'updated_at' => $row->updated_at ?? $this->now,
        ];
    }
}
