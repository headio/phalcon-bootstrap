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

use Phalcon\Config;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Di\DiInterface;
use Phalcon\Events\EventInterface;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Loader as Service;

class Loader implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(DiInterface $di) : void
    {
        $di->set(
            'loader',
            function () {
                $config = $this->get('config');
                $loader = new Service();

                if (isset($config->loader->registerNamespaces)) {
                    $loader->registerNamespaces($config->loader->registerNamespaces->toArray());
                }

                if (isset($config->loader->registerDirs)) {
                    $loader->registerDirs($config->loader->registerDirs->toArray());
                }

                $eventsManager = $this->get('eventsManager');
                $eventsManager->attach(
                    'loader:beforeCheckPath',
                    function (EventInterface $event, Service $loader) {
                        echo $loader->getCheckedPath();
                    }
                );

                $loader->setEventsManager($eventsManager);
                $loader->register();

                return $loader;
            }
        );
    }
}
