<?php
/**
 * This source file is subject to the MIT License.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this package.
 */
declare(strict_types=1);

namespace Stub\Provider;

use Phalcon\Annotations\Adapter\Memory as Service;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Di\DiInterface;

class Annotation implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(DiInterface $di): void
    {
        $di->setShared(
            'annotations',
            function () {
                $config = $this->getConfig();

                if ($config->debug) {
                    $service = new Service();
                } else {
                    $adapter = 'Phalcon\\Annotations\\Adapter\\' . $config->annotations->adapter;
                    $service = new $adapter($config->annotations->options->toArray());
                }

                return $service;
            }
        );
    }
}
