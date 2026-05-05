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

use Dbm\Debug\DebugToolbarMiddleware;
use Dbm\Middleware\CorsMiddleware;
use Dbm\Middleware\ExceptionMiddleware;
use Dbm\Middleware\RequestToolbarEndMiddleware;
use Dbm\Middleware\RequestToolbarStartMiddleware;
use Dbm\Middleware\RouterMatchMiddleware;
use Dbm\Middleware\StartSessionMiddleware;
use Dbm\Routing\MiddlewareStack;
use Dbm\Security\Middleware\AuthMiddleware;

return function (MiddlewareStack $middleware, $container): void {
    // --- SESSION (pierwsze)
    $middleware->add($container->get(StartSessionMiddleware::class));

    // --- START (globalne)
    $middleware->add(new RequestToolbarStartMiddleware());
    $middleware->add($container->get(ExceptionMiddleware::class));

    // --- CORE (aplikacja)
    $middleware->add(new CorsMiddleware());
    $middleware->add($container->get(RouterMatchMiddleware::class));

    // Auth po routerze (używa route)
    $middleware->add($container->get(AuthMiddleware::class));

    // --- END (debug / dev)
    $middleware->add($container->get(DebugToolbarMiddleware::class));
    $middleware->add(new RequestToolbarEndMiddleware());
};
