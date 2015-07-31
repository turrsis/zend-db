<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Predicate;

use Zend\Db\Sql\Predicate\Concat;
use Zend\Db\Sql\ExpressionParameter;

class ConcatTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Zend\Db\Sql\Predicate\Concat::__construct
     */
    public function testConstructor()
    {
        $concat = new Concat(['a1', 'a2']);
        $this->assertEquals(
            [
                new ExpressionParameter('a1'),
                new ExpressionParameter('a2'),
            ],
            $concat->getArguments()
        );
    }

    /**
     * @covers Zend\Db\Sql\Predicate\Concat::addArgument
     * @covers Zend\Db\Sql\Predicate\Concat::getArguments
     */
    public function testSettersAndIsMutable()
    {
        $concat = new Concat(['a1', 'a2']);

        $this->assertSame($concat, $concat->addArgument('a3'));
        $this->assertEquals(
            [
                new ExpressionParameter('a1'),
                new ExpressionParameter('a2'),
                new ExpressionParameter('a3'),
            ],
            $concat->getArguments()
        );
    }
}
