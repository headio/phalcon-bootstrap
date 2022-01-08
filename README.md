# Phalcon application bootstrap

A flexible application bootstrap for Phalcon4-based projects  

[![Build Status](https://travis-ci.com/headio/phalcon-bootstrap.svg?branch=master)](https://travis-ci.com/headio/phalcon-bootstrap) [![Coverage Status](https://coveralls.io/repos/github/headio/phalcon-bootstrap/badge.svg?branch=master)](https://coveralls.io/github/headio/phalcon-bootstrap?branch=master)

## Description

This library provides flexible application bootstrapping, encapsulating module registration (or handler registration for micro applications), event management and middleware logic assignment.
A simple factory instantiates the DI container, encapsulating the registration of service dependency definitions defined in the configuration setttings, for mvc, micro and cli applications.

## Dependencies

* PHP >=8.0
* Phalcon >=5.0.0

See composer.json for more details  

## Installation

### Composer

Open a terminal window and run:

```bash
composer require headio/phalcon-bootstrap
```

## Usage

### Micro applications (Api, prototype or micro service)

First create a config definition file inside your Phalcon project. This file should include the configuration settings, service & middleware definitions and a path to your handlers.

To get started, let's assume the following project structure:

```bash
├── public
│   ├── index.php
├── src
│   ├── Config
│   │    │── Config.php
│   │    │── Handlers.php
│   │── Controller
│   │── Domain
│   │── Middleware
│   │── Service
│   │── Var
│   │    │── Log
├── tests
├── vendor
├── Boot.php
├── codeception.yml
├── composer.json
├── .gitignore
├── README.md
└── .travis.yml
```

and your PSR-4 autoload declaration is:

```json
{
    "autoload": {
        "psr-4": {
            "Foo\\": "src/"
        }
    }
}
```

Create a config file **Config.php** inside the **Config** directory and copy-&-paste the following definition:

```php
<?php

namespace Foo\Config;

use Foo\Middleware\NotFoundMiddleware;

return [
    'applicationPath' => dirname(__DIR__) . DIRECTORY_SEPARATOR,
    'debug' => true,
    'locale' => 'en_GB',
    'logPath' => dirname(__DIR__) .
        DIRECTORY_SEPARATOR . 'Var' .
        DIRECTORY_SEPARATOR . 'Log' .
        DIRECTORY_SEPARATOR,
    'handlerPath' => __DIR__ . DIRECTORY_SEPARATOR . 'Handlers.php',
    'middleware' => [
        NotFoundMiddleware::class => 'before'
    ],
    'services' => [
        'Foo\Service\EventManager',
        'Foo\Service\Logger',
    ],
    'timezone' => 'Europe\London'
];
```

The **handlerPath** declaration must include your handlers; the best strategy is to utilize Phalcon collections. The contents of this file might look something like this:

```php
<?php
namespace Foo\Config;

use Foo\Controller\Index;
use Phalcon\Mvc\Micro\Collection;

$handler = new Collection();
$handler->setHandler(Index::class, true);
$handler->setPrefix('/');
$handler->get('/', 'indexAction', 'apiIndex');
$app->mount($handler);
```

Now, create an index file inside the **public** directory and copy-&-paste the following:

```php
<?php
declare(strict_types=1);

chdir(dirname(__DIR__));
require 'Boot.php';
```

Finally, paste the following bootstrap code inside the **Boot.php** file:

```php
<?php
declare(strict_types=1);

use Headio\Phalcon\Bootstrap\Bootstrap;
use Headio\Phalcon\Bootstrap\Di\Factory as DiFactory;
use Phalcon\Config\Config;

require_once __DIR__ . '/vendor/autoload.php';

// Micro example
(function () {
    $config = new Config(
        require __DIR__ . '/src/Config/Config.php'
    );

    $di = (new DiFactory($config))->createDefaultMvc();

    // Environment
    if (extension_loaded('mbstring')) {
        mb_internal_encoding('UTF-8');
        mb_substitute_character('none');
    }

    set_error_handler(
        function ($severity, $message, $file, $line) {
            if (!(error_reporting() & $severity)) {
                // unmasked error context
                return;
            }
            throw new \ErrorException($message, 0, $severity, $file, $line);
        }
    );

    set_exception_handler(
        function (Throwable $e) use ($di) {
            $di->get('logger')->error($e->getMessage(), ['exception' => $e]);

            // Verbose exception handling in development
            if ($di->get('config')->debug) {
            }

            exit(1);
        }
    );

    // Run the application
    return Bootstrap::handle($di)->run($_SERVER['REQUEST_URI'], Bootstrap::Micro);
})();
```

### Mvc applications

Create a config definition file inside your Phalcon project. This file should include your configuration settings and service & middleware definitions.

Let's assume the following mvc project structure:

```bash
├── public
│   ├── index.php
├── src
│   ├── Config
│   │    │── Config.php
│   │── Controller
│   │── Domain
│   │── Middleware
│   │── Module
│   │    │── Admin
│   │    │    │── Controller
│   │    │    │── Form
│   │    │    │── Task
│   │    │    │── View
│   │    │    │── Module.php
│   │── Service
│   │── Var
│   │    │── Log
├── tests
├── vendor
├── Boot.php
├── codeception.yml
├── composer.json
├── .gitignore
├── README.md
└── .travis.yml
```

and your PSR-4 autoload declaration is:

```json
{
    "autoload": {
        "psr-4": {
            "Foo\\": "src/"
        }
    }
}
```

Create a config file **Config.php** inside the **Config** directory and copy-&-paste the following definition:

```php
<?php

namespace Foo\Config;

return [
    'annotations' => [
        'adapter' => 'Apcu',
        'options' => [
            'lifetime' => 3600 * 24 * 30,
            'prefix' => 'annotations',
        ],
    ],
    'applicationPath' => dirname(__DIR__) . DIRECTORY_SEPARATOR,
    'baseUri' => '/',
    'dispatcher' => [
        'defaultAction' => 'index',
        'defaultController' => 'Admin',
        'defaultControllerNamespace' => 'Foo\\Module\\Admin\\Controller',
        'defaultModule' => 'admin'
    ],
    'locale' => 'en_GB',
    'logPath' => dirname(__DIR__) .
        DIRECTORY_SEPARATOR . 'Var' .
        DIRECTORY_SEPARATOR . 'Log' .
        DIRECTORY_SEPARATOR,
    'modules' => [
        'admin' => [
            'className' => 'Foo\\Module\\Admin\\Module',
            'path' => dirname(__DIR__) . '/Module/Admin/Module.php'
        ],
    ],
    'middleware' => [
        'Foo\\Middleware\\Bar'
    ],
    'routes' => [
        'admin' => [
            'Foo\Module\Admin\Controller\Admin' => '/admin',
        ],
    ],
    'services' => [
        'Foo\Service\EventManager',
        'Foo\Service\Logger',
        'Foo\Service\Annotation',
        'Foo\Service\Router',
        'Foo\Service\View'
    ],
    'timezone' => 'Europe\London',
    'useI18n' => true,
    'view' => [
        'defaultPath' => dirname(__DIR__) . '/Module/Admin/View/',
        'compiledPath' => dirname(__DIR__) . '/Cache/Volt/',
        'compiledSeparator' => '_',
    ]
];
```

Now, create an index file inside the **public** directory and paste the following:

```php
<?php
declare(strict_types=1);

chdir(dirname(__DIR__));
require 'Boot.php';
```

Finally, paste the following bootstrap code inside the **Boot.php** file:

```php
<?php
declare(strict_types=1);

use Headio\Phalcon\Bootstrap\Bootstrap;
use Headio\Phalcon\Bootstrap\Di\Factory as DiFactory;
use Phalcon\Config\Config;

require_once __DIR__ . '/vendor/autoload.php';

// Mvc example
(function () {
    $config = new Config(
        require __DIR__ . '/src/Config/Config.php'
    );

    $di = (new DiFactory($config))->createDefaultMvc();

    // Environment
    if (extension_loaded('mbstring')) {
        mb_internal_encoding('UTF-8');
        mb_substitute_character('none');
    }

    set_error_handler(
        function ($severity, $message, $file, $line) {
            if (!(error_reporting() & $severity)) {
                // unmasked error context
                return;
            }
            throw new \ErrorException($message, 0, $severity, $file, $line);
        }
    );

    set_exception_handler(
        function (Throwable $e) use ($di) {
            $di->get('logger')->error($e->getMessage(), ['exception' => $e]);

            // Verbose exception handling in development
            if ($di->get('config')->debug) {
            }

            exit(1);
        }
    );

    // Run the application
    return Bootstrap::handle($di)->run($_SERVER['REQUEST_URI']);
})();
```

### Console application

Create a config definition file inside your Phalcon project. This file should include your configuration settings and service & middleware definitions.

Let's assume the following project structure:

```bash
├── src
│   ├── Config
│   │    │── Config.php
│   │── Domain
│   │── Middleware
│   │── Service
│   │── Task
│   │── Var
│   │    │── Log
├── tests
├── vendor
├── Cli.php
├── codeception.yml
├── composer.json
├── .gitignore
├── README.md
└── .travis.yml
```

and your PSR-4 autoload declaration is:

```json
{
    "autoload": {
        "psr-4": {
            "Foo\\": "src/"
        }
    }
}
```

Create a config file **Config.php** inside the **Config** directory and copy-&-paste the following definition:

```php
<?php

namespace Foo\Config;

return [
    'applicationPath' => dirname(__DIR__) . DIRECTORY_SEPARATOR,
    'locale' => 'en_GB',
    'logPath' => dirname(__DIR__) .
        DIRECTORY_SEPARATOR . 'Var' .
        DIRECTORY_SEPARATOR . 'Log' .
        DIRECTORY_SEPARATOR,
    'dispatcher' => [
        'defaultTaskNamespace' => 'Foo\\Task',
    ],
    'middleware' => [
    ],
    'services' => [
        'Foo\Service\EventManager',
        'Foo\Service\Logger',
        'Foo\Service\ConsoleOutput',
    ],
    'timezone' => 'Europe\London'
];
```

Finally, paste the following bootstrap code inside the **Cli.php** file:

```php
<?php
declare(strict_types=1);

use Headio\Phalcon\Bootstrap\Cli\Bootstrap;
use Headio\Phalcon\Bootstrap\Di\Factory as DiFactory;
use Phalcon\Config\Config;

require_once __DIR__ . '/vendor/autoload.php';

// Cli example
(function () {
    $config = new Config(
        require __DIR__ . '/src/Config/Config.php'
    );

    $di = (new DiFactory($config))->createDefaultCli();

    // Environment
    set_error_handler(
        function ($severity, $message, $file, $line) {
            if (!(error_reporting() & $severity)) {
                // unmasked error context
                return;
            }
            throw new \ErrorException($message, 0, $severity, $file, $line);
        }
    );

    set_exception_handler(
        function (Throwable $e) use ($di) {
            $di->get('logger')->error($e->getMessage(), ['exception' => $e]);
            $output = $di->get('consoleOutput');
            $output->writeln('<error>' . $e->getMessage() . '</error>');

            // Verbose exception handling in development
            if ($di->get('config')->debug) {
                $output->writeln(sprintf(
                    '<error>Exception thrown in: %s at line %d.</error>',
                    $e->getFile(),
                    $e->getLine())
                );
            }

            exit(1);
        }
    );

    // Run the application
    return Bootstrap::handle($di)->run($_SERVER);
})();
```

## DI container factory

From the examples above you will have noticed that we instantiated Phalcon's factory default mvc or cli container services.

```php
$config = new Config(
    require __DIR__ . '/src/Config/Config.php'
);

// Micro/Mvc
$di = (new DiFactory($config))->createDefaultMvc();

// Cli
$di = (new DiFactory($config))->createDefaultCli();
```

Naturally, you can override the factory default services by simply defining a service definition in your config file, like so:

```php
<?php
namespace Foo\Config

return [
    'services' => [
        'Foo\Service\Router'
    ]
]

```

Then create the respective service provider and modify its behaviour:

```php
<?php
/**
 * This source file is subject to the MIT License.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this package.
 */
declare(strict_types=1);

namespace Foo\Service;

use Foo\Exception\OutOfRangeException;
use Phalcon\Config\Config;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Di\DiInterface;
use Phalcon\Cli\Router as CliService;
use Phalcon\Mvc\Router as MvcRouter;
use Phalcon\Mvc\Router\Annotations as MvcService;

class Router implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(DiInterface $di) : void
    {
        $di->setShared(
            'router',
            function () {
                $config = $this->get('config');

                if ($config->cli) {
                    $service = new CliService();
                    $service->setDefaultModule($config->dispatcher->defaultTaskModule);
                    return $service;
                }

                if (!isset($config->modules)) {
                    throw new OutOfRangeException('Undefined modules');
                }

                if (!isset($config->routes)) {
                    throw new OutOfRangeException('Undefined routes');
                }

                $service = new MvcService(false);
                $service->removeExtraSlashes(true);
                $service->setDefaultNamespace($config->dispatcher->defaultControllerNamespace);
                $service->setDefaultModule($config->dispatcher->defaultModule);
                $service->setDefaultController($config->dispatcher->defaultController);
                $service->setDefaultAction($config->dispatcher->defaultAction);

                foreach ($config->modules->toArray() ?? [] as $module => $settings) {
                    if (!$config->routes->get($module, false)) {
                        continue;
                    }
                    foreach ($config->routes->{$module}->toArray() ?? [] as $key => $val) {
                        $service->addModuleResource($module, $key, $val);
                    }
                }

                return $service;
            }
        );
    }
}
```

For complete control over the registration of service dependencies, or more generally, the services available in the container, you have two options: firstly, you can use Phalcon's base DI container, which is an empty container; or you can create your own DI container by implementing Phalcon's **Phalcon\Di\DiInterface**. See the following for an example:

```php
use Phalcon\Di;
use Foo\Bar\MyDi;

$config = new Config(
    require __DIR__ . '/src/Config/Config.php'
);

// Empty DI container
$di = (new DiFactory($config))->create(new Di);

// Custom DI container
$di = (new DiFactory($config))->create(new MyDi);
```

The DI factory **create method** expects an instance of **Phalcon\Di\DiInterface**.

## Application factory

The bootstrap factory will automatically instantiate a Phalcon application and return the response. If you want to bootstrap the application yourself, you can use the application factory directly.

```php
<?php
/**
 * This source file is subject to the MIT License.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this package.
 */
declare(strict_types=1);

use Headio\Phalcon\Bootstrap\Application\Factory as AppFactory;
use Headio\Phalcon\Bootstrap\Di\Factory as DiFactory;
use Phalcon\Config\Config;

chdir(dirname(__DIR__));

require_once 'vendor/autoload.php';

$config = new Config(
    require 'src/Config/Config.php'
);

try {
    $di = (new DiFactory($config))->createDefaultMvc();

    /** @var Phalcon\Mvc\Application */
    $app = (new AppFactory($di))->createForMvc();

    // Do some stuff

    /** @var Phalcon\Mvc\ResponseInterface|bool */
    $response = $app->handle($_SERVER['REQUEST_URI']);

    if ($response instanceof \Phalcon\Mvc\ResponseInterface) {
        return $response->send();
    }

    return $response;
} catch(\Throwable $e) {
    echo $e->getMessage();
}
```

## Testing

To see the tests, run:

```bash
php vendor/bin/codecept run -f --coverage --coverage-text
```

## License

Phalcon bootstrap is open-source and licensed under [MIT License](http://opensource.org/licenses/MIT).
