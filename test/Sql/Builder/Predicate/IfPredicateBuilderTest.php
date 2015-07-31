<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Builder\Predicate;

use ZendTest\Db\Sql\Builder\AbstractTestCase;

class IfPredicateBuilderTest extends AbstractTestCase
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
                'sqlObject' => $this->predicate_If(
                    $this->expression('expr ?', '1'),
                    $this->select('table1'),
                    'var3'
                ),
                'expected'  => [
                    'sql92'      => "CASE WHEN expr '1' THEN (SELECT \"table1\".* FROM \"table1\") ELSE 'var3' END",
                    'mysql'      => "CASE WHEN expr '1' THEN (SELECT `table1`.* FROM `table1`) ELSE 'var3' END",
                ],
            ],
        ]);
    }
}
