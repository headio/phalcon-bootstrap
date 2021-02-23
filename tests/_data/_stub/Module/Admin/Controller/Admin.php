<?php
/**
 * This source file is subject to the MIT License.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this package.
 */
declare(strict_types=1);

namespace Stub\Module\Admin\Controller;

use Phalcon\Http\Response;

/**
 * @RoutePrefix("/admin", name="adminIndex")
 */
class Admin
{
    /**
     * Index action
     *
     * @Route("/", methods={"GET"}, name="adminIndex")
     *
     * @Route("/index", methods={"GET"}, name="adminIndex")
     */
    public function indexAction()
    {
        return new Response('Hello admin world');
    }

    /**
     * Contact action
     *
     * @Route("/contact", methods={"GET"}, name="adminContact")
     */
    public function contactAction()
    {
        return new Response('Hello admin contact');
    }
}
