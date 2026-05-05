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

function bootstrapHandler(Throwable $e, string $baseDirectory): void
{
    $paths = [
        $baseDirectory . '/libraries/designbymalina/dbmframework/src/Infrastructure/Error/BootstrapErrorHandler.php',
        $baseDirectory . '/vendor/designbymalina/dbmframework/src/Infrastructure/Error/BootstrapErrorHandler.php',
    ];

    $loaded = false;

    foreach ($paths as $path) {
        if (is_file($path)) {
            require_once $path;
            $loaded = true;
            break;
        }
    }

    if ($loaded && class_exists(\Dbm\Infrastructure\Error\BootstrapErrorHandler::class)) {
        \Dbm\Infrastructure\Error\BootstrapErrorHandler::handle($e, $baseDirectory);
        return;
    }

    http_response_code(500);
    echo '<h1>Fatal error</h1>';
    echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
}
