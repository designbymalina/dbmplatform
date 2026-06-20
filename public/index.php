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

use App\Shared\Installation\InstallationRequirements;
use Dbm\Application;
use Dbm\Core\DotEnv;
use Dbm\Core\Paths;
use Dbm\Http\Emitter\ResponseEmitter;

/**
 * Base path (root aplikacji)
 */
$baseDirectory = realpath(dirname(__DIR__));

if ($baseDirectory === false) {
    http_response_code(500);
    echo 'Cannot resolve base directory.';
    exit;
}

$baseDirectory = rtrim(str_replace('\\', '/', $baseDirectory), '/');

/**
 * Runtime configuration
 */
require_once $baseDirectory . '/bootstrap/runtime.php';

initRuntime($baseDirectory);

try {
    /**
     * Dual Autoloading System (without or with Composer)
     */
    require_once $baseDirectory . '/bootstrap/autoload.php';

    autoloadingWithWithoutComposer(
        $baseDirectory,
        $baseDirectory . '/vendor/autoload.php'
    );

    /**
     * Supporpt functions and global helpers
     */
    require_once $baseDirectory . '/bootstrap/support.php';

    /**
     * Paths system and configuration
     */
    Paths::setBasePath($baseDirectory);
    $envPath = Paths::basePath() . '/.env';

    /**
     * Environment variables
     */
    if (file_exists($envPath)) {
        (new DotEnv($envPath))->load();
    }

    /**
     * Application bootstrap entry point (installation requirements)
     *
     * NOTE: This file is part of the APPLICATION layer.
     * The Dbm framework itself does not require:
     * .env file, database connection, installation process, modules
     * Those are optional application-level features.
     */
    $installer = new InstallationRequirements($envPath);

    if (!$installer->isInstalled()) {
        $installer->checkAndRender();
    }

    /**
     * Application bootstrap
     */
    $appFactory = require Paths::basePath() . '/bootstrap/app.php';

    /** @var Application $app */
    $app = $appFactory();

    /**
     * Run application and Output response
     */
    $response = $app->run();

    (new ResponseEmitter())->emit($response);
} catch (\Throwable $e) {
    /** @var callable(Throwable, string): void $bootstrapHandler */
    require $baseDirectory . '/bootstrap/handler.php';

    bootstrapHandler($e, $baseDirectory);
}
