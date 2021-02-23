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
use Phalcon\Crypt as Service;
use Phalcon\Di\DiInterface;

class Crypt implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(DiInterface $di): void
    {
        $di->setShared(
            'crypt',
            function () {
                $config = $this->get('config');
                $service = new Service();
                $service->setKey($config->security->encryption->key);
                $service->setCipher($config->security->encryption->cipher);

                if (isset($config->security->encryption->useSigning)) {
                    $service->useSigning($config->security->encryption->useSigning);
                }

                if (isset($config->security->encryption->padding)) {
                    $service->setPadding($config->security->encryption->padding);
                }

                return $service;
            }
        );
    }
}
