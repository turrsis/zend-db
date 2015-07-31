<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Predicate;

use Zend\Db\Sql\Predicate\CasePredicate;
use Zend\Db\Sql\ExpressionParameter;

class CasePredicateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Zend\Db\Sql\Predicate\CasePredicate::__construct
     */
    public function testConstructor()
    {
        $case = new CasePredicate('foo', [['bar', 'baz']], 'bat');
        $this->assertEquals('foo', $case->getCase()->getValue());
        $this->assertEquals(
            [
                [new ExpressionParameter('bar'), new ExpressionParameter('baz')],
            ],
            $case->getConditions()
        );
        $this->assertEquals('bat', $case->getElse()->getValue());
    }

    /**
     * @covers Zend\Db\Sql\Predicate\CasePredicate::setCase
     * @covers Zend\Db\Sql\Predicate\CasePredicate::getCase
     * @covers Zend\Db\Sql\Predicate\CasePredicate::addCondition
     * @covers Zend\Db\Sql\Predicate\CasePredicate::getConditions
     * @covers Zend\Db\Sql\Predicate\CasePredicate::setElse
     * @covers Zend\Db\Sql\Predicate\CasePredicate::getElse
     */
    public function testSettersAndIsMutable()
    {
        $case = new CasePredicate('0', [['1', '2']], '3');

        $this->assertSame($case, $case->setCase('foo'));
        $this->assertSame($case, $case->addCondition(['bar', 'baz']));
        $this->assertSame($case, $case->setElse('bat'));

        $this->assertEquals('foo', $case->getCase()->getValue());
        $this->assertEquals(
            [
                [new ExpressionParameter('1'), new ExpressionParameter('2')],
                [new ExpressionParameter('bar'), new ExpressionParameter('baz')],
            ],
            $case->getConditions()
        );
        $this->assertEquals('bat', $case->getElse()->getValue());

        $this->assertInstanceOf('Zend\Db\Sql\ExpressionParameter', $case->getCase());
        $this->assertInstanceOf('Zend\Db\Sql\ExpressionParameter', $case->getElse());
    }
}
