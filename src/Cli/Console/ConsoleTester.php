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

namespace Headio\Phalcon\Bootstrap\Cli\Console;

use Headio\Phalcon\Bootstrap\Exception\MissingDependencyException;
use Phalcon\Cli\{ Console, TaskInterface };
use Phalcon\Config;
use function rewind;
use function stream_get_contents;
use function str_replace;
use function ftruncate;
use Closure;

class ConsoleTester
{
    /**
     * @var Console
     */
    private $console;

    /**
     * @var TaskInterface
     */
    private $task;

    /**
     * @var \Resource
     */
    private $stream;

    public function __construct(Console $console)
    {
        $this->console = $console;
    }

    /**
     * Set the stream handler to capture the output stream.
     *
     * @throws MissingDependencyException
     */
    public function setStream(Closure $closure) : self
    {
        /** @var DiInterface */
        $di = $this->console->getDI();

        if (!$di->has('consoleOutput')) {
            throw new MissingDependencyException('Missing "ConsoleOutput" service dependency');
        }

        $service = $di->getService('consoleOutput')->setDefinition($closure);

        return $this;
    }

    /**
     * Execute the console command and capture the output stream.
     */
    public function execute(array $server) : void
    {
        /** @var DiInterface */
        $di = $this->console->getDI();

        /** @var TaskInterface */
        $this->task = $this->console->setArgument($server)->handle();

        $this->stream = $di->get('consoleOutput')->getStream();
    }

    /**
     * Return the console application instance
     */
    public function getApplication() : Console
    {
        return $this->console;
    }

    /**
     * Return the current task instance
     */
    public function getTask() : TaskInterface
    {
        return $this->task;
    }

    /**
     * Return the stream from the stream handler.
     */
    public function getStream()
    {
        return $this->stream;
    }

    /**
     * Return the stream output from the stream handler.
     */
    public function getOutput() : string
    {
        rewind($this->stream);
        $output = stream_get_contents($this->stream);

        // ftruncate($this->stream, 0);

        return $output;
    }
}
