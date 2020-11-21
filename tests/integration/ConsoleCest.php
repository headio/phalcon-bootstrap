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

namespace Integration;

use Phalcon\Cli\Dispatcher\Exception as DispatcherException;
use Phalcon\Di;
use Stub\Task\Foo;
use Stub\Task\Main;
use IntegrationTester;

class ConsoleCest
{
    private $ct;

    public function _before(IntegrationTester $I)
    {
        Di::reset();

        /** @var Headio\Phalcon\Bootstrap\Cli\Console\ConsoleTester */
        $this->ct = $I->getConsoleTester(
            $I->bootConsole($this->_config())
        );
    }

    public function _after(IntegrationTester $I)
    {
        $this->ct = null;
    }

    public function canExecuteCommandWithoutArguments(IntegrationTester $I)
    {
        $I->wantToTest('executing a command without any arguments');

        /** @var ConsoleTester */
        $ct = $this->ct;
        $ct->execute(
            [
                'boot.php',
            ]
        );

        expect($ct->getTask())->isInstanceOf(Main::class);

        expect($ct->getOutput())->equals('Main action' . PHP_EOL);
    }

    public function CanExecuteCommandOnDefaultHandlerWithoutActionArgument(IntegrationTester $I)
    {
        $I->wantToTest('executing a command on the default handler without an action argument');

        /** @var ConsoleTester */
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

    public function canExecuteDefaultActionOnDefaultHandler(IntegrationTester $I)
    {
        $I->wantToTest('executing the default action on the default handler');

        /** @var ConsoleTester */
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

    public function canExecuteCommandOnHandlerInRegisteredNamespace(IntegrationTester $I)
    {
        $I->wantToTest('executing a command on a known handler without an action argument');

        /** @var ConsoleTester */
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

    public function testCanExecuteNamedActionOnHandlerInRegisteredNamespace(IntegrationTester $I)
    {
        $I->wantToTest('executing a command on a known handler with a named action');

        /** @var ConsoleTester */
        $ct = $this->ct;
        $ct->execute(
            [
                'boot.php',
                'Foo',
                'another'
            ]
        );

        expect($ct->getTask())->isInstanceOf(Foo::class);

        expect($this->ct->getOutput())->equals('Another action' . PHP_EOL);
    }

    public function testCanExecuteCommandOnUnknownHandler(IntegrationTester $I)
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

    public function testCanExecuteUnknownActionOnKnownHandler(IntegrationTester $I)
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

    public function testCanExecuteForwardingActionOnKnownHandler(IntegrationTester $I)
    {
        $I->wantToTest('executing command on a known handler with a forwarding action');

        /** @var ConsoleTester */
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

    public function testCanRenderTableView(IntegrationTester $I)
    {
        $I->wantToTest('rendering a table view using symfony console');

        /** @var ConsoleTester */
        $ct = $this->ct;
        $ct->execute(
            [
                'boot.php',
                'foo',
                'list'
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

    public function testCanRenderTableViewWithArguments(IntegrationTester $I)
    {
        $I->wantToTest('passing cli dispatcher options to render a table view using symfony console');

        /** @var ConsoleTester */
        $ct = $this->ct;
        $ct->execute(
            [
                'boot.php',
                'foo',
                'search',
                'foo'
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

        expect($this->ct->getOutput())->equals($expected);
    }

    public function testCanExecuteActionWithMissingArguments(IntegrationTester $I)
    {
        $I->expectThrowable(
            new \BadMethodCallException('Missing value for option --label'),
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

    public function testCanExecuteActionWithArguments(IntegrationTester $I)
    {
        $I->wantToTest('executing a command on handler action with arguments');

        /** @var ConsoleTester */
        $ct = $this->ct;
        $ct->execute(
            [
                'boot.php',
                'foo',
                'create',
                '--label=bar'
            ]
        );

        expect($this->ct->getOutput())->equals('Role created' . PHP_EOL);
    }

    public function testCanExecuteHelpActionOnKnownHandler(IntegrationTester $I)
    {
        $I->wantToTest('rendering a arbitary cli help menu');

        /** @var ConsoleTester */
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

    /**
     * Return Test config
     */
    private function _config() : array
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
                'Stub\Service\EventManager',
                'Stub\Service\Logger',
                'Stub\Service\Dispatcher',
                'Stub\Service\Router',
                'Stub\Service\ConsoleOutput',
            ],
            'timezone' => 'Europe/Berlin'
        ];
    }
}
