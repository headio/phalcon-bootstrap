<?php
/**
 * This source file is subject to the MIT License.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this package.
 */
declare(strict_types=1);

namespace Headio\Phalcon\Bootstrap\Cli;

use Phalcon\Di\DiInterface;

interface BootstrapInterface
{
    /**
     * Return an instance of the bootstrap.
     */
    public static function handle(DiInterface $di): BootstrapInterface;

    /**
     * Run the console application and return the response.
     *
     * @return bool|\Phalcon\Cli\TaskInterface
     */
    public function run(array $server);
}
