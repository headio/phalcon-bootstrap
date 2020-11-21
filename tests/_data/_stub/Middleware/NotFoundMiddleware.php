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

namespace Stub\Middleware;

use Phalcon\Di\Injectable;
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Micro\MiddlewareInterface;

class NotFoundMiddleware extends Injectable implements MiddlewareInterface
{
    /**
     * Route was not found
     */
    public function beforeNotFound() : bool
    {
        $response = $this->application->getService('response');
        $response
            ->setStatusCode(404)
            ->setContent('404 Not Found')
            ->send();

        /**
         * Prevent further middleware execution
         */
        $this->application->stop();

        /**
         * Stop further execution
         */
        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @return Bool
     */
    public function call(Micro $application)
    {
        return true;
    }
}
