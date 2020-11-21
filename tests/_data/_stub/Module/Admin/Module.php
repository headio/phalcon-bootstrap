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

namespace Stub\Module\Admin;

use Phalcon\Di\DiInterface;
use Phalcon\Mvc\ModuleDefinitionInterface;

class Module implements ModuleDefinitionInterface
{
    /**
     * {@inheritdoc}
     */
    public function registerAutoloaders(DiInterface $di = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function registerServices(DiInterface $di)
    {
        $config = $di->get('config');

        if ($config->path('cli', false)) {
            $di->get('dispatcher')->setDefaultNamespace(__NAMESPACE__ . '\\Controller');
        }
    }
}
