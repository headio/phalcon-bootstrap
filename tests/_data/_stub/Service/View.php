<?php
/**
 * This source file is subject to the MIT License.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this package.
 */
declare(strict_types=1);

namespace Stub\Service;

use Phalcon\Config;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Phalcon\Events\EventInterface;
use Phalcon\Mvc\DispatcherInterface;
use Phalcon\Mvc\View as Service;
use Phalcon\Mvc\View\Engine\Volt;
use Stub\Helper\Inflector;
use Stub\View\VoltExtension;

class View implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(DiInterface $di): void
    {
        $di->setShared(
            'view',
            function () {
                $config = $this->get('config');
                $service = new Service();
                $volt = new Volt($service, $this);
                $volt->setOptions(
                    [
                        'compiledPath' => $config->view->compiledPath,
                        'compiledSeparator' => $config->view->compiledSeparator,
                        'compileAlways' => $config->debug
                    ]
                );
                $compiler = $volt->getCompiler();
                $compiler->addExtension(new VoltExtension());
                $service->registerEngines(
                    [
                        '.volt' => $volt,
                        '.phtml' => 'Phalcon\\Mvc\\View\\Engine\\Php'
                    ]
                );

                if (isset($config->view->defaultPath)) {
                    $service->setViewsDir($config->view->defaultPath);
                    $service->setEventsManager($this->get('eventsManager'));
                    $dispatcher = $this->get('dispatcher');
                    $dispatcher->getEventsManager()->attach('dispatch', new self());
                } else {
                    $service->disable();
                }

                return $service;
            }
        );
    }

    /**
     * Resolve layout and template views.
     *
     * @return Void
     */
    public function beforeExecuteRoute(EventInterface $event, DispatcherInterface $dispatcher)
    {
        if (0 === strcmp($dispatcher->getNamespaceName(), $dispatcher->getDefaultNamespace())) {
            $view = $dispatcher->getDI()->get('view');

            if ($view->isDisabled()) {
                return;
            }

            $handler = array_values(
                array_diff_assoc(
                    explode('\\', $dispatcher->getHandlerClass()),
                    explode('\\', $dispatcher->getDefaultNamespace())
                )
            );
            $handler[] = Inflector::underscore(implode('', $handler));
            $handler = array_reverse($handler);
            array_pop($handler);
            $handler[] = $dispatcher->getActionName();

            if (file_exists($view->getViewsDir() . 'layouts' . DIRECTORY_SEPARATOR . $handler[0] . '.volt')) {
                $view->setLayout($handler[0]);
            } else {
                $view->setLayout('index');
            }

            $view->lang = Inflector::normalizeLocale($dispatcher->getDI()->get('config')->locale);
            $view->pick(implode(DIRECTORY_SEPARATOR, $handler));
        }
    }

    /**
     * Handle view context for ajax requests.
     *
     * @return Void
     */
    public function beforeDispatchLoop(EventInterface $event, DispatcherInterface $dispatcher)
    {
        if (!$dispatcher->getDI()->has('request')) {
            return;
        }

        $request = $dispatcher->getDI()->get('request');

        if ($request->isAjax()) {
            $dispatcher->getDI()->get('view')->setRenderLevel(Service::LEVEL_ACTION_VIEW);
        }
    }
}
