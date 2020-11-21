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

namespace Stub\Service;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Symfony\Component\Console\Output\ConsoleOutput as Service;
use Symfony\Component\Console\Output\Output;

class ConsoleOutput implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(DiInterface $di) : void
    {
        $di->setShared(
            'consoleOutput',
            function () {
                return new Service(Output::VERBOSITY_NORMAL, true);
            }
        );
    }
}
