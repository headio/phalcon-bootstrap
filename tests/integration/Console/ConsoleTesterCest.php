<?php
/**
 * This source file is subject to the MIT License.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this package.
 */
declare(strict_types=1);

namespace Integration\Console;

use Headio\Phalcon\Bootstrap\Cli\Console\ConsoleTester;
use Phalcon\Cli\Dispatcher\Exception as DispatcherException;
use Phalcon\Di\Di;
use Stub\Task\Main as MainTask;
use Symfony\Component\Console\Output\StreamOutput;
use IntegrationTester;

class ConsoleTesterCest
{
    private ?ConsoleTester $ct = null;

    public function _before(IntegrationTester $I): void
    {
        Di::reset();

        /** @var ConsoleTester */
        $this->ct = $I->getConsoleTester(
            $I->bootConsole($this->_config())
        );
    }

    public function canCallGetApplication(IntegrationTester $I): void
    {
        $I->wantToTest('the console tester returns the expected application instance');

        expect(
            $this->ct->getApplication()
        )
        ->isInstanceOf(\Phalcon\Cli\Console::class);
    }

    public function canCallConsoleOutputDependency(IntegrationTester $I): void
    {
        $I->wantToTest(
            'the console tester returns the expected console output stream instance'
        );

        expect(
            $this->ct->getApplication()->getDI()->get('consoleOutput')
        )
        ->isInstanceOf(StreamOutput::class);
    }

    public function canExecuteCommandOnDefaultHandler(IntegrationTester $I): void
    {
        $I->wantToTest('executing a console command on the default handler');
        $ct = $this->ct;
        $ct->execute(
            [
                'boot.php',
                'main'
            ]
        );
        expect($ct->getTask())->isInstanceOf(MainTask::class);
        expect($ct->getOutput())->equals('Main action' . PHP_EOL);
    }

    public function canExecuteCommandOnUnknownHandler(IntegrationTester $I): void
    {
        $I->wantToTest('executing a console command on an unknown handler');
        $I->expectThrowable(
            new DispatcherException('Stub\Task\Foobar handler class cannot be loaded', 2),
            function() {
                $this->ct->execute(
                    [
                        'boot.php',
                        'Foobar'
                    ]
                );
            }
        );
    }

    private function _config(): array
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
