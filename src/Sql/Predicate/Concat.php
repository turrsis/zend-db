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

class Concat implements PredicateInterface
{
    protected $arguments = [];

    public function __construct(array $arguments)
    {
        foreach ($arguments as $argument) {
            $this->addArgument($argument);
        }
    }

    /**
     * @param mixed $argument
     * @return self
     */
    public function addArgument($argument)
    {
        $this->arguments[] = new ExpressionParameter($argument, self::TYPE_VALUE);
        return $this;
    }

    /**
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }
}
