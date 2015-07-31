<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Builder\Predicate;

use Zend\Db\Sql\ExpressionInterface;
use ZendTest\Db\Sql\Builder\AbstractTestCase;

class CasePredicateBuilderTest extends AbstractTestCase
{
    /**
     * @param type $data
     * @dataProvider dataProvider
     */
    public function test($sqlObject, $platform, $expected)
    {
        $this->assertBuilder($sqlObject, $platform, $expected);
    }

    public function dataProvider()
    {
        return $this->prepareDataProvider([
            [
                'sqlObject' => $this->predicate_Case(
                                    'var1',
                                    [
                                        [$this->expression('expr ?', '1'), $this->select('table1')],
                                        ['var2', 'var2then'],
                                        [['var3', ExpressionInterface::TYPE_IDENTIFIER], ['var3then', ExpressionInterface::TYPE_VALUE]],
                                        [['var4', ExpressionInterface::TYPE_LITERAL],    ['var4then', ExpressionInterface::TYPE_IDENTIFIER]],
                                        [['var5', ExpressionInterface::TYPE_VALUE],      ['var5then', ExpressionInterface::TYPE_LITERAL]],
                                    ],
                                    $this->expression('expr ?', '2')
                                ),
                'expected'  => [
                    'sql92' => "CASE \"var1\" WHEN expr '1' THEN (SELECT \"table1\".* FROM \"table1\") WHEN 'var2' THEN 'var2then' WHEN \"var3\" THEN 'var3then' WHEN var4 THEN \"var4then\" WHEN 'var5' THEN var5then ELSE expr '2' END",
                    'mysql' => "CASE `var1` WHEN expr '1' THEN (SELECT `table1`.* FROM `table1`) WHEN 'var2' THEN 'var2then' WHEN `var3` THEN 'var3then' WHEN var4 THEN `var4then` WHEN 'var5' THEN var5then ELSE expr '2' END",
                ],
            ],
            [
                'sqlObject' => $this->predicate_Case(
                                    null,
                                    [
                                        ['var1', 'var1then'],
                                    ]
                                ),
                'expected'  => [
                    'sql92' => "CASE  WHEN 'var1' THEN 'var1then' END",
                    'mysql' => "CASE  WHEN 'var1' THEN 'var1then' END",
                ],
            ],
        ]);
    }
}
