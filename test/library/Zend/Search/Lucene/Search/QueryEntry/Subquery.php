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
 * @subpackage Search
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @uses       Zend_Search_Lucene_Search_QueryEntry
 * @uses       Zend_Search_Lucene_Search_QueryParserException
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Search
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Search_Lucene_Search_QueryEntry_Subquery extends Zend_Search_Lucene_Search_QueryEntry
{
    /**
     * Query
     *
     * @var Zend_Search_Lucene_Search_Query
     */
    private $_query;

    /**
     * Object constractor
     *
     * @param Zend_Search_Lucene_Search_Query $query
     */
    public function __construct(Zend_Search_Lucene_Search_Query $query)
    {
        $this->_query = $query;
    }

    /**
     * Process modifier ('~')
     *
     * @param mixed $parameter
     * @throws Zend_Search_Lucene_Search_QueryParserException
     */
    public function processFuzzyProximityModifier($parameter = null)
    {
        throw new Zend_Search_Lucene_Search_QueryParserException('\'~\' sign must follow term or phrase');
    }


    /**
     * Transform entry to a subquery
     *
     * @param string $encoding
     * @return Zend_Search_Lucene_Search_Query
     */
    public function getQuery($encoding)
    {
        $this->_query->setBoost($this->_boost);

        return $this->_query;
    }
}
