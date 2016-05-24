<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Ddl;

use Zend\Db\Sql\Ddl\DropDatabase;

class DropDatabaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers DropDatabase::__construct
     */
    public function testConstruct()
    {
        $cd = new DropDatabase();
        $this->assertNull($cd->database);

        $cd = new DropDatabase('foo');
        $this->assertEquals('foo', $cd->database);
    }

    /**
     * @covers DropDatabase::setDatabase
     * @covers DropDatabase::ifExists
     */
    public function testMethodsIsMutable()
    {
        $cd = new DropDatabase();
        $this->assertSame($cd, $cd->setDatabase('foo'));
        $this->assertSame($cd, $cd->ifExists(true));
    }

    /**
     * @covers DropDatabase::setDatabase
     */
    public function testSetDatabase()
    {
        $cd = new DropDatabase();
        $this->assertEquals('foo', $cd->setDatabase('foo')->database);
    }

    /**
     * @covers DropDatabase::ifExists
     */
    public function testIfExists()
    {
        $cd = new DropDatabase();
        $this->assertFalse($cd->ifExists);
        $this->assertTrue($cd->ifExists(true)->ifExists);
        $this->assertFalse($cd->ifExists(false)->ifExists);
    }
}
