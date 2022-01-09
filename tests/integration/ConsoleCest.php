<?php
/**
 * This source file is subject to the MIT License.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this package.
 */
declare(strict_types=1);

namespace Integration;

use Headio\Phalcon\Bootstrap\Cli\Console\ConsoleTester;
use Phalcon\Cli\Dispatcher\Exception as DispatcherException;
use Phalcon\Di\Di;
use Stub\Task\Foo;
use Stub\Task\Main;
use IntegrationTester;
use BadMethodCallException;

class ConsoleCest
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

    public function canExecuteCommandWithNoHandler(IntegrationTester $I): void
    {
        $I->wantTo('Execute console command with missing handler');
        $ct = $this->ct;
        $ct->execute(
            [
                'boot.php',
            ]
        );

        expect($ct->getTask())->isInstanceOf(Main::class);
        expect($ct->getOutput())->equals('Main action' . PHP_EOL);
    }

    public function canExecuteCommandOnDefaultHandler(IntegrationTester $I): void
    {
        $I->wantTo('Execute console command on default handler');
        $ct = $this->ct;
        $ct->execute(
            [
                'boot.php',
                'main'
            ]
        );

        expect($ct->getTask())->isInstanceOf(Main::class);
        expect($ct->getOutput())->equals('Main action' . PHP_EOL);
    }

    public function canExecuteDefaultActionOnDefaultHandler(IntegrationTester $I): void
    {
        $I->wantTo('Execute console command on default handler with default action');
        $ct = $this->ct;
        $ct->execute(
            [
                'boot.php',
                'main',
                'main'
            ]
        );

        expect($ct->getTask())->isInstanceOf(Main::class);
        expect($ct->getOutput())->equals('Main action' . PHP_EOL);
    }

    public function canExecuteCommandOnKnownHandler(IntegrationTester $I): void
    {
        $I->wantTo('Execute console command on known handler with no action');
        $ct = $this->ct;
        $ct->execute(
            [
                'boot.php',
                'Foo'
            ]
        );

        expect($ct->getTask())->isInstanceOf(Foo::class);
        expect($ct->getOutput())->equals('Main action' . PHP_EOL);
    }

    public function canExecuteNamedActionOnKnownHandler(IntegrationTester $I): void
    {
        $I->wantTo('Execute console command on known handler with named action');
        $ct = $this->ct;
        $ct->execute(
            [
                'boot.php',
                'Foo',
                'another'
            ]
        );

        expect($ct->getTask())->isInstanceOf(Foo::class);
        expect($ct->getOutput())->equals('Another action' . PHP_EOL);
    }

    public function canExecuteCommandOnUnknownHandler(IntegrationTester $I): void
    {
        $I->expectThrowable(
            new DispatcherException('Stub\Task\Frontend handler class cannot be loaded', 2),
            function () {
                $this->ct->execute(
                    [
                        'boot.php',
                        'Frontend'
                    ]
                );
            }
        );
    }

    public function canExecuteUnknownActionOnKnownHandler(IntegrationTester $I): void
    {
        $I->expectThrowable(
            new DispatcherException("Action 'bar' was not found on handler 'Foo'", 5),
            function () {
                $this->ct->execute(
                    [
                        'boot.php',
                        'Foo',
                        'bar'
                    ]
                );
            }
        );
    }

    public function canExecuteForwardingActionOnKnownHandler(IntegrationTester $I): void
    {
        $I->wantTo('Execute console command on known handler with forwarding action');
        $ct = $this->ct;
        $ct->execute(
            [
                'boot.php',
                'foo',
                'forward'
            ]
        );

        expect($ct->getTask())->isInstanceOf(Foo::class);
        expect($ct->getOutput())->equals('Another action' . PHP_EOL);
    }

    public function eventListenerCallsMiddleware(IntegrationTester $I): void
    {
        $I->wantToTest('that the event manager calls attached console middlewares');
        $ct = $this->ct;
        $ct->execute(
            [
                'boot.php',
                'Foo',
                'timezone'
            ]
        );
        expect($ct->getOutput())->equals('America/New_York' . PHP_EOL);
    }

    public function canExecuteListActionOnKnownHandler(IntegrationTester $I): void
    {
        $I->wantTo('Execute console command on known handler');
        $ct = $this->ct;
        $ct->execute(
            [
                'boot.php',
                'foo',
                'list',
                '--foo=bar'
            ]
        );

        $expected =
<<<'TABLE'
+----+-------+
| id | label |
+----+-------+
| 1  | foo   |
| 2  | bar   |
+----+-------+

TABLE;

        expect($ct->getOutput())->equals($expected);
    }

    public function canExecuteCreateActionWithoutArgumentsOnKnownHandler(IntegrationTester $I): void
    {
        $I->expectThrowable(
            new BadMethodCallException('Missing value for option --label'),
            function () {
                $this->ct->execute(
                    [
                        'boot.php',
                        'foo',
                        'create'
                    ]
                );
            }
        );
    }

    public function canExecuteCreateActionWithArgumentsOnKnownHandler(IntegrationTester $I): void
    {
        $I->wantTo('Execute console command on known handler action with options');
        $ct = $this->ct;
        $ct->execute(
            [
                'boot.php',
                'foo',
                'create',
                '--label=foo',
            ]
        );

        expect($ct->getOutput())->equals('Role created' . PHP_EOL);
    }

    public function canExecuteSearchActionWithArgumentsOnKnownHandler(IntegrationTester $I): void
    {
        $I->wantTo('Execute console command on known action with arguments');
        $ct = $this->ct;
        $ct->execute(
            [
                'boot.php',
                'foo',
                'search',
                '--label=foo'
            ]
        );

        $expected =
<<<'TABLE'
+----+-------+
| id | label |
+----+-------+
| 1  | foo   |
+----+-------+

TABLE;

        expect($ct->getOutput())->equals($expected);
    }

    public function canExecuteHelpActionOnKnownHandler(IntegrationTester $I): void
    {
        $I->wantTo('Execute the console command on task menu action');
        $ct = $this->ct;
        $ct->execute(
            [
                'boot.php',
                'Foo',
                'help'
            ]
        );

        $expected =
<<<EOT
=========================================================================
Command line interface to Foo task
=========================================================================

-------------------------------------------------------------------------
list roles
-------------------------------------------------------------------------

Task:
php boot.php Foo listroles
php boot.php Foo listroles --label=foo

Arguments
--label: filter on label

EOT;

        expect($ct->getTask())->isInstanceOf(Foo::class);

        expect($ct->getOutput())->equals($expected);
    }

    private function _config(): array
    {
        return [
            'debug' => false,
            'dispatcher' => [
                'defaultTaskNamespace' => 'Stub\\Task',
            ],
            'middleware' => [
                'Stub\\Middleware\\Foo'
            ],
            'services' => [
                'Stub\Provider\EventManager',
                'Stub\Provider\Logger',
                'Stub\Provider\Dispatcher',
                'Stub\Provider\Router',
                'Stub\Provider\ConsoleOutput',
            ],
            'timezone' => 'Europe/Berlin'
        ];
    }
}
