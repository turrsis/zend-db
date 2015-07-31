<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder\SqlServer\Predicate;

use Zend\Db\Sql\Builder\sql92\Predicate\SubstringBuilder as BaseSubstringBuilder;

class SubstringBuilder extends BaseSubstringBuilder
{
    protected $specification = [
        'byArgNumber' => [
            2 => ['byCount' => [
                1 => '%s',
                2 => '%s, %s',
            ]],
        ],
        'format' => 'substring(%s, %s)',
    ];
}
