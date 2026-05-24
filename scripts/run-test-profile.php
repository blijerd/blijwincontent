#!/usr/bin/env php
<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$profile = $argv[1] ?? null;
$arguments = array_slice($argv, 2);

if (! is_string($profile) || $profile === '') {
    fwrite(STDERR, "Usage: php scripts/run-test-profile.php <dev|smoke|fast|architecture|cms|content|blijwinos|downloads|faq|tracking|full> [selector-or-path ...] [artisan-test-options]\n");

    exit(1);
}

$selectors = [];
$passthrough = [];

foreach ($arguments as $argument) {
    if ($argument === '--') {
        continue;
    }

    if (str_starts_with($argument, '--')) {
        $passthrough[] = $argument;

        continue;
    }

    $selectors[] = $argument;
}

$profileSubjects = [
    'dev' => 'composer test:dev',
    'smoke' => 'composer test:smoke',
    'fast' => 'composer test:fast',
    'architecture' => 'composer test:architecture',
    'cms' => 'composer test:cms',
    'content' => 'composer test:content',
    'blijwinos' => 'composer test:blijwinos',
    'downloads' => 'composer test:downloads',
    'faq' => 'composer test:faq',
    'tracking' => 'composer test:tracking',
    'full' => 'composer test:full',
];

if (! array_key_exists($profile, $profileSubjects)) {
    fwrite(STDERR, sprintf("Unknown test profile [%s]. Expected one of: %s.\n", $profile, implode(', ', array_keys($profileSubjects))));

    exit(1);
}

runCommand([PHP_BINARY, 'artisan', 'config:clear', '--ansi'], $root);

$paths = match ($profile) {
    'dev' => developmentTestPaths($root, $selectors),
    'smoke' => smokeTestPaths($root),
    'fast' => fastTestPaths($root),
    'architecture' => architectureTestPaths($root),
    'cms' => cmsTestPaths($root, $selectors),
    'content' => contentTestPaths($root, $selectors),
    'blijwinos' => namedTestPaths($root, ['tests/Feature/Blijwinos']),
    'downloads' => namedTestPaths($root, ['tests/Feature/Downloads']),
    'faq' => namedTestPaths($root, ['tests/Feature/Faq', 'tests/Unit/Faq']),
    'tracking' => namedTestPaths($root, ['tests/Feature/Tracking']),
    'full' => [],
};

if ($profile === 'architecture' && $paths === []) {
    fwrite(STDOUT, "No architecture test files exist yet; profile passed without running PHPUnit.\n");

    exit(0);
}

if (hasParallelOption($passthrough) && count($paths) > 1) {
    fwrite(STDERR, "Laravel's parallel test runner accepts one path argument in this project. Use --parallel with one explicit selector, or run the profile without --parallel.\n");

    exit(1);
}

$testCommand = array_merge([PHP_BINARY, 'artisan', 'test'], $passthrough);

if ($paths !== []) {
    $testCommand = array_merge($testCommand, $paths);
}

runCommand($testCommand, $root);

/**
 * @param list<string> $selectors
 * @return list<string>
 */
function developmentTestPaths(string $root, array $selectors): array
{
    if ($selectors === []) {
        return smokeTestPaths($root);
    }

    $paths = [];

    foreach ($selectors as $selector) {
        $paths = array_merge($paths, resolveSelector($root, $selector));
    }

    $paths = deduplicateExistingPaths($root, $paths);

    if ($paths === []) {
        fwrite(STDERR, "No dev test paths matched the provided selector(s). Refusing to fall back to the full suite.\n");

        exit(1);
    }

    return $paths;
}

/** @return list<string> */
function smokeTestPaths(string $root): array
{
    return namedTestPaths($root, [
        'tests/Unit/Cms',
        'tests/Feature/Cms/PageRenderingTest.php',
        'tests/Feature/Cms/SitemapTest.php',
    ]);
}

/** @return list<string> */
function fastTestPaths(string $root): array
{
    return namedTestPaths($root, [
        'tests/Unit',
        'tests/Feature',
    ]);
}

/** @return list<string> */
function architectureTestPaths(string $root): array
{
    $paths = [];

    foreach (['tests', 'app'] as $directory) {
        $absoluteDirectory = $root.DIRECTORY_SEPARATOR.$directory;

        if (! is_dir($absoluteDirectory)) {
            continue;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($absoluteDirectory, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $fileInfo) {
            if (! $fileInfo instanceof SplFileInfo || ! $fileInfo->isFile()) {
                continue;
            }

            $filename = $fileInfo->getFilename();

            if (! str_ends_with($filename, 'Test.php')) {
                continue;
            }

            $contents = file_get_contents($fileInfo->getPathname());

            if (
                is_string($contents)
                && (
                    str_contains($contents, 'RefreshDatabase')
                    || str_contains($contents, 'DatabaseMigrations')
                    || str_contains($contents, 'DatabaseTransactions')
                    || str_contains($contents, 'LazilyRefreshDatabase')
                )
            ) {
                continue;
            }

            if (str_contains($filename, 'Architecture') || str_contains($filename, 'Policy')) {
                $paths[] = relativePath($root, $fileInfo->getPathname());
            }
        }
    }

    sort($paths);

    return array_values(array_unique($paths));
}

/**
 * @param list<string> $selectors
 * @return list<string>
 */
function cmsTestPaths(string $root, array $selectors): array
{
    if ($selectors !== []) {
        return selectedPaths($root, $selectors);
    }

    return namedTestPaths($root, [
        'tests/Unit/Cms',
        'tests/Feature/Cms',
    ]);
}

/**
 * @param list<string> $selectors
 * @return list<string>
 */
function contentTestPaths(string $root, array $selectors): array
{
    if ($selectors !== []) {
        return selectedPaths($root, $selectors);
    }

    return namedTestPaths($root, [
        'tests/Unit/Cms',
        'tests/Unit/Faq',
        'tests/Feature/Cms',
        'tests/Feature/Downloads',
        'tests/Feature/Faq',
    ]);
}

/**
 * @param list<string> $selectors
 * @return list<string>
 */
function selectedPaths(string $root, array $selectors): array
{
    $paths = [];

    foreach ($selectors as $selector) {
        $paths = array_merge($paths, resolveSelector($root, $selector));
    }

    $paths = deduplicateExistingPaths($root, $paths);

    if ($paths === []) {
        fwrite(STDERR, "No test paths matched the provided selector(s).\n");

        exit(1);
    }

    return $paths;
}

/**
 * @param list<string> $paths
 * @return list<string>
 */
function namedTestPaths(string $root, array $paths): array
{
    return deduplicateExistingPaths($root, $paths);
}

/** @return list<string> */
function resolveSelector(string $root, string $selector): array
{
    $selector = normalizePath($selector);

    if ($selector === '') {
        return [];
    }

    $directPath = $root.DIRECTORY_SEPARATOR.$selector;

    if (file_exists($directPath)) {
        return [$selector];
    }

    $featurePath = 'tests/Feature/'.$selector;

    if (file_exists($root.DIRECTORY_SEPARATOR.$featurePath)) {
        return [$featurePath];
    }

    $unitPath = 'tests/Unit/'.$selector;

    if (file_exists($root.DIRECTORY_SEPARATOR.$unitPath)) {
        return [$unitPath];
    }

    $studly = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $selector)));

    foreach ([
        'tests/Feature/'.$studly,
        'tests/Unit/'.$studly,
        'tests/Feature/'.$studly.'Test.php',
        'tests/Unit/'.$studly.'Test.php',
    ] as $candidate) {
        if (file_exists($root.DIRECTORY_SEPARATOR.$candidate)) {
            return [$candidate];
        }
    }

    fwrite(STDERR, sprintf("Warning: no test path found for selector [%s].\n", $selector));

    return [];
}

/** @param list<string> $arguments */
function hasParallelOption(array $arguments): bool
{
    foreach ($arguments as $argument) {
        if ($argument === '--parallel' || str_starts_with($argument, '--parallel=')) {
            return true;
        }
    }

    return false;
}

/**
 * @param list<string> $paths
 * @return list<string>
 */
function deduplicateExistingPaths(string $root, array $paths): array
{
    $existing = [];

    foreach ($paths as $path) {
        $path = normalizePath($path);

        if ($path === '') {
            continue;
        }

        if (file_exists($root.DIRECTORY_SEPARATOR.$path)) {
            $existing[] = $path;
        }
    }

    return array_values(array_unique($existing));
}

function normalizePath(string $path): string
{
    return trim(str_replace(DIRECTORY_SEPARATOR, '/', $path), '/');
}

function relativePath(string $root, string $absolutePath): string
{
    $root = rtrim($root, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

    if (str_starts_with($absolutePath, $root)) {
        return normalizePath(substr($absolutePath, strlen($root)));
    }

    return normalizePath($absolutePath);
}

/** @param list<string> $command */
function runCommand(array $command, string $cwd): void
{
    fwrite(STDOUT, '$ '.implode(' ', array_map('shellDisplay', $command)).PHP_EOL);

    $descriptors = [
        0 => STDIN,
        1 => STDOUT,
        2 => STDERR,
    ];

    $process = proc_open($command, $descriptors, $pipes, $cwd);

    if (! is_resource($process)) {
        fwrite(STDERR, 'Unable to start command.'.PHP_EOL);

        exit(1);
    }

    $exitCode = proc_close($process);

    if ($exitCode !== 0) {
        exit($exitCode);
    }
}

function shellDisplay(string $value): string
{
    if (preg_match('/^[A-Za-z0-9_\\-\\.\\/=:@]+$/', $value) === 1) {
        return $value;
    }

    return escapeshellarg($value);
}
