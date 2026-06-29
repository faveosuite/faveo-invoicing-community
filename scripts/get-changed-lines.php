<?php
/**
 * Parses git diff and outputs a JSON map of repo-relative paths to the
 * line numbers that were ADDED or CHANGED in this PR vs development.
 *
 * Usage: php scripts/get-changed-lines.php [workspace]
 *
 * Output (stdout): { "app/Foo.php": [10, 11, 15], "resources/js/Bar.vue": [42] }
 *
 * Only source files are included — test/, vendor/, node_modules/ are excluded.
 */

$workspace = rtrim($argv[1] ?? getcwd(), '/');

$diff = shell_exec(
    'git -C ' . escapeshellarg($workspace)
    . ' diff --unified=0 origin/development..HEAD -- "*.php" "*.vue" "*.js" 2>/dev/null'
);

$result     = [];
$currentFile = null;
$newLineNum  = 0;

foreach (explode("\n", $diff ?? '') as $line) {

    // +++ b/app/Http/Controllers/Admin/AgentController.php
    if (preg_match('#^\+\+\+ b/(.+)$#', $line, $m)) {
        $currentFile = $m[1];
        $result[$currentFile] ??= [];
        continue;
    }

    if ($currentFile === null) continue;

    if (str_starts_with($line, '--- ')
        || str_starts_with($line, 'diff ')
        || str_starts_with($line, 'index ')) {
        continue;
    }

    // Hunk header: @@ -old +new[,count] @@
    if (preg_match('/^@@ -\S+ \+(\d+)/', $line, $m)) {
        $newLineNum = (int) $m[1];
        continue;
    }

    $ch = $line[0] ?? '';

    if ($ch === '+') {
        $result[$currentFile][] = $newLineNum++;
    } elseif ($ch === '-') {
        // removed — does not advance new-file counter
    } else {
        $newLineNum++;  // context line
    }
}

// Drop test files, vendor, node_modules — we only care about source
$result = array_filter($result, static function (string $path): bool {
    $lower = strtolower($path);
    return !str_starts_with($lower, 'tests/')
        && !str_starts_with($lower, 'vendor/')
        && !str_starts_with($lower, 'node_modules/');
}, ARRAY_FILTER_USE_KEY);

echo json_encode($result, JSON_UNESCAPED_SLASHES);
