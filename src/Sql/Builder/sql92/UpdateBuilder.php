<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder\sql92;

use Zend\Db\Sql\Update;
use Zend\Db\Sql\Builder\AbstractSqlBuilder;
use Zend\Db\Sql\Builder\Context;

class UpdateBuilder extends AbstractSqlBuilder
{
    protected $updateSpecification = [
        'byArgNumber' => [
            2 => [
                'forEach' => '%1$s = %2$s',
                'implode' => ', ',
            ],
        ],
        'format' => 'UPDATE %1$s SET %2$s',
    ];
    protected $whereSpecification = 'WHERE %1$s';

    /**
     * @param Update $sqlObject
     * @param Context $context
     * @return array
     */
    public function build($sqlObject, Context $context)
    {
        $this->validateSqlObject($sqlObject, 'Zend\Db\Sql\Update', __METHOD__);
        return [
            $this->build_Update($sqlObject, $context),
            $this->build_Where($sqlObject, $context),
        ];
    }

    /**
     * @param Update $sqlObject
     * @param Context $context
     * @return array
     */
    protected function build_Update(Update $sqlObject, Context $context)
    {
        $setSql = [];
        foreach ($sqlObject->set as $column => $value) {
            if (is_scalar($value) && $context->getParameterContainer()) {
                $context->getParameterContainer()->offsetSet($column, $value);
                $value = $context->getDriver()->formatParameterName($column);
            } else {
                $value = $this->resolveColumnValue($value, $context);
            }
            $setSql[] = [
                $context->getPlatform()->quoteIdentifier($column),
                $value
            ];
        }
        return [
            'spec' => $this->updateSpecification,
            'params' => [
                $this->nornalizeTable($sqlObject->table, $context)['name'],
                $setSql
            ],
        ];
    }

    /**
     * @param Update $sqlObject
     * @param Context $context
     * @return array|null
     */
    protected function build_Where(Update $sqlObject, Context $context)
    {
        if ($sqlObject->where->count() == 0) {
            return;
        }
        return [
            'spec' => $this->whereSpecification,
            'params' => $sqlObject->where
        ];
    }
}
