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
 * @package    Zend_Search_Lucene
 * @subpackage Analysis
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @namespace
 */
namespace Zend\Search\Lucene\Analysis\TokenFilter;
use Zend\Search\Lucene\Analysis;

/**
 * Lower case Token filter.
 *
 * @uses       \Zend\Search\Lucene\Analysis\Token
 * @uses       \Zend\Search\Lucene\Analysis\TokenFilter\TokenFilterInterface
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Analysis
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class LowerCase implements TokenFilterInterface
{
    /**
     * Normalize Token or remove it (if null is returned)
     *
     * @param \Zend\Search\Lucene\Analysis\Token $srcToken
     * @return \Zend\Search\Lucene\Analysis\Token
     */
    public function normalize(Analysis\Token $srcToken)
    {
        $newToken = new Analysis\Token(strtolower( $srcToken->getTermText() ),
                                       $srcToken->getStartOffset(),
                                       $srcToken->getEndOffset());

        $newToken->setPositionIncrement($srcToken->getPositionIncrement());

        return $newToken;
    }
}

