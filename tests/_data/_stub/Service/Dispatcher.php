<?php
/*
 * This source file is subject to the MIT License.
 *
 * (c) Dominic Beck <dominic@headcrumbs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this package.
 */
declare(strict_types=1);

namespace Stub\Service;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Phalcon\Events\EventInterface;
use Phalcon\Cli\Dispatcher as CliService;
use Phalcon\Mvc\Dispatcher as MvcService;

class Dispatcher implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(DiInterface $di) : void
    {
        $di->setShared(
            'dispatcher',
            function () {
                $config = $this->get('config');
                $eventsManager = $this->get('eventsManager');

                if ($config->cli) {
                    $service = new CliService();
                    $service->setTaskSuffix(null);

                    if (!empty($namespace = $config->dispatcher->path('defaultTaskNamespace', null))) {
                        $service->setDefaultNamespace($namespace);
                    }

                    $service->setEventsManager($eventsManager);
                    return $service;
                }

                $service = new MvcService();
                $service->setControllerSuffix(null);
                $service->setDefaultNamespace($config->dispatcher->defaultControllerNamespace);
                $service->setEventsManager($eventsManager);

                return $service;
            }
        );
    }
}
