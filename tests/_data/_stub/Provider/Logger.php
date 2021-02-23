<?php
/**
 * This source file is subject to the MIT License.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this package.
 */
declare(strict_types=1);

namespace Stub\Provider;

use Monolog\Logger as Service;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Di\DiInterface;

class Logger implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(DiInterface $di): void
    {
        $di->setShared(
            'logger',
            function () {
                $config = $this->get('config');
                $formatter = new LineFormatter(
                    "%datetime% %channel%.%level_name%: %message% %context% %extra%\n",
                    'c',
                    true,
                    true
                );

                if ($config->cli) {
                    $service = new Service('Console');
                    $fileHandler = new RotatingFileHandler(
                        $config->logPath . 'Console.log',
                        14,
                        Service::DEBUG
                    );
                    $fileHandler->setFormatter($formatter);
                    $service->pushHandler(
                        new StreamHandler('php://stdout')
                    );
                    $service->pushHandler($fileHandler);

                    return $service;
                }

                $fileHandler = new RotatingFileHandler(
                    $config->logPath . 'Web.log',
                    30,
                    Service::DEBUG
                );
                $fileHandler->setFormatter($formatter);
                $service->pushHandler(new StreamHandler('php://stdout'));
                $service->pushHandler($fileHandler);

                return $service;
            }
        );
    }
}
