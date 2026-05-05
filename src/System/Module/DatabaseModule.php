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

namespace App\System\Module;

use App\Infrastructure\Database\DatabaseFactory;
use App\System\Contracts\SystemModuleInterface;
use Dbm\Core\Config\AppConfig;
use Dbm\Core\DependencyContainer;
use Dbm\Database\Contracts\DatabaseInterface;

final class DatabaseModule implements SystemModuleInterface
{
    public function register(DependencyContainer $container): void
    {
        $container->singleton(
            DatabaseInterface::class,
            fn() => DatabaseFactory::createDatabase()
        );
    }

    public static function canRegister(): bool
    {
        return AppConfig::hasDatabase();
    }

    // @INFO Priority to półśrodek docelowo dependency graph
    // Optional: default fallback = 0
    // public static function getPriority(): int
    // {
    //     return 100; // wyższa liczba = wcześniej
    // }
}
