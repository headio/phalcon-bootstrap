<?php
/**
 * This source file is subject to the MIT License.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this package.
 */
declare(strict_types=1);

namespace Unit;

use Headio\Phalcon\Bootstrap;
use Headio\Phalcon\BootstrapInterface;
use Headio\Phalcon\Bootstrap\Di\Factory as DiFactory;
use Phalcon\Config\Config;
use Phalcon\Di\Di;
use Phalcon\Di\DiInterface;
use Phalcon\Http\Response;
use Phalcon\Http\ResponseInterface;
use Mockery;
use Module\UnitTest;

class BootstrapTest extends UnitTest
{
    protected function _after(): void
    {
        Di::reset();
    }

    public function testCanCallFactoryMethod(): void
    {
        $this->specify(
            'Factory method creates expected bootstrap instance',
            function () {
                $config = new Config($this->_config());

                /** @var \Phalcon\Di\DiInterface */
                $di = (new DiFactory($config))->createDefaultCli();

                $mock = Mockery::mock(
                    BootstrapInterface::class,
                    Bootstrap::class
                )
                ->makePartial();

                $mock->allows()
                    ->handle()
                    ->with(DiInterface::class)
                    ->andReturnSelf()
                ;

                /** @var BootstrapInterface */
                $result = $mock::handle($di);

                expect($result)->isInstanceOf(BootstrapInterface::class);
            }
        );
    }

    public function testCanCallHandleRequest(): void
    {
        $this->specify(
            'handle method returns expected response',
            function () {
                $config = new Config($this->_config());

                /** @var \Phalcon\Di\DiInterface */
                $di = (new DiFactory($config))->createDefaultCli();

                $mock = Mockery::mock(
                    BootstrapInterface::class,
                    'alias:MvcBootstrap'
                )
                ->makePartial();

                $mock->allows()
                    ->handle()
                    ->with(DiInterface::class)
                    ->andReturnSelf()
                ;
                $mock->allows()
                    ->run()
                    ->with(Mockery::type('array'))
                    ->andReturn(new Response('Hello world'))
                ;

                /** @var BootstrapInterface */
                $result = $mock::handle($di)->run($_SERVER);

                expect($result)->isInstanceOf(ResponseInterface::class);
            }
        );
    }

    private function _config(): array
    {
        return [
            'annotations' => [
                'adapter' => 'Files',
                'options' => [
                    'annotationsDir' => TEST_OUTPUT_DIR . 'Cache/Annotation/',
                ],
            ],
            'cli' => false,
            'debug' => true,
            'dispatcher' => [
                'defaultModule' => 'Frontend',
                'defaultAction' => 'index',
                'defaultController' => 'Index',
                'defaultControllerNamespace' => 'Stub\\Module\\Frontend\\Controller'
            ],
            'locale' => 'en_GB',
            'logPath' => dirname(__DIR__) .
                DIRECTORY_SEPARATOR . '_data' .
                DIRECTORY_SEPARATOR . '_output' .
                DIRECTORY_SEPARATOR . 'Var' .
                DIRECTORY_SEPARATOR . 'Log' .
                DIRECTORY_SEPARATOR . 'Web' .
                DIRECTORY_SEPARATOR,
            'modules' => [
                'Admin' => [
                    'className' => 'Stub\\Module\\Admin\\Module',
                    'path' => TEST_STUB_DIR . 'Module/Admin/Module.php',
                    'metadata' => [
                        'controllerNamespace' => 'Stub\\Module\\Admin\\Controller'
                    ]
                ],
                'Frontend' => [
                    'className' => 'Stub\\Module\\Frontend\\Module',
                    'path' => TEST_STUB_DIR . 'Module/Frontend/Module.php',
                    'metadata' => [
                        'controllerNamespace' => 'Stub\\Module\\Frontend\\Controller'
                    ]
                ]
            ],
            'routes' => [
                'Frontend' => [
                    'Stub\Module\Frontend\Controller\Index' => '/index',
                ],
                'Admin' => [
                    'Stub\Module\Admin\Controller\Admin' => '/admin',
                ],
            ],
            'services' => [
                'Stub\Provider\EventManager',
                'Stub\Provider\Annotation',
                'Stub\Provider\Dispatcher',
                'Stub\Provider\Router',
                'Stub\Provider\View'
            ],
            'timezone' => 'Europe/Berlin',
            'view' => [
                'defaultPath' => TEST_OUTPUT_DIR . 'Module/Frontend/View/',
                'compiledPath' => TEST_OUTPUT_DIR . 'Cache/Volt/',
                'compiledSeparator' => '_',
            ]
        ];
    }
}
