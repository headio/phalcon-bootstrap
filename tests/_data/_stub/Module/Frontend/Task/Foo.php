<?php
/**
 * This source file is subject to the MIT License.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this package.
 */
declare(strict_types=1);

namespace Stub\Module\Frontend\Task;

use Stub\Task\BaseTask;

class Foo extends BaseTask
{
    public function mainAction()
    {
        $this->output->writeln('<info>Foo main action</info>');
    }

    public function barAction()
    {
        $this->output->writeln('<info>Foo bar action</info>');
    }
}
