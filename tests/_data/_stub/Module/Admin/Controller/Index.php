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

namespace Stub\Module\Admin\Controller;

use Phalcon\Mvc\Controller;
use Phalcon\Http\Response;

class Index extends Controller
{
    public function indexAction()
    {
        $response = new Response();
        $response->setStatusCode(200, 'OK');
        $response->setContent("Hello world");

        return $response;
    }
}
