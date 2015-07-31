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

class CasePredicateBuilder extends AbstractSqlBuilder
{
    protected $specificationStart = 'CASE %s ';
    protected $specificationWhen  = [
        'forEach' => 'WHEN %s THEN %s',
        'implode' => ' ',
    ];
    protected $specificationElse  = ' ELSE %s';
    protected $specificationEnd   = ' END';

    /**
     * @param \Zend\Db\Sql\Predicate\CasePredicate $expression
     * @param Context $context
     * @return array
     */
    public function build($expression, Context $context)
    {
        $this->validateSqlObject($expression, 'Zend\Db\Sql\Predicate\CasePredicate', __METHOD__);

        $spec = [
            [
                'spec'   => $this->specificationStart,
                'params' => $expression->getCase() ? $expression->getCase() : '',
            ],
            [
                'spec'   => $this->specificationWhen,
                'params' => $expression->getConditions(),
            ],
        ];

        if ($expression->getElse()) {
            $spec[] = [
                'spec'   => $this->specificationElse,
                'params' => $expression->getElse(),
            ];
        }

        $spec[] = $this->specificationEnd;

        return $spec;
    }
}
