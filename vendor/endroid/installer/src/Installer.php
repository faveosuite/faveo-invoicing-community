<?php

declare(strict_types=1);

namespace Endroid\Installer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\ScriptEvents;

final class Installer implements PluginInterface, EventSubscriberInterface
{
    private const string PROJECT_TYPE_ALL = 'all';

    private Composer $composer;
    private IOInterface $io;

    /** @var array<string, array<string>> */
    private array $projectTypes = [
        self::PROJECT_TYPE_ALL => [],
        'symfony' => [
            'config/packages',
            'public',
        ],
    ];

    #[\Override]
    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    #[\Override]
    public function deactivate(Composer $composer, IOInterface $io): void {}

    #[\Override]
    public function uninstall(Composer $composer, IOInterface $io): void {}

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => ['install', 1],
            ScriptEvents::POST_UPDATE_CMD => ['install', 1],
        ];
    }

    public function install(): void
    {
        $foundCompatibleProjectType = false;
        foreach ($this->projectTypes as $projectType => $paths) {
            if (!$this->isCompatibleProjectType($paths)) {
                continue;
            }

            if (self::PROJECT_TYPE_ALL !== $projectType) {
                $this->io->write('<info>Endroid Installer detected project type "' . $projectType . '"</>');
                $foundCompatibleProjectType = true;
            }
            $this->installProjectType($projectType);
        }

        if (!$foundCompatibleProjectType) {
            $this->io->write('<info>Endroid Installer did not detect a specific framework for auto-configuration</>');
        }
    }

    /** @param array<string> $paths */
    private function isCompatibleProjectType(array $paths): bool
    {
        $cwd = getcwd();

        if (false === $cwd) {
            return false;
        }

        return array_all($paths, static fn(string $path): bool => file_exists($cwd . DIRECTORY_SEPARATOR . $path));
    }

    private function installProjectType(string $projectType): void
    {
        $extra = $this->composer->getPackage()->getExtra();
        /** @var mixed $installerConfig */
        $installerConfig = $extra['endroid']['installer'] ?? [];
        /** @var array<string> $exclude */
        $exclude = is_array($installerConfig)
        && array_key_exists('exclude', $installerConfig)
        && is_array($installerConfig['exclude'])
            ? $installerConfig['exclude']
            : [];

        $processedPackages = [];
        $packages = $this->composer
            ->getRepositoryManager()
            ->getLocalRepository()
            ->getPackages();

        foreach ($packages as $package) {
            // Avoid handling duplicates: getPackages sometimes returns duplicates
            if (in_array($package->getName(), $processedPackages, strict: true)) {
                continue;
            }
            $processedPackages[] = $package->getName();

            // Skip excluded packages
            if (in_array($package->getName(), $exclude, strict: true)) {
                $this->io->write('- Skipping <info>' . $package->getName() . '</>');
                continue;
            }

            // Check for installation files and install
            $packagePath = $this->composer->getInstallationManager()->getInstallPath($package);
            if (null === $packagePath) {
                continue;
            }
            $sourcePath = $packagePath . DIRECTORY_SEPARATOR . '.install' . DIRECTORY_SEPARATOR . $projectType;
            if (file_exists($sourcePath)) {
                $changed = $this->copy($sourcePath, (string) getcwd());
                if ($changed) {
                    $this->io->write('- Configured <info>' . $package->getName() . '</>');
                }
            }
        }
    }

    private function copy(string $sourcePath, string $targetPath): bool
    {
        $changed = false;

        $directoryIterator = new \RecursiveDirectoryIterator($sourcePath, \RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directoryIterator, \RecursiveIteratorIterator::SELF_FIRST);

        /** @var \SplFileInfo $fileInfo */
        foreach ($iterator as $fileInfo) {
            $target = $targetPath . DIRECTORY_SEPARATOR . $directoryIterator->getSubPathName();
            if ($fileInfo->isDir()) {
                if (!is_dir($target)) {
                    mkdir($target);
                }
                continue;
            }

            if (!file_exists($target)) {
                $this->copyFile($fileInfo->getPathname(), $target);
                $changed = true;
            }
        }

        return $changed;
    }

    public function copyFile(string $source, string $target): void
    {
        if (file_exists($target)) {
            return;
        }

        copy($source, $target);
        $targetPerms = fileperms($target);
        $sourcePerms = fileperms($source);
        if (false !== $targetPerms && false !== $sourcePerms) {
            chmod($target, $targetPerms | ($sourcePerms & 0o111));
        }
    }
}
