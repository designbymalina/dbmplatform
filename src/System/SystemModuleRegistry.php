<?php

/**
 * Application: DbM Framework
 * A lightweight PHP framework for building web applications.
 *
 * @author Artur Malinowski
 * @copyright Design by Malina (All Rights Reserved)
 * @license MIT
 * @link https://www.dbm.org.pl
 *
 * @INFO Można rozszerzyć o Dependency Graph dla sortowania kolejności.
 *
 * Odświeżenie cache:
 * php bin/cache:warmup or
 * SystemModuleRegistry::warmUp($cacheFile);
 */

declare(strict_types=1);

namespace App\System;

use App\System\Contracts\SystemModuleInterface;
use Dbm\Core\DependencyContainer;
use Dbm\Core\Paths;
use FilesystemIterator;

final class SystemModuleRegistry
{
    public static function register(DependencyContainer $container): void
    {
        $cacheFile = self::cachePath();
        $modulePath = self::modulePath();

        // brak katalogu = brak system modules
        if (!is_dir($modulePath)) {
            return;
        }

        // ensure cache dir
        $dir = dirname($cacheFile);

        if (!is_dir($dir)) {
            mkdir($dir, 0o775, true);
        }

        clearstatcache(true, $cacheFile);

        // brak cache - build
        if (!file_exists($cacheFile)) {
            self::warmUp($cacheFile);
        }

        if (!file_exists($cacheFile)) {
            throw new \RuntimeException("System module cache missing.");
        }

        $modules = self::load($cacheFile);

        if (count($modules) <= 1) {
            // jeśli katalog też pusty
            if (!self::hasModuleFiles($modulePath)) {
                return;
            }

            self::warmUp($cacheFile);
            $modules = self::load($cacheFile);
        }

        // count mismatch - rebuild
        if (!self::isValid($modules, $modulePath)) {
            self::warmUp($cacheFile);

            clearstatcache(true, $cacheFile);

            self::invalidateCache($cacheFile);

            $modules = self::load($cacheFile);

            // dalej źle - exception
            if (!self::isValid($modules, $modulePath)) {
                throw new \RuntimeException(
                    "System module cache mismatch. Remove cache file: " . basename($cacheFile)
                );
            }
        }

        // rejestracja
        foreach ($modules as $key => $class) {
            if ($key === '__hash') {
                continue;
            }

            if (!is_subclass_of($class, SystemModuleInterface::class)) {
                continue;
            }

            if (!$class::canRegister()) {
                continue;
            }

            (new $class())->register($container);
        }
    }

    public static function warmUp(string $cacheFile): void
    {
        $path = self::modulePath();

        $files = glob($path . '/*Module.php') ?: [];

        sort($files);

        $modules = [];

        foreach ($files as $file) {
            $class = 'App\\System\\Module\\' . basename($file, '.php');

            if (!class_exists($class, false)) {
                require_once $file;
            }

            if (is_subclass_of($class, SystemModuleInterface::class)) {
                $modules[] = $class;
            }
        }

        $hash = md5(implode('|', $files));

        // zapis atomowy (rename w systemie plików jest atomowe)
        $tmpFile = $cacheFile . '.' . uniqid('', true);

        file_put_contents($tmpFile, self::contentArray($modules, $hash), LOCK_EX);

        if (!rename($tmpFile, $cacheFile)) {
            unlink($tmpFile);
            throw new \RuntimeException('Failed to write system module cache.');
        }

        self::invalidateCache($cacheFile);
    }

    public static function cachePath(): string
    {
        return Paths::joinPaths(
            Paths::basePath(),
            'storage',
            'cache',
            'system_modules.php'
        );
    }

    private static function modulePath(): string
    {
        return Paths::joinPaths(Paths::basePath(), 'src', 'System', 'Module');
    }

    /**
     * @param array<int|string, class-string<SystemModuleInterface>|string> $modules
     */
    private static function contentArray(array $modules, string $hash): string
    {
        $content = "<?php\n\nreturn [\n";
        $content .= "    '__hash' => '{$hash}',\n\n";

        foreach ($modules as $module) {
            $content .= "    {$module}::class,\n";
        }

        $content .= "];\n";

        return $content;
    }

    /**
     * @param array<int|string, class-string<SystemModuleInterface>|string> $modules
     */
    private static function isValid(array $modules, string $path): bool
    {
        if (!isset($modules['__hash'])) {
            return false;
        }

        $files = glob($path . '/*Module.php') ?: [];
        sort($files);

        $currentHash = md5(implode('|', $files));

        return $modules['__hash'] === $currentHash;
    }

    private static function hasModuleFiles(string $path): bool
    {
        foreach (new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS) as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), 'Module.php')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int|string, class-string<SystemModuleInterface>|string>
     */
    private static function load(string $file): array
    {
        $data = require $file;

        return is_array($data) ? $data : [];
    }

    private static function invalidateCache(string $file): void
    {
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($file, true);
        }
    }
}
