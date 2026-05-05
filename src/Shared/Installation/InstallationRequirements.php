<?php

/**
 * Application: DbM Framework
 * A lightweight PHP framework for building web applications.
 *
 * @author Artur Malinowski
 * @copyright Design by Malina (All Rights Reserved)
 * @license MIT
 * @link https://www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Shared\Installation;

use Dbm\Core\Paths;

final class InstallationRequirements
{
    private const PHP_VERSION = '8.1';

    private string $envPath;
    private string $lockFile;

    /** @var array<int, string> */
    private array $issues = [];

    public function __construct(string $envPath)
    {
        $this->envPath = $envPath;
        $this->lockFile = Paths::joinPaths(
            Paths::basePath(),
            'storage',
            'framework',
            'installed.lock'
        );
    }

    public function isInstalled(): bool
    {
        return is_file($this->envPath) && is_file($this->lockFile);
    }

    public function checkAndRender(): void
    {
        $this->checkPhpVersion(self::PHP_VERSION);
        $this->checkConfig($this->envPath);
        $this->checkDirectories($this->getRequiredDirs());

        if ($this->hasIssues()) {
            $this->renderAndExit();
        }
    }

    // ===== Private =====

    /** @return array<int, string> */
    public function getRequiredDirs(): array
    {
        return [
            Paths::joinPaths(Paths::basePath(), 'var'),
            Paths::joinPaths(Paths::basePath(), 'storage'),
            Paths::joinPaths(Paths::basePath(), 'storage', 'cache'),
        ];
    }

    private function checkConfig(string $pathConfig): void
    {
        if (!is_file($pathConfig)) {
            $this->issues[] = '.env file not found (rename .env.example to .env)';
        }
    }

    /**
     * @param array<int, string> $paths
     */
    private function checkDirectories(array $paths): void
    {
        foreach ($paths as $path) {
            if (!is_dir($path)) {
                if (!@mkdir($path, 0o755, true) && !is_dir($path)) {
                    $this->issues[] = "Cannot create directory: {$path}";
                    continue;
                }
            }

            if (!$this->isReallyWritable($path)) {
                $this->issues[] = "Directory not writable: {$path} (chmod -R 775 storage)";
            }
        }
    }

    private function checkPhpVersion(string $required): void
    {
        if (version_compare(PHP_VERSION, $required, '<')) {
            $this->issues[] = "PHP {$required}+ required, current: " . PHP_VERSION;
        }
    }

    /*
     * Optional method.
     *
     * @param array<int, string> $extensions
     *
    private function checkExtensions(array $extensions): void
    {
        foreach ($extensions as $ext) {
            if (!extension_loaded($ext)) {
                $this->issues[] = "Missing PHP extension: {$ext}";
            }
        }
    } */

    private function isReallyWritable(string $path): bool
    {
        if (!is_writable($path)) {
            return false;
        }

        $testFile = $path . '/.__writable_test';

        if (@file_put_contents($testFile, 'test') === false) {
            return false;
        }

        @unlink($testFile);

        return true;
    }

    private function hasIssues(): bool
    {
        return $this->issues !== [];
    }

    private function renderAndExit(): void
    {
        http_response_code(503);

        echo $this->render();

        exit;
    }

    private function render(): string
    {
        $items = '';

        foreach ($this->issues as $issue) {
            $items .= '<li>' . htmlspecialchars($issue) . '</li>';
        }

        return <<<HTML
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="utf-8">
                <title>DbM Framework - Installation Requirements</title>
                <style>
                    .dbm-ex-root, .dbm-ex-root * { all: revert; box-sizing: border-box; }
                    .dbm-ex-root { font-family: monospace; font-size: 16px; background: #f4f4f4; color: #333; padding: 2rem; }
                    .container { max-width: 992px; margin: auto; background: #fff; border-radius: 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); overflow: hidden; }
                    .header { display: flex; justify-content: space-between; padding: 1rem; background: #e11d48; color: #fff; font-weight: normal; }
                    .page { font-size: 1.4rem; font-weight: bold; }
                    .navigation { font-size: 1rem; }
                    .title { font-size: 0.9rem; color: #cbd5e1; }
                    .description { text-align: right; }
                    .description a { font-size: 0.7rem; color: #cbd5e1; text-decoration: none; text-transform: uppercase; }
                    .main { padding: 2rem; }
                    .main p { margin-top: 0.5rem; margin-bottom: 0.5rem; }
                    .message { font-family: "Lucida Console", monospace; padding: 1rem; border: 1px solid #111827; background: #334155; color: #fff; border-radius: 5px; word-break: break-all; }
                    .info { padding: 1rem; background: #f3f4f6; border: 1px solid #d1d5db; color: #000; margin-top: 1rem; border-radius: 5px; word-break: break-all; }
                    .info h1 { margin: 0; padding: 0; font-size: 1.6rem; }
                    .info ul { margin-top: 10px; margin-bottom: 0px; padding-left: 30px; }
                </style>
            </head>
            <body class="dbm-ex-root">
                <div class="container">
                    <div class="header">
                        <div class="page">Installation Requirements</div>
                        <div class="navigation">
                            <div class="title">DbM Framework Exception</div>
                            <div class="description"><a href="https://dbm.org.pl/">Go To Project</a></div>
                        </div>
                    </div>
                    <div class="main">
                        <div class="info">
                            <h1>System requirements not met</h1>
                            <ul>{$items}</ul>
                        </div>
                    </div>
                </div>
            </body>
            </html>
            HTML;
    }
}
