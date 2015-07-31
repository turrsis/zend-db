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
use Zend\Db\Sql\Exception;
use Zend\Db\Sql\ExpressionParameter;
use Zend\Db\Sql\ExpressionInterface;

class LastInsertedIdBuilder extends AbstractSqlBuilder
{
    protected $specificationWithTable = false;

    protected $specificationWithoutTable = false;

    /**
     * @param \Zend\Db\Sql\Predicate\LastInsertedId $expression
     * @param Context $context
     * @return array
     */
    public function build($expression, Context $context)
    {
        $this->validateSqlObject($expression, 'Zend\Db\Sql\Predicate\LastInsertedId', __METHOD__);

        if ($expression->getTable()) {
            if ($this->specificationWithTable === false) {
                throw new Exception\RuntimeException('not supported');
            }
            return [[
                'spec' => $this->specificationWithTable,
                'params' => new ExpressionParameter($expression->getTable()->getTable(), ExpressionInterface::TYPE_VALUE),
            ]];
        }
        if ($this->specificationWithoutTable === false) {
            throw new Exception\RuntimeException('not supported');
        }
        return [$this->specificationWithoutTable];
    }
}
