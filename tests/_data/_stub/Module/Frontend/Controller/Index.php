<?php
/**
 * This source file is subject to the MIT License.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this package.
 */
declare(strict_types=1);

namespace Stub\Module\Frontend\Controller;

use Phalcon\Mvc\Controller;
use Phalcon\Http\Response;

/**
 * @RoutePrefix("/index", name="frontendIndex")
 */
class Index
{
    /**
     * Default action
     *
     * @Route("/", methods={"GET"}, name="frontendIndex")
     */
    public function indexAction()
    {
        return new Response('Hello world');
    }

    /**
     * @Route("/contact", methods={"GET"}, name="frontendContact")
     */
    public function contactAction()
    {
        return new Response('Hello contact');
    }
}
