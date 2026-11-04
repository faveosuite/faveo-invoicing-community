<?php

namespace Tests\Unit\Backend\Routes;

use Illuminate\Routing\Route;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

/**
 * Middleware lives on the routes (routes/*.php, plugin route files), never in controller
 * constructors, so `web.php` shows what every endpoint requires.
 */
class RouteMiddlewareTest extends TestCase
{
    /** Dev-tool / package routes that differ per environment. */
    private const VENDOR_PREFIXES = ['_debugbar/', '_ignition/', 'livewire-', 'horizon'];

    public function test_no_controller_registers_middleware_in_its_constructor(): void
    {
        $offenders = [];
        foreach (Finder::create()->files()->in(app_path())->name('*.php') as $file) {
            if (str_contains($file->getContents(), '$this->middleware(')) {
                $offenders[] = $file->getRelativePathname();
            }
        }

        $this->assertSame([], $offenders, 'Put middleware on the route instead of $this->middleware() in the controller.');
    }

    public function test_only_the_reviewed_routes_run_without_login(): void
    {
        $actual = collect(app('router')->getRoutes()->getRoutes())
            ->reject(fn (Route $r): bool => str_starts_with($r->uri(), '_') || \Illuminate\Support\Str::startsWith($r->uri(), self::VENDOR_PREFIXES)
                || str_starts_with($r->getActionName(), 'Laravel\\') || str_starts_with($r->getActionName(), 'Livewire\\'))
            ->filter(fn (Route $r): bool => ! in_array('auth', array_diff($r->gatherMiddleware(), $r->excludedMiddleware()), true))
            ->map(fn (Route $r): string => implode('|', $r->methods()).' '.$r->uri())
            ->sort()->values()->all();

        $expected = collect(file(__DIR__.'/public_routes.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES))
            ->reject(fn (string $line): bool => str_starts_with($line, '#'))
            ->sort()->values()->all();

        $this->assertSame([], array_values(array_diff($actual, $expected)), 'New route(s) without `auth`: add `auth`, or review and list them in public_routes.txt.');
        $this->assertSame([], array_values(array_diff($expected, $actual)), 'Listed public route(s) now require login or no longer exist: update public_routes.txt.');
    }
}
