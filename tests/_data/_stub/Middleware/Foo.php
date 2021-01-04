<?php
/**
 * This source file is subject to the MIT License.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this package.
 */
declare(strict_types=1);

namespace Stub\Middleware;

use Phalcon\Cli\Console;
use Phalcon\Events\EventInterface;

class Foo
{
    public function beforeHandleTask(EventInterface $event, Console $app): bool
    {
        $config = $app->getDI()->getConfig();
        $config['timezone'] = 'America/New_York';

        return true;
    }
}
