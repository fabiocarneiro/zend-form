<?php
namespace Zend\Db\Sql;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2012-03-01 at 23:39:00.
 */
class InsertTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Insert
     */
    protected $insert;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->insert = new Insert;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers Zend\Db\Sql\Insert::into
     */
    public function testInto()
    {
        $this->insert->into('table', 'schema');
        $this->assertEquals('table', $this->readAttribute($this->insert, 'table'));
        $this->assertEquals('schema', $this->readAttribute($this->insert, 'databaseOrSchema'));
    }

    /**
     * @covers Zend\Db\Sql\Insert::columns
     */
    public function testColumns()
    {
        $this->insert->columns(array('foo', 'bar'));
        $this->assertEquals(array('foo', 'bar'), $this->readAttribute($this->insert, 'columns'));
    }

    /**
     * @covers Zend\Db\Sql\Insert::values
     */
    public function testValues()
    {
        $this->insert->values(array('foo' => 'bar'));
        $this->assertEquals(array('foo'), $this->readAttribute($this->insert, 'columns'));
        $this->assertEquals(array('bar'), $this->readAttribute($this->insert, 'values'));
    }


    /**
     * @covers Zend\Db\Sql\Insert::prepareStatement
     */
    public function testPrepareStatement()
    {
        $mockDriver = $this->getMock('Zend\Db\Adapter\Driver\DriverInterface');
        $mockDriver->expects($this->any())->method('getPrepareType')->will($this->returnValue('positional'));
        $mockDriver->expects($this->any())->method('formatParameterName')->will($this->returnValue('?'));
        $mockAdapter = $this->getMock('Zend\Db\Adapter\Adapter', null, array($mockDriver));

        $mockStatement = $this->getMock('Zend\Db\Adapter\Driver\StatementInterface');
        $pContainer = new \Zend\Db\Adapter\ParameterContainer(array());
        $mockStatement->expects($this->any())->method('getParameterContainer')->will($this->returnValue($pContainer));
        $mockStatement->expects($this->at(1))
            ->method('setSql')
            ->with($this->equalTo('INSERT INTO "foo" ("bar") VALUES (?)'));

        $this->insert->into('foo')
            ->values(array('bar' => 'baz'));

        $this->insert->prepareStatement($mockAdapter, $mockStatement);
    }

    /**
     * @covers Zend\Db\Sql\Insert::getSqlString
     * @todo   Implement testGetSqlString().
     */
    public function testGetSqlString()
    {
        $this->insert->into('foo')
            ->values(array('bar' => 'baz'));

        $this->assertEquals('INSERT INTO "foo" ("bar") VALUES (\'baz\')', $this->insert->getSqlString());
    }

    /**
     * @covers Zend\Db\Sql\Insert::__set
     */
    public function test__set()
    {
        $this->insert->foo = 'bar';
        $this->assertEquals(array('foo'), $this->readAttribute($this->insert, 'columns'));
        $this->assertEquals(array('bar'), $this->readAttribute($this->insert, 'values'));
    }

    /**
     * @covers Zend\Db\Sql\Insert::__unset
     */
    public function test__unset()
    {
        $this->insert->foo = 'bar';
        $this->assertEquals(array('foo'), $this->readAttribute($this->insert, 'columns'));
        $this->assertEquals(array('bar'), $this->readAttribute($this->insert, 'values'));
        unset($this->insert->foo);
        $this->assertEquals(array(), $this->readAttribute($this->insert, 'columns'));
        $this->assertEquals(array(), $this->readAttribute($this->insert, 'values'));
    }

    /**
     * @covers Zend\Db\Sql\Insert::__isset
     */
    public function test__isset()
    {
        $this->insert->foo = 'bar';
        $this->assertTrue(isset($this->insert->foo));
    }

    /**
     * @covers Zend\Db\Sql\Insert::__get
     * @todo   Implement test__get().
     */
    public function test__get()
    {
        $this->insert->foo = 'bar';
        $this->assertEquals('bar', $this->insert->foo);
    }
}
