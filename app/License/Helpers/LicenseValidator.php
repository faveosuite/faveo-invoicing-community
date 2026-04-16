<?php

namespace App\License\Helpers;

use App\Model\Product\Product;
use App\License\Models\License;
use App\License\Models\LicenseBannedHost;
use App\License\Models\LicenseWhitelistIp;
use App\User;

class LicenseValidator
{
    /**
     * Validate connection test request
     * Same logic as original aflCheckSettings + connection validation.
     */
    public function isValidConnection($product_id, ?string $connection_hash): bool
    {
        $ip = request()->ip();
        $refer = request()->get('refer', request()->header('referer'));

        return filter_var($ip, FILTER_VALIDATE_IP) !== false
            && $this->validateIntegerValue($product_id)
            && ! empty($connection_hash);
    }

    /**
     * Validate basic license request parameters
     * Same as original validation in AflCallbacks.
     */
    public function isValidLicenseRequest(string $ip, $product_id, ?string $root_url): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false
            && $this->validateIntegerValue($product_id)
            && ! empty($root_url);
    }

    /**
     * Validate installation hash
     * Original: hash('sha256', $refer . $email . $code).
     */
    public function isValidInstallationHash(?string $hash, ?string $email, ?string $code): bool
    {
        if (empty($hash)) {
            return false;
        }
        $refer = request()->get('refer', request()->header('referer'));
        $expected = hash('sha256', $refer.$email.$code);

        return hash_equals($expected, $hash);
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
     * Validate product exists and is active (status = 1).
     */
    public function validateProduct($product_id): ?Product
    {
        $product = Product::find($product_id);

        if (! $product || $product->status != 1) {
            return null;
        }

        return $product;
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
        if (! $license) {
            $license = $this->findLicenseByEmail($client_email, $product_id);
        }

        if (! $license) {
            return ['valid' => false, 'error' => 'license_not_found'];
        }

        // Check license status (integer: 0=inactive, 1=active, 2=suspended)
        if ($license->license_status == 2) {
            return ['valid' => false, 'error' => 'license_suspended'];
        }

        if ($license->license_status == 0) {
            return [
                'valid' => false,
                'error' => 'license_cancelled',
                'data' => ['cancel_date' => $license->license_cancel_date],
            ];
        }

        // Check expiration
        if ($license->license_expire_date && $license->license_expire_date < now()->format('Y-m-d')) {
            return [
                'valid' => false,
                'error' => 'license_expired',
                'data' => ['expire_date' => $license->license_expire_date],
            ];
        }

        // Check IP restriction (supports comma-separated IPs)
        if (! empty($license->license_ip)) {
            $licensed_ips = array_map('trim', explode(',', $license->license_ip));
            if (! in_array($ip, $licensed_ips)) {
                return ['valid' => false, 'error' => 'invalid_ip', 'data' => ['ip' => $ip]];
            }
        }

        // Check domain restriction (supports comma-separated domains, uses stripos)
        if ($license->license_require_domain && ! empty($license->license_domain)) {
            $licensed_domains = array_map('trim', explode(',', $license->license_domain));
            $domain_valid = false;
            foreach ($licensed_domains as $domain) {
                if (stripos($root_url, $domain) !== false) {
                    $domain_valid = true;
                    break;
                }
            }
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
        if (empty($email)) {
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
        if (empty($url)) {
            return false;
        }

        return (bool) preg_match('/^[a-z0-9-.]+\.[a-z\.]{2,7}$/', strtolower($url));
    }

    /**
     * Extract raw domain from URL (same as aflGetRawDomain).
     */
    public function getRawDomain(?string $url): string
    {
        if (empty($url)) {
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
        if (empty($datetime) || empty($format)) {
            return false;
        }

        $dt = \DateTime::createFromFormat($format, $datetime);
        $errors = \DateTime::getLastErrors();

        return $dt && empty($errors['warning_count']);
    }
}
