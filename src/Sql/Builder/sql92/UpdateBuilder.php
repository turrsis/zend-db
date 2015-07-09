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
    const SPECIFICATION_UPDATE = 'update';
    const SPECIFICATION_WHERE = 'where';

    protected $specifications = [
        self::SPECIFICATION_UPDATE => 'UPDATE %1$s SET %2$s',
        self::SPECIFICATION_WHERE => 'WHERE %1$s'
    ];

    /**
     * @param Update $sqlObject
     * @param Context $context
     * @return string
     */
    protected function build_Update(Update $sqlObject, Context $context)
    {
        $setSql = [];
        foreach ($sqlObject->set as $column => $value) {
            $prefix = $context->getPlatform()->quoteIdentifier($column) . ' = ';
            if (is_scalar($value) && $context->getParameterContainer()) {
                $setSql[] = $prefix . $context->getDriver()->formatParameterName($column);
                $context->getParameterContainer()->offsetSet($column, $value);
            } else {
                $setSql[] = $prefix . $this->resolveColumnValue(
                    $value,
                    $context
                );
            }
        }

        return sprintf(
            $this->specifications[self::SPECIFICATION_UPDATE],
            $this->resolveTable($sqlObject->table, $context),
            implode(', ', $setSql)
        );
    }

    /**
     * @param Update $sqlObject
     * @param Context $context
     * @return string|null
     */
    protected function build_Where(Update $sqlObject, Context $context)
    {
        if ($sqlObject->where->count() == 0) {
            return;
        }
        return sprintf(
            $this->specifications[self::SPECIFICATION_WHERE],
            $this->buildSqlString($sqlObject->where, $context)
        );
    }
}
