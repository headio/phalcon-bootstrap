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

namespace Module;

use Codeception\Specify;
use Codeception\Util\Debug;
use Codeception\Test\Unit;
use Phalcon\Di;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use UnitTester;

class UnitTest extends Unit
{
    use Specify;

    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * {@inheritdoc}
     */
    protected function _before()
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function _after()
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function debug($mixed)
    {
        return Debug::debug($mixed);
    }

    /**
     * Get inaccessible class property
     */
    protected function getClassProperty(string $class, string $name) : ?ReflectionProperty
    {
        $rc = new ReflectionClass($class);

        while (!$rc->hasProperty($name)) {
            $rc = $rc->getParentClass();
        }

        if ($rc->hasProperty($name)) {
            $prop = $rc->getProperty($name);
            $prop->setAccessible(true);
            return $prop;
        }

        return null;
    }

    /**
     * Get inaccessible class method
     *
     * @param object
     * @param string
     */
    protected function getClassMethod($class, string $method) : ?ReflectionMethod
    {
        $rc = new ReflectionClass($class);

        while (!$rc->hasMethod($method)) {
            $rc = $rc->getParentClass();
        }

        if ($rc->hasMethod($method)) {
            $method = $rc->getMethod($method);
            $method->setAccessible(true);
            return $method;
        }

        return null;
    }
}
