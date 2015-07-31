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

class LastInsertedIdBuilderTest extends AbstractTestCase
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
                'sqlObject' => $this->predicate_LastInsertedId('foo'),
                'expected'  => [
                    'sql92'      => [
                        'string' => 'exception',
                        'ExpectedException' => 'Zend\Db\Sql\Exception\RuntimeException'
                    ],
                    'mysql'      => "(SELECT `AUTO_INCREMENT` - 1 FROM information_schema.`TABLES` WHERE `TABLE_SCHEMA` = (select database()) AND `TABLE_NAME` = 'foo')",
                ],
            ],
            [
                'sqlObject' => $this->predicate_LastInsertedId(),
                'expected'  => [
                    'sql92'      => [
                        'string' => 'exception',
                        'ExpectedException' => 'Zend\Db\Sql\Exception\RuntimeException'
                    ],
                    'mysql'      => "LAST_INSERT_ID()",
                ],
            ],
        ]);
    }
}
