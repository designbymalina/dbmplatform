# DBM Platform - a ready-to-use application platform built on DBM Framework

DBM Platform is a ready-to-use web application platform built on top of DBM Framework, designed for rapid development of CMS systems, admin panels, and modular web applications.

It allows you to quickly start a project without building an administration panel, user system, or basic infrastructure from scratch.

## Who is it for?

The platform is a good fit if:

- you want to launch a project quickly
- you need an administration panel
- you are building a CMS, portal, or web application
- you want to develop the system in a modular way

## Why DBM Platform?

The platform allows you to start a project faster than building an application from scratch, while maintaining full control over the architecture and source code.

Unlike traditional heavyweight CMS platforms:

- it doesn't impose a closed structure
- it can operate without extensive infrastructure
- it supports modular development
- it uses the lightweight DBM Framework runtime

## What's included in the platform?

### Basic Features

- User Login and Registration
- Admin Panel
- Module Management
- Page Management System
- Routing and Middleware
- Template System
- File System and File Upload

## Platform Versions

### CMS Lite

A minimal version based on files and templates.

### Base (CMS Lite + Admin)

Extensions include:

- Administration Panel
- Users
- Modules
- Application Management

The platform supports the installation of additional modules.

## Platform Preview

### Administration Panel

![DBM Platform Admin](https://dbm.org.pl/images/page/packages/dbm-cmslite-admin.png)

## Installation

DBM Platform can function as both a ready-made CMS system and as a foundation for your own PHP applications.

**Two installation methods are available:**

- Manual installation - for hosting and quick startup
- Developer installation - for working with Git and Composer

**DBM Platform can run:**

- as a standalone runtime
- or with full Composer support

### Manual Installation

The easiest way to launch DBM Platform.

Recommended for:

- shared hosting
- simple deployments
- CMS Lite
- users without a development environment

#### Installation Steps

1. Download the project archive from GitHub
2. Unzip the files to your server
3. Copy `.env.example` as `.env`
4. Set up the basic configuration:

```env
APP_URL="https://your-domain.pl/"
APP_NAME="DBM Platform"
APP_EMAIL="admin@domain.com"
```

5. Point your domain to the `public/` directory

6. If your server requires it, grant write access to the following directories: `data/`, `storage/`, and `var/`.

7. Open the application in a browser and complete the environment configuration.

---

### Developer Installation

This installation is intended for developers working with Git and Composer.

#### Downloading the project

```bash
git clone https://github.com/designbymalina/dbmplatform
cd dbmplatform
```

#### Environment configuration

```bash
cp .env.example .env
```

#### Local launch

```bash
php -S localhost:8000 -t public
```

The application will be available at: `http://localhost:8000`

#### Composer (optional)

By default, the platform can operate independently without Composer.

Composer is recommended for larger projects and additional packages.

Optional (not required at startup):

```bash
composer install
```

Composer installation will generate the Composer autoloader and install all required dependencies.

After switching to Composer, some libraries can be managed directly by Composer instead of the `libraries/` directory.

### Environment Configuration

#### Document Root

In a production environment, the domain should point to the `/public` directory.

#### Apache / localhost

In a local environment, you may need to configure `.htaccess` and the `RewriteBase` directive.

If you are using a local environment (localhost), copy the `.htaccess` file from the `_Documents/_Server/` directory to the project's root folder. Then, in both files—in the root directory and `public/.htaccess`—adjust the **RewriteBase** directive to match the application's launch path.

On the remote server, ensure that **open_basedir** does not block access to the application's directories.

#### Cache

After configuration, it is recommended to set:

```env
CACHE_ENABLED=true
```

**Important** During module installation, the cache should be disabled: `CACHE_ENABLED=false`.

#### Write permissions

DBM Platform requires write permissions for the following directories: `var/`, `storage/`, and `data/`.

## Architecture

DBM Platform operates as an application layer above DBM Framework.

The framework is responsible for: runtime, routing, middleware, DI, and infrastructure.

The platform provides ready-made application modules and an administration panel.

## Structure

- `bin/` - executable files: console interface (CLI) and worker (entry point: bin/dbm)
- `bootstrap/` - framework core (Routing, DI, API)
- `libraries/` - external libraries (PSR, PHPMailer, Guzzle)
- `modules/` - platform modules (installer, content management system, auth, admin)
- `public/` - public files (domain root)
- `src/` - application logic: controllers, services, models
- `storage/` - stores files generated by the application (cache)
- `templates/` - view templates
- `tests/` - unit tests
- `translations/` - translation files (optional)
- `var/` - cache and logs (created automatically, write permissions required)
- `vendor/` - libraries installed by Composer (generated automatically)
- `.env.example` - sample environment configuration

## Extended Project Structure

- `_Documents/` - documentation, module installation archive
- `data/` - data and files (write permissions required)
- `config/` - configuration files (optional, e.g., php.ini)
- `frontend/` - frontend (optional React.js or Vue.js, Node.js, Webpack)

## Hybrid Autoloading

DBM Platform includes its own hybrid autoloading system.

The system can run:

- completely independent of Composer
- with internal PSR-4 autoloading
- or with full Composer support

This allows applications to run on both simple shared hosting and a full development environment.

This allows DBM Platform to function as:

- a lightweight CMS with no additional dependencies
- a classic Composer application
- or a hybrid project combining both approaches

## Routing

Standard web routing is defined in the file: `bootstrap/web.php`.

Example:

```shell
$router->get('/path', [NameController::class, 'methodName'], 'route_name');
```

REST API routes are defined in: `bootstrap/api.php`.

Example:

```shell
$router->get('/api/path', [NameApiController::class, 'methodName'], 'api_route_name');
```

## Template Engine

DBM Framework uses the lightweight **DbM View Engine** by default.

Features:

- No additional DSL
- Syntax based directly on PHP
- High performance
- Can be extended via callbacks and helpers

Templates are located in the `templates/` directory.

The engine can be replaced with another implementation (e.g., Twig).

## Command Console

A lightweight and fast CLI for CRON and DEV tasks. It provides a simple way to run background or maintenance tasks directly from the command line with a lightweight and self-contained implementation. Console commands are executed via the `bin/dbm` file.

Available commands:

```bash
php bin/dbm list
php bin/dbm command example (for ExampleCommand)
php bin/dbm worker example (for ExampleWorker)
```

## Stack

- PHP 8.1+
- DBM Framework v6
- PSR-4 / PSR-11 / PSR-12
- Modular architecture
- Middleware pipeline
- Hybrid autoloading
- File-based architecture
- File-based CMS + optional database

## Documentation

[Introduction and Architecture](_Documents/_Docs/pl/01-getting-started/01-introduction.md)

*(Documentation currently available in Polish)*

## Support the Project

If you use DBM Platform, please consider leaving information about the project in the application footer.

This helps support the development of the DBM framework and ecosystem.

## License

DBM Framework is distributed under the MIT License.

Select DBM Platform components, modules, and components may be subject to separate license terms.

Details:

- `/LICENSE`
- `/LICENSE_DBM_PLATFORM.txt`

Copyright (c) Design by Malina
