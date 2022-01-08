<?php
/**
 * This source file is subject to the MIT License.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this package.
 */
declare(strict_types=1);

namespace Unit\Di;

use Headio\Phalcon\Bootstrap\Di\Factory;
use Headio\Phalcon\Bootstrap\Di\FactoryInterface;
use Phalcon\Annotations\Adapter\Memory;
use Phalcon\Annotations\Adapter\Apcu;
use Phalcon\Config\Config;
use Phalcon\Di\Di;
use Phalcon\Di\DiInterface;
use Phalcon\Di\FactoryDefault;
use Phalcon\Di\FactoryDefault\Cli;
use Phalcon\Encryption\Crypt;
use Phalcon\Encryption\Security;
use Mockery;
use Module\UnitTest;

class FactoryTest extends UnitTest
{
    private $mock;

    protected function _before()
    {
        $config = new Config($this->_config());
        $this->mock = Mockery::mock(
            Factory::class,
            FactoryInterface::class,
            [$config]
        )
        ->makePartial();

        $this->mock->allows()->createDefaultMvc()->andReturn(new FactoryDefault());
        $this->mock->allows()->createDefaultCli()->andReturn(new Cli());
        $this->mock->allows()->create()->with(new Di())->andReturn(new Di());
    }

    public function testFactoryCanCreateDiForCliContext(): void
    {
        $this->specify(
            'Factory creates a dependency injection container for the cli',
            function () {
                expect($this->mock->createDefaultCli())->isInstanceOf(Cli::class);
            }
        );
    }

    public function testFactoryCanCreateDiForMvcContext(): void
    {
        $this->specify(
            'Factory creates dependency injection container for an MVC context',
            function () {
                expect($this->mock->createDefaultMvc())->isInstanceOf(FactoryDefault::class);
            }
        );
    }

    public function testFactoryCanCreateDiForNamedContext(): void
    {
        $this->specify(
            'Factory creates dependency injection container for named context',
            function () {
                expect($this->mock->create(new Di()))->isInstanceOf(DiInterface::class);
            }
        );
    }

    public function testFactoryCanConfigureDiContainer(): void
    {
        $this->describe(
            'Factory can configure a dependency injection container',
            function () {
                $result = $this->mock->create(new Di());

                $this->should(
                    'have registered config as a service dependency',
                    function () use ($result) {
                        expect($result->get('config'))->isInstanceOf(Config::class);
                    }
                );

                $this->should(
                    'return config property value',
                    function () use ($result) {
                        $config = $result->get('config');
                        expect($config->get('locale'))->equals('en_GB');
                    }
                );

                $this->should(
                    'have registered crypt service dependency',
                    function () use ($result) {
                        expect($result->has('crypt'))->equals(true);
                    }
                );

                $this->should(
                    'have registered security service dependency',
                    function () use ($result) {
                        expect($result->has('security'))->equals(true);
                    }
                );

                $this->should(
                    'return instance of annotations apcu adapter in live modus',
                    function () use ($result) {
                        expect($result->get('annotations'))->isInstanceOf(Apcu::class);
                    }
                );

                $this->should(
                    'return instance of annotations memory adapter in debug modus',
                    function () {
                        $config = (new Config($this->_config()))
                            ->merge(
                                new Config(
                                    [
                                        'debug' => true
                                    ]
                                )
                            );
                        $mock = Mockery::mock(Factory::class, [$config])->makePartial();
                        $result = $mock->create(new Di());

                        expect($result->get('annotations'))->isInstanceOf(Memory::class);
                    }
                );
            }
        );
    }

    public function testFactoryIsCliAware(): void
    {
        $this->specify(
            'Factory assigns cli config during cli dependency injection container instantiation',
            function () {
                $result = $this->mock->create(new Cli())->getConfig()['cli'];

                expect($result)->true();
            }
        );
    }

    /**
     * Return test config
     */
    protected function _config(): array
    {
        return [
            'annotations' => [
                'adapter' => 'Apcu',
                'options' => [
                    'lifetime' => 3600 * 24 * 30,
                    'prefix' => 'annotations',
                ],
            ],
            'debug' => false,
            'locale' => 'en_GB',
            'security' => [
                'encryption' => [
                    'padding' => Crypt::PADDING_DEFAULT,
                    'cipher' => 'aes-256-ctr',
                    'hash' => Security::CRYPT_BLOWFISH,
                    'key' => 'AQx.+ZXCV!Q3|_[%:45T$%B&+i8s7]s9~8_4L!<@[Y/_RwIP_8vS|:+.$>/$E,Tx',
                    'workFactor' => 12,
                    'useSigning' => true
                ]
            ],
            'services' => [
                'Stub\Provider\EventManager',
                'Stub\Provider\Annotation',
                'Stub\Provider\Crypt',
                'Stub\Provider\Security'
            ],
            'timezone' => 'Europe/London'
        ];
    }
}
