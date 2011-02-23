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
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @namespace
 */
namespace Zend\Filter;

/**
 * @uses       Zend\Filter\AbstractFilter
 * @category   Zend
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class StringTrim extends AbstractFilter
{
    /**
     * List of characters provided to the trim() function
     *
     * If this is null, then trim() is called with no specific character list,
     * and its default behavior will be invoked, trimming whitespace.
     *
     * @var string|null
     */
    protected $_charList;

    /**
     * Sets filter options
     *
     * @param  string|array|\Zend\Config\Config $options
     * @return void
     */
    public function __construct($options = null)
    {
        if ($options instanceof \Zend\Config\Config) {
            $options = $options->toArray();
        } elseif (!is_array($options)) {
            $options          = func_get_args();
            $temp['charlist'] = array_shift($options);
            $options          = $temp;
        }

        if (array_key_exists('charlist', $options)) {
            $this->setCharList($options['charlist']);
        }
    }

    /**
     * Returns the charList option
     *
     * @return string|null
     */
    public function getCharList()
    {
        return $this->_charList;
    }

    /**
     * Sets the charList option
     *
     * @param  string|null $charList
     * @return \Zend\Filter\StringTrim Provides a fluent interface
     */
    public function setCharList($charList)
    {
        $this->_charList = $charList;
        return $this;
    }

    /**
     * Defined by Zend_Filter_Interface
     *
     * Returns the string $value with characters stripped from the beginning and end
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        if (null === $this->_charList) {
            return $this->_unicodeTrim((string) $value);
        }

        return $this->_unicodeTrim((string) $value, $this->_charList);
    }

    /**
     * Unicode aware trim method
     * Fixes a PHP problem
     *
     * @param string $value
     * @param string $charlist
     * @return string
     */
    protected function _unicodeTrim($value, $charlist = '\\\\s')
    {
        $chars = preg_replace(
            array( '/[\^\-\]\\\]/S', '/\\\{4}/S', '/\//'),
            array( '\\\\\\0', '\\', '\/' ),
            $charlist
        );

        $pattern = '^[' . $chars . ']*|[' . $chars . ']*$';
        return preg_replace("/$pattern/sSD", '', $value);
    }
}
