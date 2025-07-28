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
    private const PROJECT_TYPE_ALL = 'all';

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

    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }

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
            if ($this->isCompatibleProjectType($paths)) {
                if (self::PROJECT_TYPE_ALL !== $projectType) {
                    $this->io->write('<info>Endroid Installer detected project type "'.$projectType.'"</>');
                    $foundCompatibleProjectType = true;
                }
                $this->installProjectType($projectType);
            }
        }

        if (!$foundCompatibleProjectType) {
            $this->io->write('<info>Endroid Installer did not detect a specific framework for auto-configuration</>');
        }
    }

    /** @param array<string> $paths */
    private function isCompatibleProjectType(array $paths): bool
    {
        foreach ($paths as $path) {
            if (!file_exists(getcwd().DIRECTORY_SEPARATOR.$path)) {
                return false;
            }
        }

        return true;
    }

    private function installProjectType(string $projectType): void
    {
        $exclude = $this->composer->getPackage()->getExtra()['endroid']['installer']['exclude'] ?? [];

        $processedPackages = [];
        $packages = $this->composer->getRepositoryManager()->getLocalRepository()->getPackages();

        foreach ($packages as $package) {
            // Avoid handling duplicates: getPackages sometimes returns duplicates
            if (in_array($package->getName(), $processedPackages)) {
                continue;
            }
            $processedPackages[] = $package->getName();

            // Skip excluded packages
            if (in_array($package->getName(), $exclude)) {
                $this->io->write('- Skipping <info>'.$package->getName().'</>');
                continue;
            }

            // Check for installation files and install
            $packagePath = $this->composer->getInstallationManager()->getInstallPath($package);
            $sourcePath = $packagePath.DIRECTORY_SEPARATOR.'.install'.DIRECTORY_SEPARATOR.$projectType;
            if (file_exists($sourcePath)) {
                $changed = $this->copy($sourcePath, (string) getcwd());
                if ($changed) {
                    $this->io->write('- Configured <info>'.$package->getName().'</>');
                }
            }
        }
    }

    private function copy(string $sourcePath, string $targetPath): bool
    {
        $changed = false;

        /** @var \RecursiveDirectoryIterator $iterator */
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($sourcePath, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST);

        /** @var \SplFileInfo $fileInfo */
        foreach ($iterator as $fileInfo) {
            $target = $targetPath.DIRECTORY_SEPARATOR.$iterator->getSubPathName();
            if ($fileInfo->isDir()) {
                if (!is_dir($target)) {
                    mkdir($target);
                }
            } elseif (!file_exists($target)) {
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
        @chmod($target, fileperms($target) | (fileperms($source) & 0111));
    }
}
