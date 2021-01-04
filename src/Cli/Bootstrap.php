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
use Phalcon\DiInterface;

class Bootstrap implements BootstrapInterface
{
    /** @var FactoryInterface */
    private $factory;

    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public static function handle(DiInterface $di): BootstrapInterface
    {
        $factory = new Factory($di);

        return new static($factory);
    }

    /**
     * {@inheritdoc}
     *
     * @return Phalcon\Cli\TaskInterface|bool
     */
    public function run(array $server)
    {
        /** @var Phalcon\Cli\Console */
        $app = $this->factory->createForCli();

        return $app->setArgument($server['argv'])->handle();
    }
}
