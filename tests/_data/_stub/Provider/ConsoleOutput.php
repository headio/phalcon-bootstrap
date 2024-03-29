<?php
/**
 * This source file is subject to the MIT License.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this package.
 */
declare(strict_types=1);

namespace Stub\Provider;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Di\DiInterface;
use Symfony\Component\Console\Output\ConsoleOutput as Service;
use Symfony\Component\Console\Output\Output;

class ConsoleOutput implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(DiInterface $di): void
    {
        $di->setShared(
            'consoleOutput',
            function () {
                return new Service(Output::VERBOSITY_NORMAL, true);
            }
        );
    }
}
