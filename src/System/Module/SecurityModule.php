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

use App\System\Contracts\SystemModuleInterface;
use Dbm\Core\DependencyContainer;
use Dbm\Security\Contracts\AccessControlInterface;
use Dbm\Security\Contracts\UserRoleProviderInterface;
use Dbm\Security\NullAccessControl;
use Dbm\Security\NullUserRoleProvider;

final class SecurityModule implements SystemModuleInterface
{
    public function register(DependencyContainer $container): void
    {
        // --- UserRoleProvider ---
        $container->singleton(
            UserRoleProviderInterface::class,
            fn() => new NullUserRoleProvider()
        );

        // --- AccessControl (fallback) ---
        $container->singleton(
            AccessControlInterface::class,
            fn() => new NullAccessControl()
        );
    }

    public static function canRegister(): bool
    {
        return true;
    }
}
