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

class SubstringBuilderTest extends AbstractTestCase
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
                'sqlObject' => $this->predicate_Substring('string', 5, 10),
                'expected'  => [
                    'sql92'     => "substring('string', from '5', for '10')",
                    'mysql'     => "substring('string', from '5', for '10')",
                    'sqlserver' => "substring('string', '5', '10')",
                ],
            ],
            [
                'sqlObject' => $this->predicate_Substring('string', 5),
                'expected'  => [
                    'sql92'     => "substring('string', from '5')",
                    'mysql'     => "substring('string', from '5')",
                    'sqlserver' => "substring('string', '5')",
                ],
            ],
        ]);
    }
}
