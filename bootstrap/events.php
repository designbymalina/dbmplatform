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

use App\System\Listener\RebuildSystemModulesCacheListener;
use Dbm\Core\Module\Events\ModulesChangedEvent;

return [
    ModulesChangedEvent::class => [
        RebuildSystemModulesCacheListener::class,
    ],
];
