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

namespace Stub\Module\Admin\Task;

use Stub\Task\BaseTask;

class Bar extends BaseTask
{
    public function mainAction()
    {
        $this->output->writeln('<info>Bar main action</info>');
    }

    public function fooAction()
    {
        $this->output->writeln('<info>Boo foo action</info>');
    }
}
