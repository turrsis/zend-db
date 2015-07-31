<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Predicate;

use Zend\Db\Sql\ExpressionParameter;

class CasePredicate implements PredicateInterface
{
    protected $case = null;
    protected $conditions = [];
    protected $else = null;

    /**
     * @param mixed $case
     * @param mixed $conditions
     * @param mixed $else
     */
    public function __construct($case, $conditions = [], $else = null)
    {
        if ($case !== null) {
            $this->setCase($case);
        }
        if ($else !== null) {
            $this->setElse($else);
        }
        foreach ((array)$conditions as $condition) {
            $this->addCondition($condition);
        }
    }

    /**
     * @param mixed $case
     * @return self
     */
    public function setCase($case)
    {
        $this->case = new ExpressionParameter($case, self::TYPE_IDENTIFIER);
        return $this;
    }

    /**
     * @return ExpressionParameter
     */
    public function getCase()
    {
        return $this->case;
    }

    /**
     * @param mixed $condition
     * @return self
     */
    public function addCondition($condition)
    {
        $this->conditions[] = [
            new ExpressionParameter($condition[0]),
            new ExpressionParameter($condition[1]),
        ];
        return $this;
    }

    /**
     * @return array[]
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @param mixed $else
     * @return self
     */
    public function setElse($else)
    {
        $this->else = new ExpressionParameter($else);
        return $this;
    }

    /**
     * @return ExpressionParameter
     */
    public function getElse()
    {
        return $this->else;
    }
}
