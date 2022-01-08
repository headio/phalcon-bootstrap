<?php
/**
 * This source file is subject to the MIT License.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this package.
 */
declare(strict_types=1);

namespace Unit\Application;

use Headio\Phalcon\Bootstrap\Application\Factory;
use Headio\Phalcon\Bootstrap\Application\FactoryInterface;
use Headio\Phalcon\Bootstrap\Di\Factory as DiFactory;
use Headio\Phalcon\Bootstrap\Exception\OutOfBoundsException;
use Phalcon\Config\Config;
use Phalcon\Di\DiInterface;
use Phalcon\Mvc\Application;
use Phalcon\Mvc\Micro;
use Mockery;
use Module\UnitTest;
use Stub\Middleware\NotFoundMiddleware;

class FactoryTest extends UnitTest
{
    public function testFactoryCanCreateMvcApplication(): void
    {
        $this->describe(
            'Factory can create mvc application',
            function () {
                $config = new Config($this->_config());
                $di = (new DiFactory($config))->createDefaultMvc();
                $mock = Mockery::mock(
                    Factory::class,
                    FactoryInterface::class,
                    [$di]
                )
                ->makePartial();

                /** @var Application */
                $result = $mock->createForMvc();

                $this->should(
                    'return mvc application instance',
                    function () use ($result) {
                        expect($result)->isInstanceOf(Application::class);
                    }
                );

                $this->should(
                    'return default dependency injection container instance',
                    function () use ($result) {
                        expect($result->getDI())->isInstanceOf(DiInterface::class);
                    }
                );
            }
        );
    }

    public function testFactoryCanNotCreateMicroApplication(): void
    {
        $this->expectException(OutOfBoundsException::class);

        $this->expectExceptionMessage('Could not load application handlers');

        $this->specify(
            'Factory can not create micro application with missing parameters',
            function () {
                $config = new Config($this->_config());
                $di = (new DiFactory($config))->createDefaultMvc();
                $mock = Mockery::mock(
                    Factory::class,
                    FactoryInterface::class,
                    [$di]
                )
                ->makePartial();

                /** @var Micro */
                $result = $mock->createForMicro();
            }
        );
    }

    public function testFactoryCanCreateMicroApplication(): void
    {
        $this->describe(
            'Factory can create micro application',
            function () {
                $config = new Config($this->_config());
                $config->merge(
                    new Config(
                        [
                            'handlerPath' => TEST_STUB_DIR . 'Config' .
                                DIRECTORY_SEPARATOR .
                                'Handlers.php'
                        ]
                    )
                );
                $di = (new DiFactory($config))->createDefaultMvc();
                $mock = Mockery::mock(
                    Factory::class,
                    FactoryInterface::class,
                    [$di]
                )
                ->makePartial();

                /** @var Micro */
                $result = $mock->createForMicro();

                $this->should(
                    'return micro application instance',
                    function () use ($result) {
                        expect($result)->isInstanceOf(Micro::class);
                    }
                );

                $this->should(
                    'return configured dependency injection container instance',
                    function () use ($result) {
                        expect($result->getDI())->isInstanceOf(DiInterface::class);
                    }
                );
            }
        );
    }

    public function testFactoryCanRegisterModulesForMvcApplication(): void
    {
        $this->specify(
            'Factory can assign modules to mvc application',
            function () {
                $config = new Config($this->_config());
                $config->merge(
                    new Config(
                        [
                            'modules' => [
                                'A' => [
                                    'className' => 'Stub\\Module\\A\\Module',
                                    'path' => TEST_STUB_DIR . 'Module/A/Module.php'
                                ],
                                'B' => [
                                    'className' => 'Stub\\Module\\B\\Module',
                                    'path' => TEST_STUB_DIR . 'Module/B/Module.php'
                                ],
                                'C' => [
                                    'className' => 'Stub\\Module\\C\\Module',
                                    'path' => TEST_STUB_DIR . 'Module/C/Module.php'
                                ]
                            ]
                        ]
                    )
                );
                $di = (new DiFactory($config))->createDefaultMvc();
                $mock = Mockery::mock(
                    Factory::class,
                    FactoryInterface::class,
                    [$di]
                )
                ->makePartial();
                $app = $mock->createForMvc();

                /** @var array */
                $result = $app->getModules();

                expect($result)->equals($config->modules->toArray());
            }
        );
    }

    public function testFactoryCanRegisterEventListenersForMvcApplication(): void
    {
        $this->specify(
            'Event Manager has exact number of registered listeners',
            function () {
                $config = new Config($this->_config());
                $config->merge(
                    new Config(
                        [
                            'middleware' => [
                                'Stub\\Middleware\\Foo'
                            ]
                        ]
                    )
                );
                $di = (new DiFactory($config))->createDefaultMvc();
                $mock = Mockery::mock(
                    Factory::class,
                    FactoryInterface::class,
                    [$di]
                )
                ->makePartial();

                /** @var Application */
                $app = $mock->createForMvc();

                /** @var array */
                $result = $app->getEventsManager()->getListeners('application');

                expect(count($result))
                    ->equals($app->getDI()->getConfig()['middleware']->count());
            }
        );
    }

    public function testFactoryCanRegisterEventListenersForMicroApplication(): void
    {
        $this->specify(
            'Event Manager has exact number of registered listeners',
            function () {
                $config = new Config($this->_config());
                $config->merge(
                    new Config(
                        [
                            'handlerPath' => TEST_STUB_DIR . 'Config' .
                                DIRECTORY_SEPARATOR .
                                'Handlers.php',
                            'middleware' => [
                                NotFoundMiddleware::class => 'before'
                            ],
                            'modules' => []
                        ]
                    )
                );
                $di = (new DiFactory($config))->createDefaultMvc();
                $mock = Mockery::mock(
                    Factory::class,
                    FactoryInterface::class,
                    [$di]
                )
                ->makePartial();

                /** @var Application */
                $app = $mock->createForMicro();

                /** @var array */
                $result = $app->getEventsManager()->getListeners('micro');

                expect(count($result))
                    ->equals($app->getDI()->getConfig()['middleware']->count());
            }
        );
    }

    public function testFactoryCanAttachMiddlewareToMvcApplication(): void
    {
        $this->specify(
            'Event Manager receives assigned middleware',
            function () {
                $config = new Config($this->_config());
                $config->merge(
                    new Config(
                        [
                            'middleware' => [
                                'Stub\\Middleware\\Foo'
                            ]
                        ]
                    )
                );
                $di = (new DiFactory($config))->createDefaultMvc();
                $mock = Mockery::mock(
                    Factory::class,
                    FactoryInterface::class,
                    [$di]
                )
                ->makePartial();

                /** @var Application */
                $app = $mock->createForMvc();

                /** @var array */
                $result = $app->getEventsManager()->getListeners('application');

                expect($result[0])->isInstanceOf('Stub\\Middleware\\Foo');
            }
        );
    }

    public function testFactoryCanAttachMiddlewareToMicroApplication(): void
    {
        $this->specify(
            'Event Manager receives assigned middleware',
            function () {
                $config = new Config($this->_config());
                $config->merge(
                    new Config(
                        [
                            'handlerPath' => TEST_STUB_DIR . 'Config' .
                                DIRECTORY_SEPARATOR .
                                'Handlers.php',
                            'middleware' => [
                                NotFoundMiddleware::class => 'before'
                            ]
                        ]
                    )
                );
                $di = (new DiFactory($config))->createDefaultMvc();
                $mock = Mockery::mock(
                    Factory::class,
                    FactoryInterface::class,
                    [$di]
                )
                ->makePartial();

                /** @var Micro */
                $app = $mock->createForMicro();

                /** @var array */
                $result = $app->getEventsManager()->getListeners('micro');

                expect($result[0])->isInstanceOf('Stub\\Middleware\\NotFoundMiddleware');
            }
        );
    }

    /**
     * Return test config
     */
    protected function _config(): array
    {
        return [
            'middleware' => [],
            'services' => [
                'Stub\Provider\EventManager',
            ]
        ];
    }
}
