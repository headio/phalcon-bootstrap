<?php
/**
 * This source file is subject to the MIT License.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this package.
 */
declare(strict_types=1);

namespace Headio\Phalcon\Bootstrap\Di;

use Phalcon\Config;
use Phalcon\Di\DiInterface;
use Phalcon\Di\FactoryDefault;
use Phalcon\Di\FactoryDefault\Cli;

/**
 * A simple factory providing dependency injection container instantiation,
 * encapsulating the registration of service dependency definitions for
 * mvc, micro and cli applications.
 */
class Factory implements FactoryInterface
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function createDefaultMvc(): DiInterface
    {
        $di = new FactoryDefault();

        return $this->create($di);
    }

    /**
     * {@inheritDoc}
     */
    public function createDefaultCli(): DiInterface
    {
        $di = new Cli();

        return $this->create($di);
    }

    /**
     * {@inheritDoc}
     */
    public function create(DiInterface $di): DiInterface
    {
        $config = $this->config;
        $config->cli = false;

        if ($di instanceof Cli) {
            $config->cli = true;
        }

        $di->setShared('config', $config);

        /**
         * Register the service definitions
         */
        if (isset($config->services)) {
            foreach ($config->services->toArray() ?? [] as $service) {
                $di->register(new $service());
            }
        }

        return $di;
    }
}
