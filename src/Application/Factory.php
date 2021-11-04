<?php
/**
 * This source file is subject to the MIT License.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this package.
 */
declare(strict_types=1);

namespace Headio\Phalcon\Bootstrap\Application;

use Headio\Phalcon\Bootstrap\Exception\OutOfBoundsException;
use Phalcon\Cli\Console;
use Phalcon\Mvc\Application;
use Phalcon\Mvc\Micro;
use Phalcon\Di\DiInterface;
use function is_iterable;

/**
 * A simple application factory, encapsulating module registration,
 * handler registration (micro), event management and middleware logic
 * assignment for full-stack mvc applications, micro services and
 * console applications.
 */
class Factory implements FactoryInterface
{
    private DiInterface $di;

    public function __construct(DiInterface $di)
    {
        $this->di = $di;
    }

    /**
     * {@inheritDoc}
     */
    public function createForCli(): Console
    {
        $di = $this->di;
        $app = new Console($di);
        /** @var \Phalcon\Config */
        $config = $di->get('config');

        /**
         * Register modules
         */
        if (isset($config->modules)) {
            $app->registerModules($config->modules->toArray());
        }

        /** @var \Phalcon\Events\ManagerInterface */
        $eventManager = $di->get('eventsManager');

        /**
         * Attach middleware
         */
        if (isset($config->middleware)) {
            foreach ($config->middleware->toArray() ?? [] as $middleware) {
                $instance = new $middleware();
                $eventManager->attach('console', $instance);
            }
        }

        $app->setEventsManager($eventManager);
        $di->setShared('console', $app);

        return $app;
    }

    /**
     * {@inheritDoc}
     */
    public function createForMicro(): Micro
    {
        $di = $this->di;
        $app = new Micro($di);
        $config = $di->get('config');
        $handlers = $config->path('handlerPath', null);

        if (is_null($handlers) || !file_exists($handlers)) {
            throw new OutOfBoundsException('Could not load application handlers');
        }

        if (!is_file($handlers)) {
            throw new OutOfBoundsException('Could not find application handlers');
        }

        require $handlers;

        /** @var \Phalcon\Events\ManagerInterface */
        $eventManager = $di->get('eventsManager');
        $eventManager->enablePriorities(true);

        /**
         * Attach middleware
         */
        if (isset($config->middleware)) {
            foreach ($config->middleware->toArray() ?? [] as $middleware => $event) {
                $instance = new $middleware();
                $priority = $eventManager::DEFAULT_PRIORITY;

                if (is_array($event)) {
                    list($event, $priority) = $event;
                }

                $eventManager->attach('micro', $instance, $priority);
                $app->{$event}($instance);
            }
        }

        $app->setEventsManager($eventManager);

        return $app;
    }

    /**
     * {@inheritDoc}
     */
    public function createForMvc(): Application
    {
        $di = $this->di;
        $app = new Application($di);
        $config = $di->get('config');

        $app->useImplicitView(isset($config->view));

        /**
         * Register modules
         */
        if (isset($config->modules)) {
            $app->registerModules($config->modules->toArray());
        }

        /** @var \Phalcon\Events\ManagerInterface */
        $eventManager = $di->get('eventsManager');

        /**
         * Attach middleware
         */
        if (isset($config->middleware)) {
            foreach ($config->middleware->toArray() ?? [] as $middleware) {
                $instance = new $middleware();
                $eventManager->attach('application', $instance);
            }
        }

        $app->setEventsManager($eventManager);

        return $app;
    }
}
