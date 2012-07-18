<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Serializer
 */

namespace Zend\Serializer\Adapter;

use Zend\Serializer\Exception;

/**
 * @category   Zend
 * @package    Zend_Serializer
 * @subpackage Adapter
 */
class WddxOptions extends AdapterOptions
{
    /**
     * Wddx packet header comment
     *
     * @var string|null
     */
    protected $comment = null;

    /**
     * Set WDDX header comment
     *
     * @param  string $comment
     * @return WddxOptions
     */
    public function setComment($comment)
    {
        $this->comment = (string) $comment;
        return $this;
    }

    /**
     * Get WDDX header comment
     *
     * @return null|string
     */
    public function getComment()
    {
        return $this->comment;
    }
}
