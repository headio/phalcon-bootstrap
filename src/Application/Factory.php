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

namespace Headio\Phalcon\Bootstrap\Application;

use Headio\Phalcon\Bootstrap\Exception\OutOfBoundsException;
use Phalcon\Cli\Console;
use Phalcon\Mvc\Application;
use Phalcon\Mvc\Micro;
use Phalcon\Di\DiInterface;

/**
 * A simple application factory, encapsulating module registration,
 * handler registration (micro), event management and middleware logic
 * assignment for full-stack mvc applications, micro services and
 * console applications.
 */
class Factory implements FactoryInterface
{
    /** @var DiInterface */
    private $di;

    public function __construct(DiInterface $di)
    {
        $this->di = $di;
    }

    /**
     * {@inheritdoc}
     */
    public function createForCli() : Console
    {
        /** @var DiInterface */
        $di = $this->di;
        $app = new Console($di);
        $config = $di->get('config');

        /**
         * Register modules
         */
        if (isset($config->modules)) {
            $app->registerModules($config->modules->toArray());
        }

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
     * {@inheritdoc}
     */
    public function createForMicro() : Micro
    {
        /** @var DiInterface */
        $di = $this->di;
        $app = new Micro($di);
        $config = $di->get('config');

        /**
         * Register handlers
         */
        $handlers = $config->path('handlerPath', null);

        if (is_null($handlers) || !file_exists($handlers)) {
            throw new OutOfBoundsException('Could not load application handlers');
        }

        require $handlers;

        $eventManager = $di->get('eventsManager');

        /**
         * Attach middleware
         */
        if (isset($config->middleware)) {
            foreach ($config->middleware->toArray() ?? [] as $middleware => $event) {
                $instance = new $middleware();
                $eventManager->attach('micro', $instance);
                $app->{$event}($instance);
            }
        }

        $app->setEventsManager($eventManager);

        return $app;
    }

    /**
     * {@inheritdoc}
     */
    public function createForMvc() : Application
    {
        /** @var DiInterface */
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
