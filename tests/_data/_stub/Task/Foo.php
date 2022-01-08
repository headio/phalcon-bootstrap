<?php
/**
 * This source file is subject to the MIT License.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this package.
 */
declare(strict_types=1);

namespace Stub\Task;

use Stub\Task\Base as BaseTask;
use BadMethodCallException;

class Foo extends BaseTask
{
    public function mainAction()
    {
        $this->output->writeln('<info>Main action</info>');

        return 0;
    }

    public function anotherAction(): int
    {
        $this->output->writeln('<info>Another action</info>');

        return 0;
    }

    public function forwardAction()
    {
        $this->dispatcher->forward(
            [
                'controller' => 'Foo',
                'action' => 'another'
            ]
        );
    }

    public function timezoneAction()
    {
        /** @var string */
        $timezone = $this->getDI()->getConfig()->get('timezone');
        $this->output->writeln('<info>' . $timezone . '</info>');

        return 0;
    }

    public function listAction(): int
    {
        $columns = ['id', 'label'];
        $this->table->setHeaders($columns);
        $this->table->addRows([
            ['id' => 1, 'label' => 'foo'],
            ['id' => 2, 'label' => 'bar']
        ])
        ->render();

        return 0;
    }

    public function createAction(): int
    {
        if (!(isset($this->dispatcher->getOptions()['label']))) {
            throw new BadMethodCallException('Missing value for option --label');
        }

        $this->output->writeln('<info>Role created</info>');

        return 0;
    }

    public function searchAction(): int
    {
        $columns = ['id', 'label'];

        if (isset($this->dispatcher->getOptions()['label'])) {
            return $this->displayView(
                $columns,
                [
                    'id' => 1,
                    'label' => 'foo'
                ]
            );
        }
    }

    public function helpAction(): int
    {
        $this->output->writeln('<info>' . $this->getHelp() . '</info>');

        return 0;
    }

    private function getHelp(): string
    {
        return <<<EOT
=========================================================================
Command line interface to Foo task
=========================================================================

-------------------------------------------------------------------------
list roles
-------------------------------------------------------------------------

Task:
php boot.php Foo listroles
php boot.php Foo listroles --label=foo

Arguments
--label: filter on label
EOT;
    }
}
