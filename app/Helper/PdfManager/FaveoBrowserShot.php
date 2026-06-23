<?php

namespace App\Helper\PdfManager;

use App\FileSystemSettings;
use Spatie\Browsershot\Browsershot;

class FaveoBrowserShot
{
    private static bool $initialized = false;

    /**
     * @var array<mixed>
     */
    private static array $bins = [];

    private const string HOME_DIR = 'app/chrome-home';

    private const string PROFILE_DIR = 'app/chrome-profile';

    public static function bootForLaravelPdf(): void
    {
        self::init();

        config([
            'laravel-pdf.browsershot.node_binary' => self::$bins['node'],
            'laravel-pdf.browsershot.npm_binary' => self::$bins['npm'],
            'laravel-pdf.browsershot.chrome_path' => self::$bins['chrome'],
            'laravel-pdf.browsershot.no_sandbox' => true,
        ]);
    }

    public static function browsershot(): Browsershot // @phpstan-ignore class.notFound
    {
        self::init();

        return new Browsershot() // @phpstan-ignore class.notFound, class.notFound
            ->setNodeBinary(self::$bins['node'])
            ->setNpmBinary(self::$bins['npm'])
            ->setChromePath(self::$bins['chrome'])
            ->noSandbox()
            ->addChromiumArguments(self::chromiumArgs());
    }

    private static function init(): void
    {
        if (self::$initialized) {
            return;
        }

        self::prepareDirs();
        self::loadBinaries();

        self::$initialized = true;
    }

    private static function prepareDirs(): void
    {
        $home = storage_path(self::HOME_DIR);
        $profile = storage_path(self::PROFILE_DIR);

        self::mkdir($home);
        self::mkdir($profile);

        putenv('HOME='.$home);
        putenv('TMPDIR='.$home);
    }

    private static function mkdir(string $path): void
    {
        if (! is_dir($path)) {
            mkdir($path, 0755, recursive: true);
        }
    }

    private static function loadBinaries(): void
    {
        if (self::$bins !== []) {
            return;
        }

        try {
            $settings = FileSystemSettings::query()->first();
            self::$bins = [
                'node' => $settings->node_path ?? null,
                'npm' => $settings->npm_path ?? null,
                'chrome' => $settings->chrome_path ?? null,
            ];
        } catch (\Throwable $e) {
            self::$bins = ['node' => null, 'npm' => null, 'chrome' => null];
        }
    }

    /**
     * @return array<mixed>
     */
    private static function chromiumArgs(): array
    {
        $profile = storage_path(self::PROFILE_DIR);

        return [
            'user-data-dir='.$profile,
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--single-process',
        ];
    }
}
