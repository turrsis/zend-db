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

class IfPredicate implements PredicateInterface
{
    protected $if;
    protected $then;
    protected $else;

    /**
     * @param mixed $if
     * @param mixed $then
     * @param mixed $else
     */
    public function __construct($if, $then, $else)
    {
        $this->setIf($if);
        $this->setThen($then);
        $this->setElse($else);
    }

    /**
     * @param mixed $if
     * @return self
     */
    public function setIf($if)
    {
        $this->if = new ExpressionParameter($if);
        return $this;
    }

    /**
     * @return ExpressionParameter
     */
    public function getIf()
    {
        return $this->if;
    }

    /**
     * @param mixed $then
     * @return self
     */
    public function setThen($then)
    {
        $this->then = new ExpressionParameter($then);
        return $this;
    }

    /**
     * @return ExpressionParameter
     */
    public function getThen()
    {
        return $this->then;
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
