<?php
/**
 * This source file is subject to the MIT License.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this package.
 */
declare(strict_types=1);

namespace Unit\Cli\Console;

use Headio\Phalcon\Bootstrap\Cli\Console\ConsoleTester;
use Phalcon\Di;
use Phalcon\Cli\Dispatcher\Exception as DispatcherException;
use Symfony\Component\Console\Output\StreamOutput;
use Module\UnitTest;
use Stub\Task\Main as MainTask;

class ConsoleTesterTest extends UnitTest
{
    private $ct;

    protected function _before()
    {
        /** @var Phalcon\Cli\Console */
        $console = $this->tester->bootConsole(
            $this->_config()
        );

        /** @var ConsoleTester */
        $this->ct = $this->tester->getConsoleTester($console);
    }

    protected function _after()
    {
        Di::reset();
        $this->ct = null;
    }

    public function testCanCallGetApplication()
    {
        $this->specify(
            'Console tester should return expected application instance',
            function () {
                expect($this->ct->getApplication())->isInstanceOf(\Phalcon\Cli\Console::class);
            }
        );
    }

    public function testCanCallConsoleOutput()
    {
        $this->specify(
            'Console tester should return expected console output stream instance',
            function () {
                expect(
                    $this->ct->getApplication()
                        ->getDI()
                        ->get('consoleOutput')
                )
                ->isInstanceOf(StreamOutput::class);
            }
        );
    }

    public function testCanExecuteCommandOnDefaultHandler()
    {
        $this->describe(
            'Execute console command on default handler',
            function () {
                /** @var ConsoleTester */
                $ct = $this->ct;
                $ct->execute(
                    [
                        'boot.php',
                        'main'
                    ]
                );

                $this->should(
                    'return instance of default handler',
                    function () use ($ct) {
                        expect($ct->getTask())->isInstanceOf(MainTask::class);
                    }
                );

                $this->should(
                    'return expected default action response',
                    function () use ($ct) {
                        expect($ct->getOutput())->equals('Main action' . PHP_EOL);
                    }
                );
            }
        );
    }

    public function testCanExecuteCommandOnUnknownHandler()
    {
        $this->expectException(DispatcherException::class);

        $this->expectExceptionMessage('Stub\Task\Foobar handler class cannot be loaded');

        $this->describe(
            'Execute console command on unknown handler',
            function () {
                /** @var ConsoleTester */
                $ct = $this->ct;
                $ct->execute(
                    [
                        'boot.php',
                        'Foobar'
                    ]
                );
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
                'defaultTaskNamespace' => 'Stub\\Task',
            ],
            'services' => [
                'Stub\Provider\EventManager',
                'Stub\Provider\Logger',
                'Stub\Provider\Dispatcher',
                'Stub\Provider\Router',
                'Stub\Provider\ConsoleOutput'
            ],
            'timezone' => 'Europe/Berlin'
        ];
    }
}
