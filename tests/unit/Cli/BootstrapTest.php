<?php
/**
 * This source file is subject to the MIT License.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this package.
 */
declare(strict_types=1);

namespace Unit\Cli;

use Headio\Phalcon\Bootstrap\Cli\Bootstrap;
use Headio\Phalcon\Bootstrap\Cli\BootstrapInterface;
use Headio\Phalcon\Bootstrap\Di\Factory as DiFactory;
use Phalcon\Config\Config;
use Phalcon\Di\Di;
use Phalcon\Di\DiInterface;
use Phalcon\Cli\Task;
use Phalcon\Cli\TaskInterface;
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

                $mock->allows()->handle()->with(DiInterface::class)->andReturnSelf();

                /** @var BootstrapInterface */
                $result = $mock::handle($di);

                expect($result)->isInstanceOf(BootstrapInterface::class);
            }
        );
    }

    public function testCanCallHandleConsoleRequest(): void
    {
        $this->specify(
            'handle method returns expected response',
            function () {
                $config = new Config($this->_config());

                /** @var \Phalcon\Di\DiInterface */
                $di = (new DiFactory($config))->createDefaultCli();

                $mock = Mockery::mock(
                    BootstrapInterface::class,
                    'alias:Bootstrap'
                )
                ->makePartial();

                $mock->allows()->handle()->with(DiInterface::class)->andReturnSelf();
                $mock->allows()->run()->with(Mockery::type('array'))->andReturn(new Task());

                /** @var BootstrapInterface */
                $result = $mock::handle($di)->run($_SERVER);

                expect($result)->isInstanceOf(TaskInterface::class);
            }
        );
    }
    /**
     * Return test config
     */
    protected function _config(): array
    {
        return [
            'debug' => false,
            'dispatcher' => [
                'defaultTaskNamespace' => 'Stub\\Task',
            ],
            'locale' => 'en_GB',
            'middleware' => [
                'Stub\\Middleware\\Foo'
            ],
            'services' => [
                'Stub\Provider\EventManager', // load first
                'Stub\Provider\Logger',
                'Stub\Provider\Dispatcher',
                'Stub\Provider\Router',
                'Stub\Provider\ConsoleOutput',
            ],
            'timezone' => 'Europe/London'
        ];
    }
}
