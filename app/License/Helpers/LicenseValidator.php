<?php

namespace App\License\Helpers;

use App\License\Models\License;
use App\License\Models\LicenseBannedHost;
use App\License\Models\LicensePlugin;
use App\License\Models\LicenseWhitelistIp;
use App\Model\Product\Product;
use App\User;
use DateTime;
use Illuminate\Http\Request;

class LicenseValidator
{
    /**
     * Validate connection test request.
     * Original checks: valid IP, valid referrer URL, valid integer product_id,
     * and connection_hash must equal hash('sha256', 'connection_test').
     */
    public function isValidConnection($product_id, ?string $connection_hash): bool
    {
        $ip = request()->ip();
        $refer = request()->get('refer', request()->header('referer'));

        return filter_var($ip, FILTER_VALIDATE_IP) !== false
            && filter_var($refer, FILTER_VALIDATE_URL) !== false
            && $this->validateIntegerValue($product_id)
            && ! in_array($connection_hash, [null, '', '0'], strict: true)
            && $connection_hash === hash('sha256', 'connection_test');
    }

    /**
     * Validate basic license request parameters.
     * Original checks: valid IP, valid integer product_id, valid URL for root_url,
     * referrer matches root_url, valid installation_hash, non-empty signature,
     * and at least license_code or valid email is provided.
     */
    public function isValidLicenseRequest(string $ip, $product_id, ?string $root_url, ?string $license_code = null, ?string $client_email = null): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false
            && $this->validateIntegerValue($product_id)
            && filter_var($root_url, FILTER_VALIDATE_URL) !== false
            && (! in_array($license_code, [null, '', '0'], strict: true) || filter_var($client_email, FILTER_VALIDATE_EMAIL) !== false);
    }

    /**
     * Resolve client IP, applying cloud load-balancer override when needed.
     */
    public function resolveIp(Request $request): string
    {
        if ($request->input('is_cloud')) {
            return '138.197.237.160';
        }

        return $request->ip();
    }

    /**
     * Check if IP is banned.
     */
    public function isBanned(string $ip): bool
    {
        return LicenseBannedHost::where('banned_host_ip', $ip)->exists();
    }

    /**
     * Check if IP is whitelisted.
     */
    public function isWhitelisted(string $ip): bool
    {
        return LicenseWhitelistIp::where('whitelist_host_ip', $ip)->exists();
    }

    /**
     * Validate product exists.
     * Returns product if found (regardless of status), null if not found.
     * Controllers check status separately to distinguish not_found vs inactive.
     */
    public function validateProduct($product_id): ?Product
    {
        return Product::find($product_id);
    }

    /**
     * Validate license status and restrictions.
     * Uses integer status values matching original:
     *   0 = inactive, 1 = active, 2 = suspended.
     *
     * Supports comma-separated IPs and domains (same as original).
     */
    public function validateLicense(
        ?License $license,
        int $product_id,
        ?string $client_email,
        string $ip,
        string $root_url
    ): array {
        if (! $license instanceof \App\License\Models\License) {
            $license = $this->findLicenseByEmail($client_email, $product_id);
        }

        if (! $license instanceof \App\License\Models\License) {
            return ['valid' => false, 'error' => 'license_not_found'];
        }

        // Check license status (integer: 0=inactive, 1=active, 2=suspended)
        if ($license->license_status === 2) {
            return ['valid' => false, 'error' => 'license_suspended'];
        }

        if ($license->license_status === 0) {
            return [
                'valid' => false,
                'error' => 'license_cancelled',
                'data' => ['cancel_date' => $license->license_cancel_date],
            ];
        }

        // Check expiration (validate date format first, matching original)
        if ($this->verifyDateTime($license->license_expire_date, 'Y-m-d')
            && $license->license_expire_date < now()->format('Y-m-d')) {
            return [
                'valid' => false,
                'error' => 'license_expired',
                'data' => ['expire_date' => $license->license_expire_date],
            ];
        }

        // Check IP restriction (supports comma-separated IPs)
        if (! empty($license->license_ip)) {
            $licensed_ips = array_map(trim(...), explode(',', (string) $license->license_ip));
            if (! in_array($ip, $licensed_ips)) {
                return ['valid' => false, 'error' => 'invalid_ip', 'data' => ['ip' => $ip]];
            }
        }

        // Check domain restriction (supports comma-separated domains, uses stripos)
        if (! empty($license->license_domain)) {
            $licensed_domains = array_map(trim(...), explode(',', (string) $license->license_domain));
            $domain_valid = array_any($licensed_domains, fn ($domain): bool => stripos($root_url, (string) $domain) !== false);
            if (! $domain_valid) {
                return ['valid' => false, 'error' => 'invalid_domain', 'data' => ['domain' => $root_url]];
            }
        }

        return ['valid' => true, 'license' => $license];
    }

    /**
     * Find license by email for a product (public — called from callback controllers)
     * Same logic as original: find user by email → find active license.
     */
    public function findLicenseByEmail(?string $email, int $product_id): ?License
    {
        if (in_array($email, [null, '', '0'], strict: true)) {
            return null;
        }

        $user = User::where('email', $email)->where('active', 1)->first();
        if (! $user) {
            return null;
        }

        return License::where('user_id', $user->id)
            ->where('product_id', $product_id)
            ->where('license_status', 1)
            ->first();
    }

    /**
     * Validate integer value (same as aflValidateIntegerValue).
     */
    public function validateIntegerValue($number, int $min = 1, int $max = 999999999): bool
    {
        if (is_float($number)) {
            return false;
        }

        return filter_var($number, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => $min, 'max_range' => $max],
        ]) !== false;
    }

    /**
     * Validate raw domain format (same as aflValidateRawDomain).
     */
    public function validateRawDomain(?string $url): bool
    {
        if (in_array($url, [null, '', '0'], strict: true)) {
            return false;
        }

        return (bool) preg_match('/^[a-z0-9-.]+\.[a-z\.]{2,7}$/', strtolower($url));
    }

    /**
     * Extract raw domain from URL (same as aflGetRawDomain).
     */
    public function getRawDomain(?string $url): string
    {
        if (in_array($url, [null, '', '0'], strict: true)) {
            return '';
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (empty($scheme)) {
            $url = 'http://'.$url;
        }

        return str_ireplace('www.', '', parse_url($url, PHP_URL_HOST) ?? '');
    }

    /**
     * Verify datetime format (same as aflVerifyDateTime).
     */
    public function verifyDateTime(?string $datetime, string $format): bool
    {
        if (in_array($datetime, [null, '', '0'], strict: true) || ($format === '' || $format === '0')) {
            return false;
        }

        $dt = DateTime::createFromFormat($format, $datetime);
        $errors = DateTime::getLastErrors();

        return $dt && empty($errors['warning_count']);
    }

    /**
     * Verify script signature received from user's script.
     * Original: hash('sha256', gmdate('Y-m-d') . $root_url . $client_email . $license_code . $product_id . implode('', $root_ips_array)).
     */
    public function verifyScriptSignature(?string $license_signature, string $product_id, ?string $root_url, ?string $client_email, ?string $license_code): bool
    {
        if (in_array($license_signature, [null, '', '0'], strict: true)) {
            return false;
        }

        $rootUrl = url('/');
        $rootIps = @gethostbynamel($this->getRawDomain($rootUrl));

        if (! is_array($rootIps)) {
            $rootIps = [];
        }

        sort($rootIps);

        $expected = hash('sha256', gmdate('Y-m-d').$root_url.$client_email.$license_code.$product_id.implode('', $rootIps));

        return hash_equals($expected, $license_signature);
    }

    /**
     * Find license by code (with plugin support), falling back to email lookup.
     */
    public function findLicense(?string $license_code, ?string $client_email, int $product_id): ?License
    {
        if (! in_array($license_code, [null, '', '0'], strict: true)) {
            $license = $this->findLicenseWithPlugins($license_code, $product_id);
            if ($license instanceof \App\License\Models\License) {
                return $license;
            }
        }

        return $this->findLicenseByEmail($client_email, $product_id);
    }

    /**
     * Find license by license_code, with LicensePlugin multi-product support.
     * Original logic: first check license_code + product_id, then check if product_id
     * is in the LicensePlugin table for that license.
     */
    public function findLicenseWithPlugins(?string $license_code, int $product_id): ?License
    {
        if (in_array($license_code, [null, '', '0'], strict: true)) {
            return null;
        }

        // Direct match: license_code + product_id
        $license = License::where('license_code', $license_code)
            ->where('product_id', $product_id)
            ->first();

        if ($license) {
            return $license;
        }

        // Check LicensePlugin: license may cover this product via plugin
        $baseLicense = License::where('license_code', $license_code)->first();
        if (! $baseLicense) {
            return null;
        }

        $pluginProductIds = LicensePlugin::where('license_id', $baseLicense->id)
            ->pluck('product_id')
            ->toArray();

        if (in_array($product_id, $pluginProductIds)) {
            // Return the base license but associate it with the requested product_id
            $baseLicense->product_id = $product_id;

            return $baseLicense;
        }

        return null;
    }

    /**
     * Validate installation hash.
     * Original: hash('sha256', $root_url . $client_email . $license_code).
     */
    public function validateInstallationHash(?string $hash, ?string $root_url, ?string $client_email, ?string $license_code): bool
    {
        if (in_array($hash, [null, '', '0'], strict: true)) {
            return false;
        }

        $expected = hash('sha256', $root_url.$client_email.$license_code);

        return hash_equals($expected, $hash);
    }

    /**
     * Verify AFU (Auto Faveo Updater) script signature.
     * Original: hash('sha256', gmdate('Y-m-d') . $product_id . $product_key . implode('', $root_ips_array)).
     */
    public function verifyAfuScriptSignature(?string $script_signature, string $product_id, ?string $product_key): bool
    {
        if (in_array($script_signature, [null, '', '0'], strict: true)) {
            return false;
        }

        $rootUrl = url('/');
        $rootIps = @gethostbynamel($this->getRawDomain($rootUrl));

        if (! is_array($rootIps)) {
            $rootIps = [];
        }

        sort($rootIps);

        $expected = hash('sha256', gmdate('Y-m-d').$product_id.$product_key.implode('', $rootIps));

        return hash_equals($expected, $script_signature);
    }

    /**
     * Validate basic AFU (version/update) request parameters.
     * Original checks: valid IP, valid integer product_id, non-empty product_key,
     * non-empty user_local_path, non-empty script_signature.
     */
    public function isValidAfuRequest(string $ip, $product_id, ?string $product_key, ?string $user_local_path, ?string $script_signature): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false
            && $this->validateIntegerValue($product_id)
            && ! in_array($product_key, [null, '', '0'], strict: true)
            && ! in_array($user_local_path, [null, '', '0'], strict: true)
            && ! in_array($script_signature, [null, '', '0'], strict: true);
    }
}
