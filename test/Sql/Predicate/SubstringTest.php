<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Predicate;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Db\Sql\Predicate\Substring;

class SubstringTest extends TestCase
{
    /**
     * @covers Zend\Db\Sql\Predicate\Substring::__construct
     */
    public function testConstructor()
    {
        $substring = new Substring('string', 'start', 'len');
        $this->assertEquals('string', $substring->getString()->getValue());
        $this->assertEquals('start',  $substring->getStart()->getValue());
        $this->assertEquals('len',    $substring->getLen()->getValue());

        $substring = new Substring('string', null, null);
        $this->assertEquals('string', $substring->getString()->getValue());
        $this->assertNull($substring->getStart());
        $this->assertNull($substring->getLen());
    }

    /**
     * @covers Zend\Db\Sql\Predicate\Substring::setString
     * @covers Zend\Db\Sql\Predicate\Substring::getString
     * @covers Zend\Db\Sql\Predicate\Substring::setStart
     * @covers Zend\Db\Sql\Predicate\Substring::getStart
     * @covers Zend\Db\Sql\Predicate\Substring::setLen
     * @covers Zend\Db\Sql\Predicate\Substring::getLen
     */
    public function testSettersAndIsMutable()
    {
        $substring = new Substring('string');

        $this->assertSame($substring, $substring->setString('foo'));
        $this->assertSame($substring, $substring->setStart('bar'));
        $this->assertSame($substring, $substring->setLen('baz'));

        $this->assertEquals('foo', $substring->getString()->getValue());
        $this->assertEquals('bar', $substring->getStart()->getValue());
        $this->assertEquals('baz', $substring->getLen()->getValue());

        $this->assertInstanceOf('Zend\Db\Sql\ExpressionParameter', $substring->getString());
        $this->assertInstanceOf('Zend\Db\Sql\ExpressionParameter', $substring->getStart());
        $this->assertInstanceOf('Zend\Db\Sql\ExpressionParameter', $substring->getLen());
    }
}
