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
use Headio\Phalcon\Bootstrap\Cli\ConsoleInterface;
use Headio\Phalcon\Bootstrap\Di\Factory as DiFactory;
use Phalcon\Config;
use Phalcon\Di\DiInterface;
use Mockery;
use Module\UnitTest;

class ConsoleFactoryTest extends UnitTest
{
    private $mock;

    protected function _before()
    {
        /** @var Config */
        $config = new Config($this->_config());

        /** @var DiInterface */
        $di = (new DiFactory($config))->createDefaultCli();

        $this->mock = Mockery::mock(
            Factory::class,
            FactoryInterface::class,
            [$di]
        )
        ->makePartial();
    }

    protected function _after()
    {
    }

    public function testFactoryCanCreateApplication()
    {
        $this->describe(
            'Factory can create console application',
            function () {
                /** @var Phalcon\Cli\Console */
                $result = $this->mock->createForCli();

                $this->should(
                    'return console application instance',
                    function () use ($result) {
                        expect($result)->isInstanceOf('Phalcon\\Cli\\Console');
                    }
                );

                $this->should(
                    'return cli dependency injection container instance',
                    function () use ($result) {
                        expect($result->getDI())->isInstanceOf(DiInterface::class);
                    }
                );
            }
        );
    }

    public function testFactoryCanRegisterModules(): void
    {
        $this->specify(
            'Factory can assign modules to console application',
            function () {
                /** @var Console */
                $console = $this->mock->createForCli();

                /** @var Config */
                $config = new Config($this->_config());

                /** @var array */
                $result = $console->getModules();

                expect($result)->equals($config->modules->toArray());
            }
        );
    }

    public function testFactoryCanRegisterEventListeners(): void
    {
        $this->specify(
            'Event Manager has correct number of registered listeners',
            function () {
                /** @var Console */
                $console = $this->mock->createForCli();

                /** @var array */
                $result = $console->getEventsManager()->getListeners('console');

                expect(count($result))
                    ->equals($console->getDI()->getConfig()['middleware']->count());
            }
        );
    }

    public function testFactoryCanAttachMiddleware(): void
    {
        $this->specify(
            'Event Manager receives assigned middleware',
            function () {
                /** @var Console */
                $console = $this->mock->createForCli();

                /** @var array */
                $result = $console->getEventsManager()->getListeners('console');

                expect($result[0])->isInstanceOf('Stub\\Middleware\\Foo');
            }
        );
    }

    public function testFactoryCanSetDiHasConsoleDependency(): void
    {
        $this->specify(
            'Factory can create console service dependency',
            function () {
                /** @var Console */
                $console = $this->mock->createForCli();

                /** @var array */
                $result = $console->getDI()->get('console');

                expect($result)->isInstanceOf('Phalcon\\Cli\\Console');
            }
        );
    }

    /**
     * Return test config
     */
    protected function _config(): array
    {
        return [
            'dispatcher' => [
                'defaultTaskNamespace' => 'Stub\\Module\\A\\Task',
            ],
            'loader' => [
                'registerNamespaces' => [
                    'Stub\\Module\\A\\Task' => TEST_STUB_DIR . 'Module/A/Task',
                    'Stub\\Module\\B\\Task' => TEST_STUB_DIR . 'Module/B/Task',
                ]
            ],
            'locale' => 'en_GB',
            'middleware' => [
                'Stub\\Middleware\\Foo'
            ],
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
            ],
            'services' => [
                'Stub\Service\EventManager', // load first
                'Stub\Service\Logger',
                'Stub\Service\Dispatcher',
                'Stub\Service\Router',
                'Stub\Service\ConsoleOutput',
            ],
            'timezone' => 'Europe/London'
        ];
    }
}
