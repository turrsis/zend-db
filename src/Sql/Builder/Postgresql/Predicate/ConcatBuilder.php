<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder\Postgresql\Predicate;

use Zend\Db\Sql\Builder\sql92\Predicate\ConcatBuilder as BaseConcatBuilder;

class ConcatBuilder extends BaseConcatBuilder
{
    protected $specification = [
        'implode' => ' || ',
        'format' => '(%s)',
    ];
}
