<?php
/*
 * This source file is subject to the MIT License.
 *
 * (c) Dominic Beck <dominic@headcrumbs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this package.
 */
namespace Stub\Config;

use Stub\Module\Admin\Controller\{ Index, User };
use Phalcon\Mvc\Micro\Collection;


$handler = new Collection();
$handler->setHandler(User::class, true);
$handler->setPrefix('/users');
$handler->get('/', 'indexAction', 'apiUsers');
$handler->get('/{id:[0-9]+}', 'readAction', 'apiUserRead');
$app->mount($handler);

$handler = new Collection();
$handler->setHandler(Index::class, true);
$handler->setPrefix('/');
$handler->get('/', 'indexAction', 'apiIndex');
$app->mount($handler);

