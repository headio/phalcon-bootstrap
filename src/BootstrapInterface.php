<?php
/**
 * This source file is subject to the MIT License.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this package.
 */
declare(strict_types=1);

namespace Headio\Phalcon\Bootstrap;

use Phalcon\DiInterface;

interface BootstrapInterface
{
    public const Micro = 100;

    /**
     * Return an instance of the bootstrap.
     */
    public static function handle(DiInterface $di): BootstrapInterface;

    /**
     * Run the mvc (or micro) application and return the response.
     *
     * @return Phalcon\Http\ResponseInterface|bool
     */
    public function run(?int $context);
}
