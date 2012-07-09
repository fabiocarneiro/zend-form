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
 * @package    Zend_Server
 * @subpackage Zend_Server_Reflection
 */

namespace Zend\Server\Reflection;

/**
 * Method/Function prototypes
 *
 * Contains accessors for the return value and all method arguments.
 *
 * @category   Zend
 * @package    Zend_Server
 * @subpackage Zend_Server_Reflection
 */
class Prototype
{
    /**
     * Constructor
     *
     * @param Zend\Server\Reflection\ReflectionReturnValue $return
     * @param array $params
     * @return void
     */
    public function __construct(ReflectionReturnValue $return, $params = null)
    {
        $this->_return = $return;

        if (!is_array($params) && (null !== $params)) {
            throw new Exception\InvalidArgumentException('Invalid parameters');
        }

        if (is_array($params)) {
            foreach ($params as $param) {
                if (!$param instanceof ReflectionParameter) {
                    throw new Exception\InvalidArgumentException('One or more params are invalid');
                }
            }
        }

        $this->_params = $params;
    }

    /**
     * Retrieve return type
     *
     * @return string
     */
    public function getReturnType()
    {
        return $this->_return->getType();
    }

    /**
     * Retrieve the return value object
     *
     * @access public
     * @return Zend\Server\Reflection\ReflectionReturnValue
     */
    public function getReturnValue()
    {
        return $this->_return;
    }

    /**
     * Retrieve method parameters
     *
     * @return array Array of {@link \Zend\Server\Reflection\ReflectionParameter}s
     */
    public function getParameters()
    {
        return $this->_params;
    }
}
