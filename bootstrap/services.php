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
 * File related to DependecyContainer()
 *
 * Dependency Injection configuration
 *
 * IMPORTANT:
 * - Only CORE / INFRA services
 * - No App\* services here
 * - Exceptions:
 *   - global application context
 *   - heavy infrastructure adapters
 *
 * Usage:
 * $container->singleton() - Creates one instance for the entire lifecycle
 * $container->set() - Creates a new instance on each request (rarely needed)
 * $container->get() - Gets instance, if singleton same, if set new
 */

declare(strict_types=1);

use Dbm\Core\DependencyContainer;
use Dbm\Core\Module\CoreModuleServiceProvider;
use Dbm\Events\EventDispatcher;
use Dbm\Exceptions\ExceptionHandler;
use Dbm\Http\Contracts\HttpClientInterface;
use Dbm\Http\CurlHttpClient;
use Dbm\Infrastructure\Cookie\CookieManager;
use Dbm\Infrastructure\Filesystem\Filesystem;
use Dbm\Infrastructure\Session\SessionManager;
use Dbm\Views\TemplateEngine;
use Dbm\Infrastructure\Log\Logger;
use Dbm\Kernel\Contracts\KernelInterface;
use Dbm\Kernel\HttpKernel;
use Dbm\Localization\LocalizationServiceProvider;
use Dbm\Routing\RoutingServiceProvider;
use Dbm\Routing\Contracts\UrlGeneratorInterface;
use Dbm\Routing\MiddlewareStack;
use Dbm\Routing\Router;
use Dbm\Security\CsrfTokenManager;
use Dbm\Views\Flash\FlashBag;
use Dbm\Views\ViewServiceProvider;
use Psr\Log\LoggerInterface;

return function (DependencyContainer $container): DependencyContainer {
    // ===== CORE =====

    $container->singleton(
        ExceptionHandler::class,
        fn($c) => new ExceptionHandler(
            $c->get(UrlGeneratorInterface::class),
        )
    );

    $container->singleton(KernelInterface::class, function ($c) {
        return new HttpKernel(
            $c->get(Router::class),
            $c->get(MiddlewareStack::class),
            $c->get(TemplateEngine::class),
            $c->get(UrlGeneratorInterface::class)
        );
    });

    $container->singleton(
        EventDispatcher::class,
        fn() => new EventDispatcher()
    );

    // --- Modules ---

    CoreModuleServiceProvider::register($container);

    // ===== ROUTING =====

    RoutingServiceProvider::register($container);

    // ===== INFRASTRUCTURE =====

    $container->singleton(
        LoggerInterface::class,
        fn() => new Logger()
    );

    $container->singleton(SessionManager::class);

    $container->singleton(CookieManager::class);

    // --- Language and Translation ---

    LocalizationServiceProvider::register($container);

    // --- Filesystem ---

    $container->singleton(Filesystem::class);

    // --- Flash (Views) ---

    $container->singleton(
        FlashBag::class,
        fn($c) => new FlashBag($c->get(SessionManager::class))
    );

    // --- CSRF Token (Security) ---

    $container->singleton(
        CsrfTokenManager::class,
        fn($c) => new CsrfTokenManager(
            $c->get(SessionManager::class)
        )
    );

    // --- API HTTP Client ---

    $container->singleton(
        HttpClientInterface::class,
        fn() => new CurlHttpClient()
    );

    // ===== VIEW =====

    ViewServiceProvider::register($container);

    // ===== End of service definitions =====
    return $container;
};
