<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Test
 */

namespace Zend\Test\PHPUnit\Db\DataSet;

use Zend\Db\Select;

/**
 * Uses several query strings or Zend_Db_Select objects to form a dataset of tables for assertion with other datasets.
 *
 * @category   Zend
 * @package    Zend_Test
 * @subpackage PHPUnit
 */
class QueryDataSet extends \PHPUnit_Extensions_Database_DataSet_QueryDataSet
{
    /**
     * Creates a new dataset using the given database connection.
     *
     * @param PHPUnit_Extensions_Database_DB_IDatabaseConnection $databaseConnection
     */
    public function __construct(\PHPUnit_Extensions_Database_DB_IDatabaseConnection $databaseConnection)
    {
        if( !($databaseConnection instanceof \Zend\Test\PHPUnit\Db\Connection) ) {
            throw new \Zend\Test\PHPUnit\Db\Exception\InvalidArgumentException(
            	"Zend\Test\PHPUnit\Db\DataSet\QueryDataSet only works with Zend\Test\PHPUnit\Db\Connection connections-"
            );
        }
        $this->databaseConnection = $databaseConnection;
    }

    /**
     * Add a Table dataset representation by specifiying an arbitrary select query.
     *
     * By default a select * will be done on the given tablename.
     *
     * @param string                $tableName
     * @param string|\Zend\Db\Select $query
     */
    public function addTable($tableName, $query = \NULL)
    {
        if ($query === NULL) {
            $query = $this->databaseConnection->getConnection()->select();
            $query->from($tableName, Select::SQL_WILDCARD);
        }

        if($query instanceof Select) {
            $query = $query->__toString();
        }

        $this->tables[$tableName] = new QueryTable($tableName, $query, $this->databaseConnection);
    }
}
