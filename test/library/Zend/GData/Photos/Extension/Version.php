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
 * @package    Zend_Gdata
 * @subpackage Photos
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @namespace
 */
namespace Zend\GData\Photos\Extension;

/**
 * Represents the gphoto:version element used by the API.
 * This number is used for optimistic concurrency, and does not
 * increase linearly.
 *
 * @uses       \Zend\GData\Extension
 * @uses       \Zend\GData\Photos
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Photos
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Version extends \Zend\GData\Extension
{

    protected $_rootNamespace = 'gphoto';
    protected $_rootElement = 'version';

    /**
     * Constructs a new Zend_Gdata_Photos_Extension_Version object.
     *
     * @param string $text (optional) The value to represent.
     */
    public function __construct($text = null)
    {
        $this->registerAllNamespaces(\Zend\GData\Photos::$namespaces);
        parent::__construct();
        $this->setText($text);
    }

}
