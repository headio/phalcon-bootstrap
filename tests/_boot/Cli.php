<?php
/**
 * This source file is subject to the MIT License.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this package.
 */

/**
 * Codeception bootstrap
 */
use Headio\Phalcon\Bootstrap\Application\Factory as AppFactory;
use Headio\Phalcon\Bootstrap\Di\Factory as DiFactory;
use Phalcon\Config\Config;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

$config = [
    'applicationPath' => dirname(__DIR__) .
        DIRECTORY_SEPARATOR . '_data' .
        DIRECTORY_SEPARATOR . '_stub' .
        DIRECTORY_SEPARATOR,
    'debug' => false,
    'dispatcher' => [
        'defaultTaskNamespace' => 'Stub\\Module\\Frontend\\Task'
    ],
    'loader' => [
        'registerNamespaces' => [
            'Stub\\Module\\Admin\\Task' => TEST_STUB_DIR . 'Module/Admin/Task',
            'Stub\\Module\\Frontend\\Task' => TEST_STUB_DIR . 'Module/Frontend/Task',
        ]
    ],
    'logPath' => dirname(__DIR__) .
        DIRECTORY_SEPARATOR . '_data' .
        DIRECTORY_SEPARATOR . '_output' .
        DIRECTORY_SEPARATOR . 'Var' .
        DIRECTORY_SEPARATOR . 'Log' .
        DIRECTORY_SEPARATOR . 'Console' .
        DIRECTORY_SEPARATOR,
    'locale' => 'en_GB',
    'middleware' => [
    ],
    'modules' => [
        'Admin' => [
            'className' => 'Stub\\Module\\Admin\\Module',
            'path' => TEST_STUB_DIR . 'Module/Admin/Module.php',
            'metadata' => [
                'taskNamespace' => 'Stub\\Module\\Admin\\Task'
            ]
        ],
        'Frontend' => [
            'className' => 'Stub\\Module\\Frontend\\Module',
            'path' => TEST_STUB_DIR . 'Module/Frontend/Module.php',
            'metadata' => [
                'taskNamespace' => 'Stub\\Module\\Frontend\\Task'
            ]
        ]
    ],
    'router' => [
        'defaultPaths' => [
            'module' => 'Frontend'
        ]
    ],
    'services' => [
        'Stub\Provider\EventManager',
        'Stub\Provider\Logger',
        'Stub\Provider\Loader',
        'Stub\Provider\Dispatcher',
        'Stub\Provider\Router',
        'Stub\Provider\ConsoleOutput',
    ],
    'timezone' => 'Europe/London'
];
$di = (new DiFactory(new Config($config)))->createDefaultCli();
$app = (new AppFactory($di))->createForCli();

return $app;
