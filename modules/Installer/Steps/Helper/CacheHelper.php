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

namespace Mod\Installer\Steps\Helper;

use Dbm\Core\Paths;
use Dbm\Infrastructure\Filesystem\Filesystem;

final class CacheHelper
{
    public static function clearCache(): void
    {
        $cacheDir = Paths::joinPaths(Paths::varPath(), 'cache');

        if (is_dir($cacheDir)) {
            (new Filesystem())->deleteDir($cacheDir);
        }
    }
}
