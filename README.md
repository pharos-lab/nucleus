# Nucleus Framework

Nucleus is a **lightweight PHP framework** designed for building modern web applications.
It provides a **minimal core** with routing, middleware, dependency injection, HTTP abstraction, and view rendering, allowing developers to build applications on top of it without being opinionated about structure or additional tools.

---

## Features

* **Routing**

  * Supports `GET` and `POST` routes.
  * Named routes and URL generation.
  * Parameter constraints and route parameters extraction.
  * Middleware support (global and route-specific).

* **Middleware Pipeline**

  * PSR‑7 compatible.
  * Middleware stack is composable and runs before the controller/action.

* **Controllers**

  * Base controller with helpers:

    * `view()` – Render PHP templates.
    * `json()` – Return JSON responses.
    * `response()` – Custom responses.

* **HTTP Abstractions**

  * PSR‑7 compliant `Request`, `Response`, `Stream`, and `Uri`.
  * Helper methods for querying request data and generating responses.

* **Dependency Injection**

  * Lightweight container for automatic resolution of classes and dependencies.
  * Supports service binding via providers.

* **View Rendering**

  * Simple PHP-based templating engine.
  * Dot notation for view files (e.g., `view('pages.home')`).

* **Providers**

  * Service providers register bindings and services into the container.
  * `NucleusProvider` sets up core services like Router, Request, Response, and View.

* **Exceptions**

  * Custom exceptions for routing errors (e.g., route not found, missing parameters).

---

## Architecture Overview

```
nucleus/
│
├─ core/
│   ├─ Application.php       # Bootstraps the framework
│   ├─ Nucleus.php           # Handles requests and middleware
│   └─ Bootstrap/
│       ├─ Provider.php      # Abstract provider class
│       └─ NucleusProvider.php
│
├─ container/
│   └─ Container.php         # Dependency Injection container
│
├─ controller/
│   └─ BaseController.php
│
├─ exceptions/
│   └─ Custom framework exceptions
│
├─ http/
│   ├─ Request.php
│   ├─ Response.php
│   ├─ Stream.php
│   └─ Uri.php
│
├─ routing/
│   ├─ Route.php
│   ├─ Router.php
│   └─ RouteResolver.php
│
└─ view/
    └─ View.php
```

---

## Usage

To build an application with Nucleus:

1. Create a new project that **depends on Nucleus** via Composer.
2. Set up your **Application class**, extending Nucleus core if needed.
3. Register your providers to bind your application services.
4. Define routes, controllers, middleware, and views in your application.
5. Use `$app->run()` to handle incoming requests.

---

## Quick Start Example

Here’s a minimal example showing how to create a small application on top of Nucleus:

**Project Structure**

```
my-app/
├─ app/
│   ├─ Controllers/
│   │   └─ HomeController.php
│   └─ Providers/
│       └─ AppProvider.php
├─ config/
│   └─ app.php
├─ routes/
│   └─ web.php
└─ public/
    └─ index.php
```

**app/Controllers/HomeController.php**

```php
<?php
namespace App\Controllers;

use Nucleus\Controller\BaseController;

class HomeController extends BaseController
{
    public function index()
    {
        return $this->view('home', ['message' => 'Hello from Nucleus!']);
    }
}
```

**routes/web.php**

```php
<?php
use App\Controllers\HomeController;

$router->get('/', [HomeController::class, 'index'])->name('home');
```

**public/index.php**

```php
<?php
require __DIR__ . '/../vendor/autoload.php';

use Nucleus\Core\Application;

$app = new Application(dirname(__DIR__));
$app->run();
```

**config/app.php**

```php
<?php
use App\Providers\AppProvider;

return [
    'routes_path' => __DIR__ . '/../routes/web.php',
    'providers' => [
        AppProvider::class,
    ],
    'env' => 'dev',
    'timezone' => 'UTC',
];
```

With this setup:

1. Accessing `/` will trigger the `HomeController@index` method.
2. You can add more routes, controllers, middleware, and views.
3. Named routes allow generating URLs dynamically.


## Feature Development Workflow

We follow a **Reflection → Tests → Implementation** workflow:

1. **Reflection**

   * Understand the feature and its edge cases.
   * Think carefully about parameters, dependencies, and expected responses.
2. **Tests**

   * Write PHPUnit tests for routing, middleware, controllers, and core components.
   * Ensure both success and failure cases are covered.
3. **Implementation**

   * Implement the feature to pass all tests.
   * Refactor cautiously to maintain framework stability.

---

## Contributing

* Follow **PSR‑12 coding standards**.
* All new classes and methods must include **English docblocks**.
* Write **unit tests** for every new feature or fix.
* Document architectural decisions in code and PRs.