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

namespace Unit\Cli;

use Headio\Phalcon\Bootstrap\Cli\Bootstrap;
use Headio\Phalcon\Bootstrap\Cli\BootstrapInterface;
use Headio\Phalcon\Bootstrap\Di\Factory as DiFactory;
use Phalcon\Config;
use Phalcon\Di;
use Phalcon\Di\DiInterface;
use Mockery;
use Module\UnitTest;

class BootstrapTest extends UnitTest
{
    protected function _after() : void
    {
        Di::reset();
    }

    public function testCanCallFactoryMethod() : void
    {
        $this->specify(
            'Factory method creates expected bootstrap instance',
            function () {
                $config = new Config($this->_config());

                /** @var Phalcon\Di\DiInterface */
                $di = (new DiFactory($config))->createDefaultCli();

                $mock = Mockery::mock(
                    BootstrapInterface::class,
                    Bootstrap::class
                )
                ->makePartial();

                $mock->allows()->handle()->with(DiInterface::class)->andReturnSelf();

                /** @var BootstrapInterface */
                $result = $mock->handle($di);

                expect($result)->isInstanceOf(BootstrapInterface::class);
            }
        );
    }

    public function testConsoleEventListenerCallsMiddleware() : void
    {
        $this->specify(
            'Event Manager calls attached console middleware',
            function () {
                /** @var Phalcon\Cli\Console */
                $console = $this->tester->bootConsole(
                    $this->_config()
                );

                /** @var Headio\Phalcon\Bootstrap\Cli\Console\ConsoleTester */
                $ct = $this->tester->getConsoleTester($console);
                $ct->execute(
                    [
                        'boot.php',
                        'Foo',
                        'timezone'
                    ]
                );

                expect($ct->getOutput())->equals('America/New_York' . PHP_EOL);
            }
        );
    }

    /**
     * Return test config
     */
    protected function _config() : array
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
