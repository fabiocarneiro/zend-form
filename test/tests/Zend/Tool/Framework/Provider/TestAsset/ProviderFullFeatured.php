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
 * @package    Zend_Tool
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @namespace
 */
namespace ZendTest\Tool\Framework\Provider\TestAsset;

/**
 * @category   Zend
 * @package    Zend_Tool
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class ProviderFullFeatured extends \Zend\Tool\Framework\Provider\AbstractProvider
{

    protected $_specialties = array('Hi', 'BloodyMurder', 'ForYourTeam');

    public function getName()
    {
        return 'FooBarBaz';
    }

    public function say($what)
    {

    }

    public function scream($what = 'HELLO')
    {

    }

    public function sayHi()
    {

    }

    public function screamBloodyMurder()
    {

    }

    public function screamForYourTeam()
    {

    }

    protected function _iAmNotCallable()
    {

    }

    public function _testReturnInternals()
    {
        return array($this->_registry->getRequest(), $this->_registry->getResponse());
    }

}

