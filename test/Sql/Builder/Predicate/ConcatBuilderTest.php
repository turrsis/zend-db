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

class ConcatBuilderTest extends AbstractTestCase
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
                'sqlObject' => $this->predicate_Concat([
                    $this->expression('expr ?', '1'),
                    $this->select('table1'),
                    'var3',
                    ['var4', ExpressionInterface::TYPE_IDENTIFIER],
                    ['var5', ExpressionInterface::TYPE_LITERAL],
                    ['var6', ExpressionInterface::TYPE_VALUE],
                ]),
                'expected'  => [
                    'sql92'      => "CONCAT (expr '1', (SELECT \"table1\".* FROM \"table1\"), 'var3', \"var4\", var5, 'var6')",
                    'postgresql' => "(expr E'1' || (SELECT \"table1\".* FROM \"table1\") || E'var3' || \"var4\" || var5 || E'var6')",
                ],
            ],
        ]);
    }
}
