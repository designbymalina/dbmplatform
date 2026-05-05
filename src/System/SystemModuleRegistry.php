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

        $modules = require $cacheFile;

        if (empty($modules)) {
            // jeśli katalog też pusty
            if (!self::hasModuleFiles($modulePath)) {
                return;
            }

            self::warmUp($cacheFile);
            $modules = require $cacheFile;
        }

        // count mismatch - rebuild
        if (!self::isCountValid($modules, $modulePath)) {
            self::warmUp($cacheFile);

            clearstatcache(true, $cacheFile);

            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($cacheFile, true);
            }

            $modules = require $cacheFile;

            // dalej źle - exception
            if (!self::isCountValid($modules, $modulePath)) {
                throw new \RuntimeException(
                    "System module cache mismatch. Remove cache file: " . basename($cacheFile)
                );
            }
        }

        // rejestracja
        foreach ($modules as $class) {
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

        $modules = [];

        foreach (glob($path . '/*Module.php') as $file) {
            $class = 'App\\System\\Module\\' . basename($file, '.php');

            if (!class_exists($class)) {
                require_once $file;
            }

            if (is_subclass_of($class, SystemModuleInterface::class)) {
                $modules[] = $class;
            }
        }

        file_put_contents($cacheFile, self::contentArray($modules), LOCK_EX);
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
     * @param list<class-string<SystemModuleInterface>> $modules
     */
    private static function contentArray(array $modules): string
    {
        $content = "<?php\n\n// Application: DbM Framework\n// System Module cache file.\n\nreturn [\n";

        foreach ($modules as $module) {
            $content .= "    {$module}::class,\n";
        }

        $content .= "];\n";

        return $content;
    }

    /**
     * @INFO Count można zamienić na md5(implode('|', glob(...)))
     *
     * @param list<class-string<SystemModuleInterface>> $modules
     */
    private static function isCountValid(array $modules, string $path): bool
    {
        if (empty($modules)) {
            return false;
        }

        if (!is_dir($path)) {
            return false;
        }

        $countModules = count($modules);

        $fi = new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS);

        $countFiles = 0;

        foreach ($fi as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), 'Module.php')) {
                $countFiles++;
            }
        }

        return $countModules === $countFiles;
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
}
