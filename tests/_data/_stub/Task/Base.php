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

namespace Stub\Task;

use Headio\Phalcon\Bootstrap\Exception\MissingDependencyException;
use Phalcon\Cli\Task;
use Phalcon\Mvc\ModelInterface;
use Phalcon\Mvc\Model\{ ResultsetInterface, ValidationFailed };
use Phalcon\Mvc\Model\Transaction\Failed as TransactionFailed;
use Symfony\Component\Console\Helper\{ Table, TableSeparator };
use function implode;
use Throwable;

class BaseTask extends Task
{
    /**
     * @var ConsoleOutput
     */
    protected $output;

    /**
     * @var Table;
     */
    protected $table;

    public function onConstruct()
    {
        if (!$this->getDI()->get('consoleOutput')) {
            throw new MissingDependencyException('Missing "ConsoleOutput" service dependency');
        }

        $this->output = $this->getDI()->get('consoleOutput');
        $this->table = new Table($this->output);
    }

    /**
     * Render table layout view for a query resultset.
     */
    protected function displayTable(ResultsetInterface $resultset) : int
    {
        if ($resultset->count() <> 0) {
            $resultset->rewind();
            $this->output->writeln(sprintf('<info>%d record(s)</info>', $resultset->count()));
            $this->table->addRows($resultset->toArray())->render();
            return 0;
        }

        $this->output->writeln('<error>No results</error>');

        return 0;
    }

    /**
     * Render view for model data.
     */
    protected function displayView(array $columns, array $data) : int
    {
        $this->table->setHeaders($columns);
        $this->table->addRow($data)->render();

        return 0;
    }

    /**
     * Handle exception
     */
    protected function handleException(Throwable $e) : string
    {
        $exceptionMessage = $e->getMessage();
        /**
         * Normalize the transaction failed and entity validation
         * error messages for the console.
         */
        if ($e instanceof TransactionFailed || $e instanceof ValidationFailed) {
            if (!empty($e->getRecordMessages())) {
                /** @var Array */
                $validationErrors = $this->getValidationErrors($e->getRecord());

                foreach($validationErrors as $key => $message) {
                    $errors[] = $key . ': ' . $message;
                }

                if (!empty($errors)) {
                    $exceptionMessage .= PHP_EOL . implode(' ' . PHP_EOL, $errors);  
                }
            }
        }

        return $exceptionMessage;
    }

    /**
     * Return an array representation of the model validation errors.
     */
    private function getValidationErrors(ModelInterface $model) : array
    {
        $errors = [];

        if ($model->validationHasFailed()) {
            foreach ($model->getMessages() as $message) {
                if (isset($errors[$message->getField()])) {
                    $errors[$message->getField()] .= ' ' . $message->getmessage();
                } else {
                    $errors[$message->getField()] = $message->getmessage();
                }
            }
        }

        return $errors;
    }
}
