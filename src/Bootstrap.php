<?php
/**
 * This source file is subject to the MIT License.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this package.
 */
declare(strict_types=1);

namespace Headio\Phalcon\Bootstrap;

use Headio\Phalcon\Bootstrap\Application\Factory;
use Headio\Phalcon\Bootstrap\Application\FactoryInterface;
use Phalcon\Di\DiInterface;
use Phalcon\Http\ResponseInterface;

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
    public function run(string $uri, ?int $context = null): mixed
    {
        if ($context === Bootstrap::Micro) {
            return $this->factory->createForMicro()->handle($uri);
        }

        $response = $this->factory->createForMvc()->handle($uri);

        if ($response instanceof ResponseInterface) {
            return $response->send();
        }

        return $response;
    }
}
