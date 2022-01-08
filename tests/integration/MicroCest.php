<?php
/**
 * This source file is subject to the MIT License.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this package.
 */
declare(strict_types=1);

namespace Integration;

use Headio\Phalcon\Bootstrap\Bootstrap;
use Headio\Phalcon\Bootstrap\BootstrapInterface;
use Headio\Phalcon\Bootstrap\Di\Factory as DiFactory;
use Phalcon\Config\Config;
use Phalcon\Http\ResponseInterface;
use Stub\Middleware\NotFoundMiddleware;
use IntegrationTester;

class MicroCest
{
    private ?BootstrapInterface $bootstrap = null;

    public function _before(IntegrationTester $I): void
    {
        $di = (new DiFactory(new Config($this->_config())))->createDefaultMvc();
        $this->bootstrap = Bootstrap::handle($di);
    }

    protected function _after(IntegrationTester $I): void
    {
        $this->bootstrap = null;
    }

    public function executeIndexActionOnIndexHandler(IntegrationTester $I): void
    {
        $I->wantTo('Execute request on index action in user handler.');

        $_SERVER['REQUEST_URI'] = '/';

        /** @var ResponseInterface */
        $response = $this->bootstrap->run(
            $_SERVER['REQUEST_URI'],
            Bootstrap::Micro
        );

        $I->assertInstanceOf(ResponseInterface::class, $response);

        $I->assertEquals(200, $response->getStatusCode());

        $I->assertEquals('Hello world', $response->getContent());
    }

    public function executeIndexActionOnUserHandler(IntegrationTester $I): void
    {
        $I->wantTo('Execute request on index action in user handler.');

        $_SERVER['REQUEST_URI'] = '/users';

        /** @var ResponseInterface */
        $response = $this->bootstrap->run(
            $_SERVER['REQUEST_URI'],
            Bootstrap::Micro
        );

        $I->assertInstanceOf(ResponseInterface::class, $response);

        $I->assertEquals(200, $response->getStatusCode());

        $I->assertEquals('Stub\Module\Admin\Controller\User::indexAction', $response->getContent());
    }

    public function executeReadActionOnUserHandler(IntegrationTester $I): void
    {
        $I->wantTo('Execute request on read action in user handler.');

        $_SERVER['REQUEST_URI'] = '/users/1';

        /** @var ResponseInterface */
        $response = $this->bootstrap->run(
            $_SERVER['REQUEST_URI'],
            Bootstrap::Micro
        );

        $I->assertInstanceOf(ResponseInterface::class, $response);

        $I->assertEquals(200, $response->getStatusCode());

        $I->assertEquals(
            '{"data":{"type":"Users","id":1,"attributes":{"name":"John Doe"}}}',
            $response->getContent()
        );
    }

    private function _config(): array
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
                'Stub\Provider\EventManager'
            ],
            'timezone' => 'Europe/London'
        ];
    }
}
