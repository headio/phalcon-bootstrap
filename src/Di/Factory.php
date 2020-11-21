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

namespace Headio\Phalcon\Bootstrap\Di;

use Phalcon\Config;
use Phalcon\DiInterface;
use Phalcon\Di\FactoryDefault;
use Phalcon\Di\FactoryDefault\Cli;

/**
 * A simple factory providing dependency injection container instantiation,
 * encapsulating the registration of service dependency definitions for
 * mvc, micro and cli applications.
 */
class Factory implements FactoryInterface
{
    /** @var Config */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function createDefaultMvc(): DiInterface
    {
        $di = new FactoryDefault();

        return $this->create($di);
    }

    /**
     * {@inheritdoc}
     */
    public function createDefaultCli() : DiInterface
    {
        $di = new Cli();

        return $this->create($di);
    }

    /**
     * {@inheritdoc}
     */
    public function create(DiInterface $di) : DiInterface
    {
        /** @var Config */
        $config = $this->config;
        $config->cli = false;

        if ($di instanceof Cli) {
            $config->cli = true;
        }

        $di->setShared('config', $config);

        /**
         * Register di container service definitions
         */
        if (isset($config->services)) {
            foreach ($config->services->toArray() ?? [] as $service) {
                $di->register(new $service());
            }
        }

        return $di;
    }
}
