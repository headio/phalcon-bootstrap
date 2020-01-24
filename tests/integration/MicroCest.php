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

namespace Integration;

use Headio\Phalcon\Bootstrap\Bootstrap;
use Headio\Phalcon\Bootstrap\BootstrapInterface;
use Headio\Phalcon\Bootstrap\Di\Factory as DiFactory;
use Phalcon\Config;
use Phalcon\Http\ResponseInterface;
use Stub\Middleware\NotFoundMiddleware;
use IntegrationTester;

class MicroCest
{
    private $bootstrap;

    public function _before(IntegrationTester $I)
    {
        /** @var Phalcon\DiInterface */
        $di = (new DiFactory(new Config($this->_config())))->createDefaultMvc();

        /** @var BootstrapInterface */
        $this->bootstrap = Bootstrap::handle($di);
    }

    protected function _after(IntegrationTester $I)
    {
        $this->bootstrap = null;
    }

    public function executeIndexActionOnIndexHandler(IntegrationTester $I)
    {
        $I->wantTo('Execute request on index action in user handler.');

        $_GET['_url'] = '/';

        /** @var ResponseInterface */
        $response = $this->bootstrap->run(Bootstrap::Micro);

        $I->assertInstanceOf(ResponseInterface::class, $response);

        $I->assertEquals(200, $response->getStatusCode());

        $I->assertEquals('Hello world', $response->getContent());
    }

    public function executeIndexActionOnUserHandler(IntegrationTester $I)
    {
        $I->wantTo('Execute request on index action in user handler.');

        $_GET['_url'] = '/users';

        /** @var ResponseInterface */
        $response = $this->bootstrap->run(Bootstrap::Micro);

        $I->assertInstanceOf(ResponseInterface::class, $response);

        $I->assertEquals(200, $response->getStatusCode());

        $I->assertEquals('Stub\Module\Admin\Controller\User::indexAction', $response->getContent());
    }

    public function executeReadActionOnUserHandler(IntegrationTester $I)
    {
        $I->wantTo('Execute request on read action in user handler.');

        $_GET['_url'] = '/users/1';

        /** @var ResponseInterface */
        $response = $this->bootstrap->run(Bootstrap::Micro);

        $I->assertInstanceOf(ResponseInterface::class, $response);

        $I->assertEquals(200, $response->getStatusCode());

        $I->assertEquals(
            '{"data":{"type":"Users","id":1,"attributes":{"name":"John Doe"}}}', 
            $response->getContent()
        );
    }

    /**
     * Return test config
     */
    private function _config() : array
    {
        return [
            'locale' => 'en_GB',
            'handlerPath' => TEST_STUB_DIR . 'Config' . 
                DIRECTORY_SEPARATOR . 
                'Handlers.php',
            'middleware' => [
                NotFoundMiddleware::class => 'before'
            ],
            'services' => [
                'Stub\Service\EventManager'
            ],
            'timezone' => 'Europe/London'
        ];
    }
}
