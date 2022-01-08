<?php
/**
 * This source file is subject to the MIT License.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this package.
 */
declare(strict_types=1);

namespace Headio\Phalcon\Bootstrap\Cli\Console;

use Headio\Phalcon\Bootstrap\Exception\MissingDependencyException;
use Phalcon\Cli\Console;
use Phalcon\Cli\TaskInterface;
use Closure;
use function ftruncate;
use function rewind;
use function stream_get_contents;

final class ConsoleTester
{
    private ?TaskInterface $task = null;

    public function __construct(private Console $console)
    {
    }

    /**
     * Set the stream handler to capture the output stream.
     *
     * @throws MissingDependencyException
     */
    public function setStream(Closure $closure): static
    {
        /** @var \Phalcon\Di\DiInterface */
        $di = $this->console->getDI();

        if (!$di->has('consoleOutput')) {
            throw new MissingDependencyException(
                'Missing "ConsoleOutput" service dependency'
            );
        }

        $service = $di->getService('consoleOutput')->setDefinition($closure);

        return $this;
    }

    /**
     * Execute the console command and capture the output stream.
     */
    public function execute(array $server): void
    {
        $this->task = $this->console->setArgument($server)->handle();
    }

    /**
     * Return the console application instance.
     */
    public function getApplication(): Console
    {
        return $this->console;
    }

    /**
     * Return the current task instance.
     */
    public function getTask(): ?TaskInterface
    {
        return $this->task;
    }

    /**
     * Return the stream output from the stream handler.
     */
    public function getOutput(): string
    {
        /** @var \Phalcon\Di\DiInterface */
        $di = $this->console->getDI();
        $stream = $di->get('consoleOutput')->getStream();

        if (!$stream) {
            return '';
        }

        rewind($stream);
        // ftruncate($this->stream, 0);

        return stream_get_contents($stream);
    }
}
