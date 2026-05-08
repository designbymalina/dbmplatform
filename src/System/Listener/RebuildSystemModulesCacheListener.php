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

namespace App\System\Listener;

use App\System\SystemModuleRegistry;
use Dbm\Core\Module\Events\ModulesChangedEvent;

final class RebuildSystemModulesCacheListener
{
    // Optional $event for future use: log, debug, selective rebuild
    public function handle(ModulesChangedEvent $event): void
    {
        SystemModuleRegistry::rebuild();
    }
}
