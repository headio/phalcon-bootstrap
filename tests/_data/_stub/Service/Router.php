<?php
/**
 * This source file is subject to the MIT License.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this package.
 */
declare(strict_types=1);

namespace Stub\Service;

use Headio\Phalcon\Bootstrap\Exception\OutOfBoundsException;
use Phalcon\Config;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Di\DiInterface;
use Phalcon\Cli\Router as CliService;
use Phalcon\Mvc\Router as MvcRouter;
use Phalcon\Mvc\Router\Annotations as MvcService;

class Router implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(DiInterface $di): void
    {
        $di->setShared(
            'router',
            function () {
                $config = $this->get('config');

                if ($config->cli) {
                    $service = new CliService();

                    if (isset($config->router->defaultPaths)) {
                        $service->setDefaults(
                            $config->router->defaultPaths->toArray()
                        );
                    }

                    return $service;
                }

                if (!isset($config->modules)) {
                    throw new OutOfBoundsException('Undefined modules');
                }

                if (!isset($config->routes)) {
                    throw new OutOfBoundsException('Undefined routes');
                }

                $service = new MvcService(false);
                $service->setControllerSuffix(''); // Remove default suffix
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
