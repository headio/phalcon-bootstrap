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

namespace Helper;

use Headio\Phalcon\Bootstrap\Application\Factory as AppFactory;
use Headio\Phalcon\Bootstrap\Bootstrap;
use Headio\Phalcon\Bootstrap\BootstrapInterface;
use Headio\Phalcon\Bootstrap\Cli\Console\ConsoleTester;
use Headio\Phalcon\Bootstrap\Di\Factory as DiFactory;
use Phalcon\Config;
use Phalcon\Cli\Console;
use Symfony\Component\Console\Output\StreamOutput;
use Codeception\Util\Debug;

class Integration extends \Codeception\Module
{
    /**
     * Return a bootstrapped console application
     */
    public function bootConsole(array $global) : Console
    {
        /** @var Config */
        $config = new Config($global);

        /** @var Phalcon\DiInterface */
        $di = (new DiFactory($config))->createDefaultCli();

        /** @var Console */
        $app = (new AppFactory($di))->createForCli();

        return $app;
    }

    /**
     * Return an application factory instance with a fully configured
     * mvc dependency injection container.
     */
    public function bootMvc(array $global) : BootstrapInterface
    {
        /** @var Config */
        $config = new Config($global);

        /** @var Phalcon\DiInterface */
        $di = (new DiFactory($config))->createDefaultMvc();

        /** @var BootstrapInterface */
        $bootstrap = Bootstrap::handle($di);

        return $bootstrap;
    }

    /**
     * {@inheritdoc}
     */
    public function debug($mixed)
    {
        return Debug::debug($mixed);
    }

    /**
     * Return an instance of the console tester
     */
    public function getConsoleTester(Console $console) : ConsoleTester
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

    /**
     * Return the bootstrapped Phalcon application
     */
    public function getApplication()
    {
        return $this->getModule('Phalcon')->getApplication();
    }

    /**
     * Send http request
     */
    public function sendRequest(string $method, string $uri, ?array $params)
    {
        return $this->getModule('Phalcon')->_request($method, $uri, $params ?? []);
    }

    /**
     * Validate the response payload
     */
    public function seeResponseEquals(string $content) : bool
    {
        $this->assertEquals(
            $content,
            $this->getModule('Phalcon')->_getResponseContent(),
            'response payload'
        );
    }
}
