# Creating Your First Controller and Service

This document shows how to create and run your first controller and service in DBM Framework.

---

## Application Entry Points

DBM Framework uses PHP-based routing.

* Web entry point: `/`
* API entry point: `/api`

Routing configuration is loaded from:

```bash
application/web.php
application/api.php
```

---

## Controller Location

Controllers should be placed in:

```bash
src/Controller
```

---

## Example Controller

```php
declare(strict_types=1);

namespace App\Controller;

use App\Service\IndexService;
use Dbm\Http\Controller\BaseController;
use Dbm\Views\Flash\FlashBag;
use Psr\Http\Message\ResponseInterface;

class IndexController extends BaseController
{
    public function __construct(
        private readonly IndexService $service,
        private readonly FlashBag $flash
    ) {}

    /**
     * Index page
     * @routing GET '/' name: home
     */
    public function index(): ResponseInterface
    {
        $this->flash->set(
            'Your application is now ready and you can start working on a new project.'
        );

        return $this->render('index/start.phtml', [
            'meta' => $this->service->getMetaIndex(),
        ]);
    }

    /**
     * Start page
     * @routing GET '/start' name: start
     */
    public function start(): ResponseInterface
    {
        return $this->render('index/start.phtml', [
            'meta' => $this->service->getMetaStart(),
        ]);
    }
}
```

---

## Service Location

Services should be placed in:

```bash
src/Service
```

---

## Example Service

```php
declare(strict_types=1);

namespace App\Service;

use Dbm\Localization\Translation;

class IndexService
{
    public function __construct(
        private readonly Translation $translation,
    ) {}

    public function getMetaIndex(): array
    {
        return [
            'meta.title' => "Your Web Application Name",
            'meta.description' => "Web application description...",
            'meta.keywords' => "application keywords",
        ];
    }

    public function getMetaStart(): array
    {
        return [
            'meta.title' => $this->translation->trans('index.start_meta_title'),
            'meta.description' => $this->translation->trans('index.start_meta_description'),
            'meta.keywords' => $this->translation->trans('index.start_meta_keywords'),
            'meta.robots' => "noindex,nofollow",
        ];
    }
}
```

---

## Routing

Routes are defined manually in:

```bash
application/web.php
application/api.php
```

The `@routing` annotation in PHPDoc is **informational only** and does not register routes automatically.

---

## Dependency Injection

DBM uses automatic dependency injection.

### Constructor Injection (recommended)

```php
public function __construct(
    private IndexService $service,
    private FlashBag $flash
) {}
```

Dependencies are resolved automatically from the DI container.

---

### Method Injection (optional)

```php
public function example(Request $request): ResponseInterface
```

---

## Flash Messages

Flash messages are handled via `FlashBag`:

```php
$this->flash->set('Message text');
```

They are typically displayed once (next request).

---

## Translations

Translations are handled via the `Translation` service.

### Example

```php
$this->translation->trans('key');
```

### With parameters

```php
$this->translation->trans('hello', ['name' => 'John']);
```

---

### Translation Files

Stored in:

```bash
translations/
```

Example:

```php
return [
    'index.start_meta_title' => 'Start page',
];
```

---

## Views / Templates

Templates are located in:

```bash
templates/
```

Render using:

```php
$this->render('index/start.phtml', [...]);
```

---

## Logger (Optional)

You can inject the logger into services or controllers:

```php
use Dbm\Infrastructure\Log\Logger;

public function __construct(
    private Logger $logger
) {}
```

---

## Example Usage in Service

```php
$this->logger->info('Meta generated');
```

---

## Full Flow

1. Request hits `/`
2. Router resolves route
3. Controller is created via DI
4. Dependencies are injected
5. Action is executed
6. View is rendered
7. Response is returned

---

## Running the Application

1. Point your web server to:

```bash
public/
```

2. Open:

```bash
http://localhost/
```

---

## Summary

This example demonstrates:

* Clean controller structure
* Automatic dependency injection
* Service-based architecture
* Translation integration
* Flash messaging
* Template rendering

DBM encourages:

* thin controllers
* reusable services
* explicit architecture

---
