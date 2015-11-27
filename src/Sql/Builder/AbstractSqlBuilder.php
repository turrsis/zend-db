<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder;

abstract class AbstractSqlBuilder extends AbstractBuilder
{
    protected $specifications = [];

    /**
     * @var Builder
     */
    protected $platformBuilder;

    /**
     * @param Builder $platformBuilder
     */
    public function __construct(Builder $platformBuilder)
    {
        $this->platformBuilder = $platformBuilder;
    }

    abstract public function build($sqlObject, Context $context);

    /**
     * @param string|array $column
     * @param mixed $value
     * @param Context $context
     * @return array
     */
    protected function resolveColumnValue($column, $value, Context $context)
    {
        if (is_scalar($value) || $value === null) {
            if ($context->getParameterContainer() && $column !== null) {
                $context->getParameterContainer()->offsetSet($column, $value);
                $value = $context->getDriver()->formatParameterName($column);
            } elseif ($value === null) {
                $value = 'NULL';
            } else {
                $value = $context->getPlatform()->quoteValue($value);
            }
        }

        return [
            $context->getPlatform()->quoteIdentifier($column),
            $value
        ];
    }
}
