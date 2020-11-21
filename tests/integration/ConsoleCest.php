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
use BadMethodCallException;

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

    public function canExecuteCommandWithNoHandler(IntegrationTester $I)
    {
        $I->wantTo('Execute console command with missing handler');

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

    public function canExecuteCommandOnDefaultHandler(IntegrationTester $I)
    {
        $I->wantTo('Execute console command on default handler');

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
        $I->wantTo('Execute console command on default handler with default action');

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

    public function canExecuteCommandOnKnownHandler(IntegrationTester $I)
    {
        $I->wantTo('Execute console command on known handler with no action');

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

    public function canExecuteNamedActionOnKnownHandler(IntegrationTester $I)
    {
        $I->wantTo('Execute console command on known handler with named action');

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

    public function canExecuteCommandOnUnknownHandler(IntegrationTester $I)
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

    public function canExecuteUnknownActionOnKnownHandler(IntegrationTester $I)
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

    public function canExecuteForwardingActionOnKnownHandler(IntegrationTester $I)
    {
        $I->wantTo('Execute console command on known handler with forwarding action');

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

    public function canExecuteListActionOnKnownHandler(IntegrationTester $I)
    {
        $I->wantTo('Execute console command on known handler');

        /** @var ConsoleTester */
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

    public function canExecuteCreateActionWithoutArgumentsOnKnownHandler(IntegrationTester $I)
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

    public function canExecuteCreateActionWithArgumentsOnKnownHandler(IntegrationTester $I)
    {
        $I->wantTo('Execute console command on known handler action with options');

        /** @var ConsoleTester */
        $ct = $this->ct;
        $ct->execute(
            [
                'boot.php',
                'foo',
                'create',
                '--label=foo',
            ]
        );

        expect($this->ct->getOutput())->equals('Role created' . PHP_EOL);
    }

    public function canExecuteSearchActionWithArgumentsOnKnownHandler(IntegrationTester $I)
    {
        $I->wantTo('Execute console command on known action with arguments');

        /** @var ConsoleTester */
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

        expect($this->ct->getOutput())->equals($expected);
    }

    public function canExecuteHelpActionOnKnownHandler(IntegrationTester $I)
    {
        $I->wantTo('Execute the console command on task menu action');

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
    protected function _config() : array
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
