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

class Substring implements PredicateInterface
{
    protected $string = null;
    protected $start = null;
    protected $len = null;

    /**
     * @param mixed $string
     * @param mixed $start
     * @param mixed $len
     */
    public function __construct($string, $start = null, $len = null)
    {
        $this->setString($string);
        if ($start !== null) {
            $this->setStart($start);
        }
        if ($len !== null) {
            $this->setLen($len);
        }
    }

    /**
     * @param mixed $string
     * @return self
     */
    public function setString($string)
    {
        $this->string = new ExpressionParameter($string);
        return $this;
    }

    /**
     * @return ExpressionParameter
     */
    public function getString()
    {
        return $this->string;
    }

    /**
     * @param mixed $string
     * @return self
     */
    public function setStart($start)
    {
        $this->start = new ExpressionParameter($start);
        return $this;
    }

    /**
     * @return ExpressionParameter
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param mixed $string
     * @return self
     */
    public function setLen($len)
    {
        $this->len = new ExpressionParameter($len);
        return $this;
    }

    /**
     * @return ExpressionParameter
     */
    public function getLen()
    {
        return $this->len;
    }
}
