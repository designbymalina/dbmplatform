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
use Dbm\Infrastructure\Log\Logger;

final class SystemModuleRegistry
{
    private static ?Logger $logger = null;

    public static function register(DependencyContainer $container): void
    {
        $cacheFile = self::cachePath();
        $modulePath = self::modulePath();

        // safety: brak modułów
        if (!is_dir($modulePath)) {
            return;
        }

        // ensure cache dir
        $dir = dirname($cacheFile);

        if (!is_dir($dir)) {
            mkdir($dir, 0o775, true);
        }

        // load cache (SAFE)
        clearstatcache(true, $cacheFile);

        $modules = self::load($cacheFile);

        // jeśli cache uszkodzony -> fallback rebuild
        if (!self::isCacheUsable($modules, $modulePath)) {
            self::safeWarmUp($cacheFile, $modulePath);

            clearstatcache(true, $cacheFile);

            self::invalidateCache($cacheFile);

            $modules = self::load($cacheFile);
        }

        // jeśli nadal cache uszkodzony - direct scan fallback
        if (!self::isCacheUsable($modules, $modulePath)) {
            self::logger()->warning(
                'System module cache invalid. Using filesystem scan fallback.'
            );

            $modules = self::scanModules($modulePath);
        }

        // total fail
        if ($modules === []) {
            self::logger()->warning(
                'SystemModuleRegistry failed to load any modules.'
            );

            return;
        }

        // 6. register modules
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

    public static function rebuild(): void
    {
        self::safeWarmUp(
            self::cachePath(),
            self::modulePath()
        );
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

    private static function safeWarmUp(string $cacheFile, string $modulePath): void
    {
        try {
            $files = glob($modulePath . '/*Module.php') ?: [];
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

            $tmpFile = $cacheFile . '.' . uniqid('', true);

            file_put_contents(
                $tmpFile,
                self::contentArray($modules, $hash),
                LOCK_EX
            );

            rename($tmpFile, $cacheFile);

            self::invalidateCache($cacheFile);

        } catch (\Throwable) {
            // silent fail (boot must never break)
        }
    }

    /**
     * @return list<class-string<SystemModuleInterface>>
     */
    private static function scanModules(string $path): array
    {
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

        return $modules;
    }

    private static function modulePath(): string
    {
        return Paths::joinPaths(Paths::basePath(), 'src', 'System', 'Module');
    }

    /**
     * @param array<int|string, class-string<SystemModuleInterface>|string> $modules
     */
    private static function isCacheUsable(array $modules, string $path): bool
    {
        if (!isset($modules['__hash'])) {
            return false;
        }

        $files = glob($path . '/*Module.php') ?: [];
        sort($files);

        return $modules['__hash'] === md5(implode('|', $files));
    }

    /**
     * @return array<int|string, class-string<SystemModuleInterface>|string>
     */
    private static function load(string $file): array
    {
        if (!is_file($file)) {
            return [];
        }

        $data = require $file;

        return is_array($data) ? $data : [];
    }

    /**
     * @param array<int|string, class-string<SystemModuleInterface>|string> $modules
     */
    private static function contentArray(array $modules, string $hash): string
    {
        $content = "<?php\n\n// DbM Framework\n// Static modules cache file.\n\nreturn [\n";
        $content .= "    '__hash' => '{$hash}',\n";

        foreach ($modules as $module) {
            $content .= "    {$module}::class,\n";
        }

        $content .= "];\n";

        return $content;
    }

    private static function invalidateCache(string $file): void
    {
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($file, true);
        }
    }

    private static function logger(): Logger
    {
        if (self::$logger === null) {
            self::$logger = new Logger();
        }

        return self::$logger;
    }
}
