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
 * @INFO Można rozszerzyć o: classmap cache, brak globów w runtime, OPcache...
 * $cacheFile = $base . '/storage/cache/classmap.php';
 */

declare(strict_types=1);

/**
 * Registers hybrid autoloader supporting:
 * - Composer autoload (vendor/)
 * - Custom PSR-4 application namespaces
 * - Bundled libraries (local / embedded packages)
 * - Runtime module bundles (cache-based)
 *
 * Loading order:
 * 1. Composer autoloader (if exists)
 * 2. PSR-4 application modules (App/, Mod/)
 * 3. Runtime + static bundles
 * 4. Bundled libraries (local framework ecosystem)
 *
 * This allows full CMS operation WITHOUT Composer,
 * while still supporting Composer-based deployments.
 *
 * @param string $baseDirectory Absolute path to project root
 * @param string $pathComposerAutoload Path to vendor/autoload.php
 * @return void
 */
function autoloadingWithWithoutComposer(
    string $baseDirectory,
    string $pathComposerAutoload
): void {
    // Composer (optional)
    if (is_file($pathComposerAutoload)) {
        require_once $pathComposerAutoload;
    }

    $base = rtrim($baseDirectory, DIRECTORY_SEPARATOR);

    // ===== Application PSR-4 namespaces =====

    $psr4 = [
        'App\\' => $base . '/src/',
        'Mod\\' => $base . '/modules/',
    ];

    // ===== Bundled libraries (framework ecosystem) =====

    $libraries = [
        'Psr\\Http\\Message\\' => $base . '/libraries/psr/http-message/src/',
        'Psr\\Http\\Client\\'  => $base . '/libraries/psr/http-client/src/',
        'Psr\\Http\\Server\\'  => $base . '/libraries/psr/http-server/src/',
        'Psr\\Log\\'           => $base . '/libraries/psr/log/src/',

        // Core framework (Dbm ecosystem)
        'Dbm\\' => $base . '/libraries/designbymalina/dbmframework/src/',

        // External bundled libs
        'PHPMailer\\PHPMailer\\' => $base . '/libraries/phpmailer/src/',
        'GuzzleHttp\\Promise\\'  => $base . '/libraries/guzzlehttp/promise/src/',
        'GuzzleHttp\\Psr7\\'     => $base . '/libraries/guzzlehttp/psr7/src/',
        'GuzzleHttp\\'           => $base . '/libraries/guzzlehttp/guzzle/src/',
    ];

    // ===== Runtime + static bundles (modules, features) =====

    $bundles = [];

    // Auto bundle discovery
    foreach (glob($base . '/libraries/*/bundle.php') as $bundleFile) {
        $bundles += require $bundleFile;
    }

    $runtimeBundles = $base . '/storage/framework/bundles.php';

    if (is_file($runtimeBundles)) {
        $bundles += require $runtimeBundles;
    }

    // Merge order: most specific first
    $maps = $psr4 + $bundles + $libraries;

    spl_autoload_register(
        static function (string $class) use ($maps): void {
            // Prevent double-loading (Composer already resolved it)
            if (class_exists($class, false)) {
                return;
            }

            if ($class === '' || $class[0] === '_') {
                return;
            }

            foreach ($maps as $prefix => $dir) {
                if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
                    continue;
                }

                $file = $dir
                    . str_replace('\\', '/', substr($class, strlen($prefix)))
                    . '.php';

                if (is_file($file)) {
                    require $file;
                }

                return;
            }
        },
        true,
        false // Important: Composer stays primary
    );
}
