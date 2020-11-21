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

namespace Headio\Phalcon\Bootstrap\Cli;

use Phalcon\DiInterface;

interface BootstrapInterface
{
    /**
     * Return an instance of the bootstrap.
     */
    public static function handle(DiInterface $di) : BootstrapInterface;

    /**
     * Run the console application and return the response.
     *
     * @return Phalcon\Cli\TaskInterface|bool
     */
    public function run(array $server);
}
