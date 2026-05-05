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
 * Test command: vendor/bin/phpunit tests/App/ExampleUnitTest.php
 */

declare(strict_types=1);

use Dbm\Core\Paths;

// --- Resolve base path ---
$basePath = realpath(dirname(__DIR__));

if ($basePath === false) {
    throw new RuntimeException('Cannot resolve base directory path.');
}

$basePath = rtrim(str_replace('\\', '/', $basePath), '/');

// --- Paths ---
Paths::setBasePath($basePath);

// --- Autoload ---
require_once Paths::joinPaths(Paths::basePath(), 'vendor', 'autoload.php');
