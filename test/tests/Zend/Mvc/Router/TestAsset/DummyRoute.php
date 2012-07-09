<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Mvc_Router
 * @subpackage UnitTests
 */

namespace ZendTest\Mvc\Router\TestAsset;

use Zend\Mvc\Router\RouteInterface;
use Zend\Mvc\Router\RouteMatch;
use Zend\Stdlib\RequestInterface;

/**
 * Dummy route.
 *
 * @category   Zend
 * @package    Zend_Mvc_Router
 * @subpackage UnitTests
 */
class DummyRoute implements RouteInterface
{
    /**
     * match(): defined by RouteInterface interface.
     *
     * @see    Route::match()
     * @param  RequestInterface $request
     * @return RouteMatch
     */
    public function match(RequestInterface $request)
    {
        return new RouteMatch(array());
    }

    /**
     * assemble(): defined by RouteInterface interface.
     *
     * @see    Route::assemble()
     * @param  array $params
     * @param  array $options
     * @return mixed
     */
    public function assemble(array $params = null, array $options = null)
    {
        return '';
    }

    /**
     * factory(): defined by RouteInterface interface
     * 
     * @param  array|\Traversable $options
     * @return DummyRoute
     */
    public static function factory($options = array())
    {
        return new static();
    }
}
