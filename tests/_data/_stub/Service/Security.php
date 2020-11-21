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
use Phalcon\Security as Service;
use Phalcon\DiInterface;

class Security implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(DiInterface $di) : void
    {
        $di->setShared(
            'security',
            function () {
                $config = $this->get('config');
                $service = new Service();
                $service->setWorkFactor($config->security->encryption->workFactor);
                $service->setDefaultHash($config->security->encryption->hash);

                return $service;
            }
        );
    }
}
