<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder;

use Zend\Db\Sql\TableIdentifier;
use Zend\Db\Sql\SelectableInterface;
use Zend\Db\Sql\ExpressionInterface;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Exception;

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

    protected function buildSqlString($sqlObject, Context $context)
    {
        if ($sqlObject instanceof ExpressionInterface) {
            return $this->buildExpression($sqlObject, $context);
        }
        $sqls       = [];
        $parameters = [];

        foreach ($this->specifications as $name => $specification) {
            $parameters[$name] = $this->{'build_' . $name}($sqlObject, $context, $sqls, $parameters);

            if ($specification && is_array($parameters[$name])) {
                $sqls[$name] = $this->createSqlFromSpecificationAndParameters($specification, $parameters[$name]);

                continue;
            }

            if (is_string($parameters[$name])) {
                $sqls[$name] = $parameters[$name];
            }
        }
        return rtrim(implode(' ', $sqls), "\n ,");
    }

    /**
     * @param string|array $specifications
     * @param string|array $parameters
     *
     * @return string
     *
     * @throws Exception\RuntimeException
     */
    protected function createSqlFromSpecificationAndParameters($specifications, $parameters)
    {
        if (is_string($specifications)) {
            return vsprintf($specifications, $parameters);
        }

        $parametersCount = count($parameters);

        foreach ($specifications as $specificationString => $paramSpecs) {
            if ($parametersCount == count($paramSpecs)) {
                break;
            }

            unset($specificationString, $paramSpecs);
        }

        if (!isset($specificationString)) {
            throw new Exception\RuntimeException(
                'A number of parameters was found that is not supported by this specification'
            );
        }

        $topParameters = [];
        foreach ($parameters as $position => $paramsForPosition) {
            if (isset($paramSpecs[$position]['combinedby'])) {
                $multiParamValues = [];
                foreach ($paramsForPosition as $multiParamsForPosition) {
                    $ppCount = count($multiParamsForPosition);
                    if (!isset($paramSpecs[$position][$ppCount])) {
                        throw new Exception\RuntimeException(sprintf(
                            'A number of parameters (%d) was found that is not supported by this specification', $ppCount
                        ));
                    }
                    $multiParamValues[] = vsprintf($paramSpecs[$position][$ppCount], $multiParamsForPosition);
                }
                $topParameters[] = implode($paramSpecs[$position]['combinedby'], $multiParamValues);
            } elseif ($paramSpecs[$position] !== null) {
                $ppCount = count($paramsForPosition);
                if (!isset($paramSpecs[$position][$ppCount])) {
                    throw new Exception\RuntimeException(sprintf(
                        'A number of parameters (%d) was found that is not supported by this specification', $ppCount
                    ));
                }
                $topParameters[] = vsprintf($paramSpecs[$position][$ppCount], $paramsForPosition);
            } else {
                $topParameters[] = $paramsForPosition;
            }
        }
        return vsprintf($specificationString, $topParameters);
    }

    /**
     * @param string|TableIdentifier|Select $table
     * @param Context $context
     * @return string
     */
    protected function resolveTable($table, Context $context)
    {
        $schema = null;
        if ($table instanceof TableIdentifier) {
            list($table, $schema) = $table->getTableAndSchema();
        }

        if ($table instanceof SelectableInterface) {
            $table = '(' . $this->buildSubSelect($table, $context) . ')';
        } elseif ($table) {
            $table = $context->getPlatform()->quoteIdentifier($table);
        }

        if ($schema && $table) {
            $table = $context->getPlatform()->quoteIdentifier($schema) . $context->getPlatform()->getIdentifierSeparator() . $table;
        }
        return $table;
    }

    protected function buildSubSelect(SelectableInterface $subselect, Context $context)
    {
        $context->startPrefix('subselect');

        $builder = $this->platformBuilder->getPlatformBuilder($subselect, $context->getPlatform());
        $result = $builder->buildSqlString($subselect, $context);

        $context->endPrefix();

        return $result;
    }

    private function buildExpression(ExpressionInterface $expression, Context $context)
    {
        $sql = '';

        $parts = $expression->getExpressionData();

        foreach ($parts as $part) {
            // #7407: use $expression->getExpression() to get the unescaped
            // version of the expression
            if (is_string($part) && $expression instanceof Expression) {
                $sql .= $expression->getExpression();

                continue;
            }

            // if it is a string, simply tack it onto the return sql
            // "specification" string
            if (is_string($part)) {
                $sql .= $part;

                continue;
            }

            if (! is_array($part)) {
                throw new Exception\RuntimeException(
                    'Elements returned from getExpressionData() array must be a string or array.'
                );
            }

            // build_ values and types (the middle and last position of the
            // expression data)
            $values = $part[1];
            $types = isset($part[2]) ? $part[2] : [];
            foreach ($values as $vIndex => $value) {
                if (!isset($types[$vIndex])) {
                    continue;
                }
                $type = $types[$vIndex];
                if ($value instanceof SelectableInterface) {
                    // build_ sub-select
                    $values[$vIndex] = '(' . $this->buildSubSelect($value, $context) . ')';
                } elseif ($value instanceof ExpressionInterface) {
                    // recursive call to satisfy nested expressions
                    $values[$vIndex] = $this->buildSqlString($value, $context);
                } elseif ($type == ExpressionInterface::TYPE_IDENTIFIER) {
                    $values[$vIndex] = $context->getPlatform()->quoteIdentifierInFragment($value);
                } elseif ($type == ExpressionInterface::TYPE_VALUE) {
                    // if prepareType is set, it means that this particular value must be
                    // passed back to the statement in a way it can be used as a placeholder value
                    if ($context->getParameterContainer()) {
                        $name = $context->getNestedAlias('expr');
                        $context->getParameterContainer()->offsetSet($name, $value);
                        $values[$vIndex] = $context->getDriver()->formatParameterName($name);
                        continue;
                    }

                    // if not a preparable statement, simply quote the value and move on
                    $values[$vIndex] = $context->getPlatform()->quoteValue($value);
                } elseif ($type == ExpressionInterface::TYPE_LITERAL) {
                    $values[$vIndex] = $value;
                }
            }

            // after looping the values, interpolate them into the sql string
            // (they might be placeholder names, or values)
            $sql .= vsprintf($part[0], $values);
        }

        return $sql;
    }

    /**
     * @param string|array $column
     * @param Context $context
     * @return string
     */
    protected function resolveColumnValue($column, Context $context)
    {
        $isIdentifier = false;
        $fromTable = '';
        if (is_array($column)) {
            if (isset($column['isIdentifier'])) {
                $isIdentifier = (bool) $column['isIdentifier'];
            }
            if (isset($column['fromTable']) && $column['fromTable'] !== null) {
                $fromTable = $column['fromTable'];
            }
            $column = $column['column'];
        }

        if ($column instanceof ExpressionInterface) {
            return $this->buildSqlString($column, $context);
        }
        if ($column instanceof SelectableInterface) {
            return '(' . $this->buildSubSelect($column, $context) . ')';
        }
        if ($column === null) {
            return 'NULL';
        }
        return $isIdentifier
                ? $fromTable . $context->getPlatform()->quoteIdentifierInFragment($column)
                : $context->getPlatform()->quoteValue($column);
    }
}
