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

use App\Controller\Api\ExampleApiController;
use App\Controller\Api\IndexApiController;
use Dbm\Core\DependencyContainer;
use Dbm\Core\Module\Contracts\ApiModuleInterface;
use Dbm\Core\Module\ModuleRegistry;
use Dbm\Routing\RouteBuilder;
use Dbm\Security\Middleware\AuthMiddleware;
use Dbm\Security\Middleware\JwtAuthMiddleware;

return function (RouteBuilder $routes, DependencyContainer $container): void {
    $routes->get('/', [IndexApiController::class, 'index'], 'api_index');

    $routes->group('/example', function (RouteBuilder $routes): void {
        $routes->get('/', [ExampleApiController::class, 'list'], 'api_example_list');
        // $routes->get('/{id}', [ExampleApiController::class, 'get'], 'api_example_get');
        // $routes->post('/', [ExampleApiController::class, 'create'], 'api_example_create');
        // $routes->put('/{id}', [ExampleApiController::class, 'update'], 'api_example_update');
        // $routes->delete('/{id}', [ExampleApiController::class, 'delete'], 'api_example_delete');
    }, [JwtAuthMiddleware::class, AuthMiddleware::class]);

    // ===== Module routes =====

    $moduleRegistry = $container->get(ModuleRegistry::class);

    foreach ($moduleRegistry->all() as $module) {
        if ($module instanceof ApiModuleInterface) {
            $module->registerApiRoutes($routes);
        }
    }
};
