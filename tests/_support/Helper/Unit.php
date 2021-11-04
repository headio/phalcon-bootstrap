<?php
/**
 * This source file is subject to the MIT License.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this package.
 */
declare(strict_types=1);

namespace Helper;

use Headio\Phalcon\Bootstrap\Application\Factory as AppFactory;
use Headio\Phalcon\Bootstrap\Cli\Console\ConsoleTester;
use Headio\Phalcon\Bootstrap\Di\Factory as DiFactory;
use Phalcon\Config;
use Phalcon\Cli\Console;
use Symfony\Component\Console\Output\StreamOutput;

class Unit extends \Codeception\Module
{
    /**
     * Return a bootstrapped console application
     */
    public function bootConsole(array $global): Console
    {
        $config = new Config($global);

        /** @var Phalcon\Di\DiInterface */
        $di = (new DiFactory($config))->createDefaultCli();

        /** @var Console */
        $app = (new AppFactory($di))->createForCli();

        return $app;
    }

    /**
     * Return an instance of the console tester
     */
    public function getConsoleTester(Console $console): ConsoleTester
    {
        $ct = new ConsoleTester($console);
        $ct->setStream(
            function () {
                return new StreamOutput(
                    fopen(TEST_OUTPUT_DIR . 'Var/Log/Console/output.log', 'w+', false)
                );
            }
        );

        return $ct;
    }
}
