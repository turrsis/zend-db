<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Predicate;

use Zend\Db\Sql\Predicate\IfPredicate;

class IfPredicateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Zend\Db\Sql\Predicate\IfPredicate::__construct
     */
    public function testConstructor()
    {
        $substring = new IfPredicate('if', 'then', 'else');
        $this->assertEquals('if', $substring->getIf()->getValue());
        $this->assertEquals('then',  $substring->getThen()->getValue());
        $this->assertEquals('else',    $substring->getElse()->getValue());
    }

    /**
     * @covers Zend\Db\Sql\Predicate\IfPredicate::setIf
     * @covers Zend\Db\Sql\Predicate\IfPredicate::getIf
     * @covers Zend\Db\Sql\Predicate\IfPredicate::setThen
     * @covers Zend\Db\Sql\Predicate\IfPredicate::getThen
     * @covers Zend\Db\Sql\Predicate\IfPredicate::setElse
     * @covers Zend\Db\Sql\Predicate\IfPredicate::getElse
     */
    public function testSettersAndIsMutable()
    {
        $substring = new IfPredicate('if', 'then', 'else');

        $this->assertSame($substring, $substring->setIf('foo'));
        $this->assertSame($substring, $substring->setThen('bar'));
        $this->assertSame($substring, $substring->setElse('baz'));

        $this->assertEquals('foo', $substring->getIf()->getValue());
        $this->assertEquals('bar', $substring->getThen()->getValue());
        $this->assertEquals('baz', $substring->getElse()->getValue());

        $this->assertInstanceOf('Zend\Db\Sql\ExpressionParameter', $substring->getIf());
        $this->assertInstanceOf('Zend\Db\Sql\ExpressionParameter', $substring->getThen());
        $this->assertInstanceOf('Zend\Db\Sql\ExpressionParameter', $substring->getElse());
    }
}
