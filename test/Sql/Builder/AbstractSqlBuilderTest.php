<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Builder;

use Zend\Db\Sql\Expression;
use Zend\Db\Sql\ExpressionInterface;
use Zend\Db\Sql\Predicate;
use Zend\Db\Sql\Select;
use Zend\Db\Adapter;
use ZendTest\Db\TestAsset;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Sql\Builder;

class AbstractSqlBuilderTest extends \PHPUnit_Framework_TestCase
{
    protected $builder;

    protected $adapter = null;

    public function setup()
    {
        $this->builder = $this->getMockForAbstractClass('Zend\Db\Sql\Builder\AbstractSqlBuilder', [new Builder\Builder]);

        $mockDriver = $this->getMock('Zend\Db\Adapter\Driver\DriverInterface');
        $mockDriver
            ->expects($this->any())
            ->method('formatParameterName')
            ->will($this->returnCallback(
                function ($name) { return (':' . $name); }
            ));

        $this->adapter = new Adapter\Adapter(
            $mockDriver,
            new TestAsset\TrustingSql92Platform()
        );
    }

    /**
     * @covers Zend\Db\Sql\AbstractSql::processExpression
     */
    public function testProcessExpressionWithoutParameterContainer()
    {
        $expression = new Expression(
            '? > ? AND y < ?',
            [
                ['x', Expression::TYPE_IDENTIFIER],
                5,
                10
            ]
        );
        $sqlAndParams = $this->invokeProcessExpressionMethod($expression);

        $this->assertEquals("\"x\" > '5' AND y < '10'", $sqlAndParams);
    }

    /**
     * @covers Zend\Db\Sql\AbstractSql::processExpression
     */
    public function testProcessExpressionWithParameterContainerAndParameterizationTypeNamed()
    {
        $parameterContainer = new ParameterContainer;
        $expression = new Expression('? > ? AND y < ?', [['x', Expression::TYPE_IDENTIFIER], 5, 10]);
        $sqlAndParams = $this->invokeProcessExpressionMethod($expression, $parameterContainer);

        $this->assertEquals('"x" > :expr1 AND y < :expr2', $sqlAndParams);
        $this->assertEquals(
            [
                'expr1' => 5,
                'expr2' => 10,
            ],
            $parameterContainer->getNamedArray()
        );
    }

    /**
     * @covers Zend\Db\Sql\AbstractSql::processExpression
     */
    public function testProcessExpressionWorksWithExpressionContainingStringParts()
    {
        $expression = new Predicate\Expression('x = ?', 5);

        $predicateSet = new Predicate\PredicateSet([new Predicate\PredicateSet([$expression])]);
        $sqlAndParams = $this->invokeProcessExpressionMethod($predicateSet);

        $this->assertEquals("(x = '5')", $sqlAndParams);
    }

    /**
     * @covers Zend\Db\Sql\AbstractSql::processExpression
     */
    public function testProcessExpressionWorksWithExpressionContainingSelectObject()
    {
        $select = new Select();
        $select->from('x')->where->like('bar', 'Foo%');
        $expression = new Predicate\In('x', $select);

        $predicateSet = new Predicate\PredicateSet([new Predicate\PredicateSet([$expression])]);
        $sqlAndParams = $this->invokeProcessExpressionMethod($predicateSet);

        $this->assertEquals('("x" IN (SELECT "x".* FROM "x" WHERE "bar" LIKE \'Foo%\'))', $sqlAndParams);
    }

    public function testProcessExpressionWorksWithExpressionContainingExpressionObject()
    {
        $expression = new Predicate\Operator(
            'release_date',
            '=',
            new Expression('FROM_UNIXTIME(?)', 100000000)
        );

        $sqlAndParams = $this->invokeProcessExpressionMethod($expression);
        $this->assertEquals('"release_date" = FROM_UNIXTIME(\'100000000\')', $sqlAndParams);
    }

    /**
     * @group 7407
     */
    public function testProcessExpressionWorksWithExpressionObjectWithPercentageSigns()
    {
        $expressionString = 'FROM_UNIXTIME(date, "%Y-%m")';
        $expression       = new Expression($expressionString);
        $sqlString        = $this->invokeProcessExpressionMethod($expression);

        $this->assertSame($expressionString, $sqlString);
    }

    /**
     * @param \Zend\Db\Sql\ExpressionInterface $expression
     * @param \Zend\Db\Adapter\Adapter|null $adapter
     * @return \Zend\Db\Adapter\StatementContainer
     */
    protected function invokeProcessExpressionMethod(ExpressionInterface $expression, $parameterContainer = null)
    {
        $method = new \ReflectionMethod($this->builder, 'buildExpression');
        $method->setAccessible(true);
        return $method->invoke(
            $this->builder,
            $expression,
            new Builder\Context($this->adapter, $parameterContainer)
        );
    }
}
