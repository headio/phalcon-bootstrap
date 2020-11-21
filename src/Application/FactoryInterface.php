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

namespace Headio\Phalcon\Bootstrap\Application;

use Phalcon\Di\DiInterface;
use Phalcon\Cli\Console;
use Phalcon\Mvc\Application;
use Phalcon\Mvc\Micro;

interface FactoryInterface
{
    /**
     * Bootstrap a console application, encapsulation
     * module registration, event management and
     * middleware logic assignment.
     */
    public function createForCli() : Console;

    /**
     * Bootstrap an api or micro service, encapsulating
     * handler registration, event management and middleware
     * logic assignment.
     */
    public function createForMicro() : Micro;

    /**
     * Bootstrap an application following the mvc patteren,
     * encapsulating module registration, event management
     * and middleware logic assignment.
     */
    public function createForMvc() : Application;
}
