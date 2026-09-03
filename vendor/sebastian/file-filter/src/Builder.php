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
use function preg_quote;
use function str_replace;
use function strlen;
use function strrpos;
use function substr;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for this library
 */
final readonly class Builder
{
    /**
     * @param list<array{path: non-empty-string, prefix: string, suffix: string}> $includeDirectories
     * @param list<non-empty-string>                                              $includeFiles
     * @param list<array{path: non-empty-string, prefix: string, suffix: string}> $excludeDirectories
     * @param list<non-empty-string>                                              $excludeFiles
     */
    public function build(array $includeDirectories, array $includeFiles, array $excludeDirectories, array $excludeFiles): Filter
    {
        return new Filter(
            $this->matchers($includeDirectories),
            $this->map($includeFiles),
            $this->matchers($excludeDirectories),
            $this->map($excludeFiles),
        );
    }

    /**
     * @param list<array{path: non-empty-string, prefix: string, suffix: string}> $directories
     *
     * @return list<array{regularExpression: string, prefix: string, suffix: string}>
     */
    private function matchers(array $directories): array
    {
        $matchers = [];

        foreach ($directories as $directory) {
            $matchers[] = [
                'regularExpression' => $this->globToRegularExpression($directory['path']),
                'prefix'            => $directory['prefix'],
                'suffix'            => $directory['suffix'],
            ];
        }

        return $matchers;
    }

    /**
     * @param list<non-empty-string> $files
     *
     * @return array<string, true>
     */
    private function map(array $files): array
    {
        $map = [];

        foreach ($files as $file) {
            $map[str_replace(DIRECTORY_SEPARATOR, '/', $file)] = true;
        }

        return $map;
    }

    private function globToRegularExpression(string $path): string
    {
        $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);

        // Escape special regular expression characters except * and ?
        $regularExpression = '';
        $len               = strlen($path);
        $i                 = 0;

        while ($i < $len) {
            $char = $path[$i];

            if ($char === '*') {
                // Check for globstar **
                if ($i + 1 < $len && $path[$i + 1] === '*') {
                    // Globstar pattern - matches zero or more directories
                    // Check if preceded by a single non-slash character (special case like Z**)
                    // In this case, the single char + ** means: match the parent directory
                    // and all subdirectories (the single char is essentially optional)
                    if ($i > 0 && $path[$i - 1] !== '/') {
                        // Check how many characters are between the last / and **
                        $lastSlash = strrpos(substr($path, 0, $i), '/');
                        $prefixLen = $lastSlash === false ? $i : $i - $lastSlash - 1;

                        if ($prefixLen === 1) {
                            // Single char before ** (like "Z**")
                            // Remove the single char we already added and match parent + subdirectories
                            $regularExpression = substr($regularExpression, 0, -1);
                            $regularExpression .= '(?:[^/]+/)*';
                        } else {
                            // Multiple chars before ** (like "ZZ**") - must match actual directory
                            $regularExpression .= '[^/]*/(?:[^/]+/)*';
                        }
                    } else {
                        // Standard globstar after slash or at start
                        $regularExpression .= '(?:[^/]+/)*';
                    }
                    $i += 2;

                    // Skip trailing slash if present
                    if ($i < $len && $path[$i] === '/') {
                        $i++;
                    }
                } else {
                    // Single * - matches anything except path separator
                    $regularExpression .= '[^/]*';

                    $i++;
                }
            } elseif ($char === '?') {
                // Single character except path separator
                $regularExpression .= '[^/]';

                $i++;
            } elseif ($char === '/') {
                $regularExpression .= '/';

                $i++;
            } else {
                // Escape the character if it is a regular expression special char
                $regularExpression .= preg_quote($char, '#');

                $i++;
            }
        }

        return '#^' . $regularExpression . '(?:/|$)#';
    }
}
