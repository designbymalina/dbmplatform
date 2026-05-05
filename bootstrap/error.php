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

function initErrorHandling(string $baseDirectory): void
{
    error_reporting(E_ALL);

    $env = $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'production';
    $isProd = $env === 'production';

    ini_set('display_errors', $isProd ? '0' : '1');
    ini_set('log_errors', '1');

    set_error_handler(function ($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        throw new \ErrorException($message, 0, $severity, $file, $line);
    });

    set_exception_handler(function (\Throwable $e) use ($baseDirectory) {
        require $baseDirectory . '/bootstrap/handler.php';
        bootstrapHandler($e, $baseDirectory);
    });

    register_shutdown_function(function () use ($baseDirectory) {
        $error = error_get_last();

        if ($error && in_array($error['type'], [
            E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR,
        ])) {
            require $baseDirectory . '/bootstrap/handler.php';

            bootstrapHandler(
                new \ErrorException(
                    $error['message'],
                    0,
                    $error['type'],
                    $error['file'],
                    $error['line']
                ),
                $baseDirectory
            );
        }
    });
}
