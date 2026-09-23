<?php

namespace App\Services\Pdf;

use App\FileSystemSettings;
use Illuminate\Support\Facades\Cache;

class PdfManager
{
    private const string CHROME_PATH_CACHE_KEY = 'settings_filesystem_chrome_path';

    private const string DRIVER_CACHE_KEY = 'settings_filesystem_pdf_driver';

    private const string HOME_DIR = 'app/chrome-home';

    private const string PROFILE_DIR = 'app/chrome-profile';

    public function boot(): void
    {
        $driver = $this->loadDriver();

        config(['laravel-pdf.driver' => $driver]);

        match ($driver) {
            'chrome' => $this->bootChrome(),
            default => null,
        };
    }

    /**
     * Call after any settings change (e.g. chrome_path, pdf_driver) that a driver has cached.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CHROME_PATH_CACHE_KEY);
        Cache::forget(self::DRIVER_CACHE_KEY);
    }

    private function bootChrome(): void
    {
        $home = storage_path(self::HOME_DIR);
        $profile = storage_path(self::PROFILE_DIR);

        $this->mkdir($home);
        $this->mkdir($profile);

        $isWindows = PHP_OS_FAMILY === 'Windows';

        // --disable-setuid-sandbox / --disable-dev-shm-usage are Linux-only concerns (setuid
        // sandbox, /dev/shm) — Chrome just ignores unknown flags, but skip them on Windows for clarity
        $flags = ['--disable-crashpad', '--disable-crash-reporter'];
        if (! $isWindows) {
            array_unshift($flags, '--disable-setuid-sandbox', '--disable-dev-shm-usage');
        }

        // HOME isn't consulted by Chrome on Windows — it reads USERPROFILE/APPDATA instead
        $defaultPath = $isWindows ? 'C:\Windows\System32' : '/usr/bin:/bin';
        $path = getenv('PATH') ?: $defaultPath;

        $envVariables = $isWindows
            ? ['PATH' => $path, 'USERPROFILE' => $home, 'APPDATA' => $home]
            : ['PATH' => $path, 'HOME' => $home];

        config([
            'laravel-pdf.chrome.chrome_binary' => $this->loadChromePath() ?: config('laravel-pdf.chrome.chrome_binary'),
            'laravel-pdf.chrome.no_sandbox' => true,
            'laravel-pdf.chrome.user_data_dir' => $profile,
            'laravel-pdf.chrome.custom_flags' => $flags,
            'laravel-pdf.chrome.env_variables' => $envVariables,
        ]);
    }

    private function mkdir(string $path): void
    {
        if (! is_dir($path)) {
            mkdir($path, 0755, recursive: true);
        }
    }

    private function loadChromePath(): ?string
    {
        try {
            return Cache::remember(self::CHROME_PATH_CACHE_KEY, 60, fn () => FileSystemSettings::query()->first()->chrome_path ?? null);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function loadDriver(): string
    {
        try {
            return Cache::remember(self::DRIVER_CACHE_KEY, 60, fn () => FileSystemSettings::query()->first()->pdf_driver ?? null) ?: 'chrome';
        } catch (\Throwable $e) {
            return 'chrome';
        }
    }
}
