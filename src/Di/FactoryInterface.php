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

interface FactoryInterface
{
    /**
     * Return an instance of a dependency injection container,
     * automatically registering service dependency definitions.
     */
    public function create(DiInterface $di): DiInterface;

    /**
     * Create an instance of Phalcon's factory default dependency
     * injection container for the mvc environment.
     */
    public function createDefaultMvc(): DiInterface;

    /**
     * Create an instance of Phalcon's factory default dependency
     * injection container for the cli environment.
     */
    public function createDefaultCli(): DiInterface;
}
