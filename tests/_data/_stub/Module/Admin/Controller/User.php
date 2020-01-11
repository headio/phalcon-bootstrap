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

/**
 * User handler
 */
class User extends Controller
{
    /**
     * Inject service layer
     */
    public function onConstruct() : void
    {
    }

    public function indexAction()
    {
        $context = __METHOD__;
        $response = new Response();
        $response->setStatusCode(200, 'OK');
        $response->setContent($context);

        return $response;
    }

    public function readAction(int $id)
    {
        $id = $this->filter->sanitize($id, 'absint');
        $response = new Response();
        $response
            ->setStatusCode(200, 'OK')  
            ->setJsonContent(
                [
                    'data' => [
                        'type' => 'Users',
                        'id' => $id,
                        'attributes' => [
                            'name' => 'John Doe'
                        ]
                    ]
                ]
            )
        ;

        return $response;
    }
}
