<?php
/**
 * This source file is subject to the MIT License.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this package.
 */
declare(strict_types=1);

namespace Headio\Phalcon\Bootstrap\Cli;

use Headio\Phalcon\Bootstrap\Application\Factory;
use Headio\Phalcon\Bootstrap\Application\FactoryInterface;
use Phalcon\Di\DiInterface;
use Phalcon\Cli\TaskInterface;

class Bootstrap implements BootstrapInterface
{
    final public function __construct(private FactoryInterface $factory)
    {
    }

    /**
     * {@inheritDoc}
     */
    public static function handle(DiInterface $di): BootstrapInterface
    {
        $factory = new Factory($di);

        return new static($factory);
    }

    /**
     * {@inheritDoc}
     */
    public function run(array $server): bool|TaskInterface
    {
        $app = $this->factory->createForCli();

        return $app->setArgument($server['argv'])->handle();
    }
}
