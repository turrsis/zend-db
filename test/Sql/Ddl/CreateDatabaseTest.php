<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Ddl;

use Zend\Db\Sql\Ddl\CreateDatabase;

class CreateDatabaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers CreateDatabase::__construct
     */
    public function testConstruct()
    {
        $cd = new CreateDatabase();
        $this->assertNull($cd->database);

        $cd = new CreateDatabase('foo');
        $this->assertEquals('foo', $cd->database);
    }

    /**
     * @covers CreateDatabase::setDatabase
     */
    public function testMethodsIsMutable()
    {
        $cd = new CreateDatabase();
        $this->assertSame($cd, $cd->setDatabase('foo'));
    }

    /**
     * @covers CreateDatabase::setDatabase
     */
    public function testSetDatabase()
    {
        $cd = new CreateDatabase();
        $this->assertEquals('foo', $cd->setDatabase('foo')->database);
    }
}
