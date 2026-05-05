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

namespace App\System\Contracts;

use Dbm\Core\DependencyContainer;

interface SystemModuleInterface
{
    public function register(DependencyContainer $container): void;

    public static function canRegister(): bool;
}
