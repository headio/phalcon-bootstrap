<?php
/**
 * This source file is subject to the MIT License.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this package.
 */
declare(strict_types=1);

namespace Integration;

use Headio\Phalcon\Bootstrap\BootstrapInterface;
use Phalcon\Http\ResponseInterface;
use IntegrationTester;

class MvcCest
{
    private $bootstrap;

    public function _before(IntegrationTester $I)
    {
        $I->wantTo('Bootstrap mvc application using factory');

        $bootstrap = $I->bootMvc($this->_config());

        $I->assertInstanceOf(BootstrapInterface::class, $bootstrap);

        $this->bootstrap = $bootstrap;
    }

    protected function _after(IntegrationTester $I)
    {
        $this->bootstrap = null;
    }

    public function executeRequestOnDefaultHandler(IntegrationTester $I)
    {
        $I->wantTo('Execute request on default handler');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';

        /** @var ResponseInterface|bool */
        $response = $this->bootstrap->run();

        $I->assertEquals('Hello world', $response->getContent());
    }

    public function executeRequestOnDefaultHandlerInDefaultModule(IntegrationTester $I)
    {
        $I->wantTo('Execute named request on default handler');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/index/contact';

        /** @var ResponseInterface|bool */
        $response = $this->bootstrap->run();

        $I->assertEquals('Hello contact', $response->getContent());
    }

    public function executeUnknownRoute(IntegrationTester $I)
    {
        $I->wantTo('Execute request with unknown route');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/foo/bar';

        /** @var ResponseInterface|bool */
        $response = $this->bootstrap->run();

        $I->assertEquals('Hello world', $response->getContent());
    }

    public function executeRequestOnAdminHandlerInKnownModule(IntegrationTester $I)
    {
        $I->wantTo('Execute named request on admin handler in admin module');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/admin';

        /** @var ResponseInterface|bool */
        $response = $this->bootstrap->run();

        $I->assertEquals('Hello admin world', $response->getContent());
    }

    /**
     * Return test config
     */
    private function _config(): array
    {
        return [
            'annotations' => [
                'adapter' => 'Files',
                'options' => [
                    'annotationsDir' => TEST_OUTPUT_DIR . 'Cache/Annotation/',
                ],
            ],
            'cli' => false,
            'debug' => true,
            'dispatcher' => [
                'defaultModule' => 'Frontend',
                'defaultAction' => 'index',
                'defaultController' => 'Index',
                'defaultControllerNamespace' => 'Stub\\Module\\Frontend\\Controller'
            ],
            'locale' => 'en_GB',
            'logPath' => dirname(__DIR__) .
                DIRECTORY_SEPARATOR . '_data' .
                DIRECTORY_SEPARATOR . '_output' .
                DIRECTORY_SEPARATOR . 'Var' .
                DIRECTORY_SEPARATOR . 'Log' .
                DIRECTORY_SEPARATOR . 'Web' .
                DIRECTORY_SEPARATOR,
            'modules' => [
                'Admin' => [
                    'className' => 'Stub\\Module\\Admin\\Module',
                    'path' => TEST_STUB_DIR . 'Module/Admin/Module.php',
                    'metadata' => [
                        'controllerNamespace' => 'Stub\\Module\\Admin\\Controller'
                    ]
                ],
                'Frontend' => [
                    'className' => 'Stub\\Module\\Frontend\\Module',
                    'path' => TEST_STUB_DIR . 'Module/Frontend/Module.php',
                    'metadata' => [
                        'controllerNamespace' => 'Stub\\Module\\Frontend\\Controller'
                    ]
                ]
            ],
            'routes' => [
                'Frontend' => [
                    'Stub\Module\Frontend\Controller\Index' => '/index',
                ],
                'Admin' => [
                    'Stub\Module\Admin\Controller\Admin' => '/admin',
                ],
            ],
            'services' => [
                'Stub\Service\EventManager',
                'Stub\Service\Annotation',
                'Stub\Service\Dispatcher',
                'Stub\Service\Router',
                'Stub\Service\View'
            ],
            'timezone' => 'Europe/Berlin',
            'view' => [
                'defaultPath' => TEST_OUTPUT_DIR . 'Module/Frontend/View/',
                'compiledPath' => TEST_OUTPUT_DIR . 'Cache/Volt/',
                'compiledSeparator' => '_',
            ]
        ];
    }
}
