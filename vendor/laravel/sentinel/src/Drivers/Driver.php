<?php

namespace Laravel\Sentinel\Drivers;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;

abstract class Driver
{
    /**
     * Construct a new driver.
     *
     * @param  Closure(): Application  $applicationResolver
     */
    public function __construct(protected Closure $applicationResolver)
    {
        //
    }

    /**
     * Authorize access for the request.
     */
    abstract public function authorize(Request $request): bool;

    /**
     * Authorize access for the request or throw an exception.
     *
     * @throws AuthorizationException
     */
    public function authorizeOrFail(Request $request): void
    {
        if ($this->authorize($request) === false) {
            throw new AuthorizationException;
        }
    }

    /**
     * Authorize accessing via reverse proxies.
     */
    protected function authorizeAccessingViaReverseProxies(Request $request): bool
    {
        if (! $this->isPrivateIp($request->ip()) && $request->isFromTrustedProxy()) {
            return false;
        }

        return true;
    }

    /**
     * Determine if the application is running on Docker locally.
     */
    protected function isRunningOnDockerLocally(Request $request): bool
    {
        return $request->server->get('REMOTE_ADDR') === '127.0.0.1' && file_exists(base_path('.dockerenv'));
    }

    /**
     * Checks if an IPv4 or IPv6 address is contained in the list of private IP subnets.
     */
    protected function isPrivateIp(string $requestIp): bool
    {
        /** @phpstan-ignore function.alreadyNarrowedType */
        if (method_exists(IpUtils::class, 'isPrivateIp')) {
            return IpUtils::isPrivateIp($requestIp);
        }

        return IpUtils::checkIp($requestIp, [
            '127.0.0.0/8',    // RFC1700 (Loopback)
            '10.0.0.0/8',     // RFC1918
            '192.168.0.0/16', // RFC1918
            '172.16.0.0/12',  // RFC1918
            '169.254.0.0/16', // RFC3927
            '0.0.0.0/8',      // RFC5735
            '240.0.0.0/4',    // RFC1112
            '::1/128',        // Loopback
            'fc00::/7',       // Unique Local Address
            'fe80::/10',      // Link Local Address
            '::ffff:0:0/96',  // IPv4 translations
            '::/128',         // Unspecified address
        ]);
    }

    /**
     * Get the application instance.
     */
    protected function app(): Application
    {
        return ($this->applicationResolver)();
    }
}
