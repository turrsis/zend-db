<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder\sql92\Predicate;

use Zend\Db\Sql\Builder\AbstractSqlBuilder;
use Zend\Db\Sql\Builder\Context;

class ConcatBuilder extends AbstractSqlBuilder
{
    protected $specification = [
        'implode' => ', ',
        'format' => 'CONCAT (%s)',
    ];

    /**
     * @param \Zend\Db\Sql\Predicate\ConcatPredicate $expression
     * @param Context $context
     * @return array
     */
    public function build($expression, Context $context)
    {
        return [[
            'spec' => $this->specification,
            'params' => $expression->getArguments(),
        ]];
    }
}
