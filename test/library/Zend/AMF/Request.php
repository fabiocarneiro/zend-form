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
 * @package    Zend_Amf
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @namespace
 */
namespace Zend\AMF;

/**
 * Handle the incoming AMF request by deserializing the data to php object
 * types and storing the data for Zend_Amf_Server to handle for processing.
 *
 * @todo       Currently not checking if the object needs to be Type Mapped to a server object.
 * @uses       Zend\AMF\Constants
 * @uses       Zend\AMF\Exception
 * @uses       Zend\AMF\Parser\AMF0\Deserializer
 * @uses       Zend\AMF\Parser\InputStream
 * @uses       Zend\AMF\Value\MessageBody
 * @uses       Zend\AMF\Value\MessageHeader
 * @package    Zend_Amf
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface Request
{
    /**
     * Prepare the AMF InputStream for parsing.
     *
     * @param  string $request
     * @return Zend\AMF\Request
     */
    public function initialize($request);

    /**
     * Takes the raw AMF input stream and converts it into valid PHP objects
     *
     * @param  Zend\AMF\Parser\InputStream
     * @return Zend\AMF\Request
     */
    public function readMessage(Parser\InputStream $stream);

    /**
     * Deserialize a message header from the input stream.
     *
     * A message header is structured as:
     * - NAME String
     * - MUST UNDERSTAND Boolean
     * - LENGTH Int
     * - DATA Object
     *
     * @return Zend\AMF\Value\MessageHeader
     */
    public function readHeader();

    /**
     * Deserialize a message body from the input stream
     *
     * @return Zend\AMF\Value\MessageBody
     */
    public function readBody();

    /**
     * Return an array of the body objects that were found in the amf request.
     *
     * @return array {target, response, length, content}
     */
    public function getAmfBodies();

    /**
     * Accessor to private array of message bodies.
     *
     * @param  Zend\AMF\Value\MessageBody $message
     * @return Zend\AMF\Request
     */
    public function addAmfBody(Value\MessageBody $message);

    /**
     * Return an array of headers that were found in the amf request.
     *
     * @return array {operation, mustUnderstand, length, param}
     */
    public function getAmfHeaders();

    /**
     * Return the either 0 or 3 for respect AMF version
     *
     * @return int
     */
    public function getObjectEncoding();

    /**
     * Set the object response encoding
     *
     * @param  mixed $int
     * @return Zend\AMF\Request
     */
    public function setObjectEncoding($int);
}
