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

use App\System\SystemModuleRegistry;
use Dbm\Application;
use Dbm\Core\DependencyContainer;
use Dbm\Core\Module\ModuleBootstrapper;
use Dbm\Routing\RouteBuilder;
use Dbm\Routing\MiddlewareStack;
use Dbm\Core\Module\ModuleRegistry;
use Dbm\Events\EventDispatcher;
use Dbm\Events\EventServiceProvider;

return function (): Application {
    // ===== Dependency Injection Container =====
    $container = new DependencyContainer();

    // ===== Register Core Services =====
    (require __DIR__ . '/services.php')($container);

    // ===== Register System Infrastructure =====
    $dispatcher = $container->get(EventDispatcher::class);
    (new EventServiceProvider())->register($dispatcher);

    SystemModuleRegistry::register($container); // App

    // ===== Bootstrap Modules =====
    $container->get(ModuleBootstrapper::class)->bootModules();

    // ===== Routes =====
    $routeBuilder = $container->get(RouteBuilder::class);

    (require __DIR__ . '/web.php')($routeBuilder, $container);

    $routeBuilder->group('/api', function ($routes) use ($container) {
        (require __DIR__ . '/api.php')($routes, $container);
    });

    // ===== Module Routes =====
    $container->get(ModuleRegistry::class)->registerRoutes($routeBuilder);

    // ===== Middleware =====
    $middleware = $container->get(MiddlewareStack::class);
    (require __DIR__ . '/middleware.php')($middleware, $container);

    // ===== Application =====
    return new Application($container);
};
