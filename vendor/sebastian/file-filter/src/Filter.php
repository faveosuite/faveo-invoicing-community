<?php declare(strict_types=1);
/*
 * This file is part of sebastian/file-filter.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace SebastianBergmann\FileFilter;

use const DIRECTORY_SEPARATOR;
use function array_key_exists;
use function assert;
use function basename;
use function dirname;
use function preg_match;
use function str_ends_with;
use function str_replace;
use function str_starts_with;
use function strlen;
use function substr;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for this library
 */
final readonly class Filter
{
    /**
     * @var list<array{regularExpression: string, prefix: string, suffix: string}>
     */
    private array $includeDirectoryMatchers;

    /**
     * @var array<string, true>
     */
    private array $includeFilesMap;

    /**
     * @var list<array{regularExpression: string, prefix: string, suffix: string}>
     */
    private array $excludeDirectoryMatchers;

    /**
     * @var array<string, true>
     */
    private array $excludeFilesMap;

    /**
     * @param list<array{regularExpression: string, prefix: string, suffix: string}> $includeDirectoryMatchers
     * @param array<string, true>                                                    $includeFilesMap
     * @param list<array{regularExpression: string, prefix: string, suffix: string}> $excludeDirectoryMatchers
     * @param array<string, true>                                                    $excludeFilesMap
     */
    public function __construct(array $includeDirectoryMatchers, array $includeFilesMap, array $excludeDirectoryMatchers, array $excludeFilesMap)
    {
        $this->includeDirectoryMatchers = $includeDirectoryMatchers;
        $this->includeFilesMap          = $includeFilesMap;
        $this->excludeDirectoryMatchers = $excludeDirectoryMatchers;
        $this->excludeFilesMap          = $excludeFilesMap;
    }

    /**
     * @param non-empty-string $path
     */
    public function accepts(string $path): bool
    {
        $normalizedPath = str_replace(DIRECTORY_SEPARATOR, '/', $path);

        $directory = dirname($normalizedPath);
        $filename  = basename($normalizedPath);

        if (array_key_exists($normalizedPath, $this->excludeFilesMap)) {
            return false;
        }

        if (array_key_exists($normalizedPath, $this->includeFilesMap)) {
            if ($this->matchesDirectory($this->excludeDirectoryMatchers, $directory, $filename, false)) {
                return false;
            }

            return true;
        }

        if (!$this->matchesDirectory($this->includeDirectoryMatchers, $directory, $filename, true)) {
            return false;
        }

        if ($this->matchesDirectory($this->excludeDirectoryMatchers, $directory, $filename, false)) {
            return false;
        }

        return true;
    }

    /**
     * @param list<array{regularExpression: string, prefix: string, suffix: string}> $matchers
     */
    private function matchesDirectory(array $matchers, string $directory, string $filename, bool $rejectHiddenSubdirectories): bool
    {
        $directoryWithTrailingSlash = $directory . '/';

        foreach ($matchers as $matcher) {
            if (preg_match($matcher['regularExpression'], $directoryWithTrailingSlash, $matches) !== 1) {
                continue;
            }

            if ($matcher['prefix'] !== '' && !str_starts_with($filename, $matcher['prefix'])) {
                continue;
            }

            if ($matcher['suffix'] !== '' && !str_ends_with($filename, $matcher['suffix'])) {
                continue;
            }

            assert(isset($matches[0]));

            if ($rejectHiddenSubdirectories && $this->isInHiddenSubdirectory($directoryWithTrailingSlash, $matches[0])) {
                continue;
            }

            return true;
        }

        return false;
    }

    private function isInHiddenSubdirectory(string $directoryWithTrailingSlash, string $matchedPrefix): bool
    {
        $relativeDirectory = substr($directoryWithTrailingSlash, strlen($matchedPrefix));

        if ($relativeDirectory === '') {
            return false;
        }

        return preg_match('#(?:^|/)\.[^/]+/#', $relativeDirectory) === 1;
    }
}
