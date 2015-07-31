<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Predicate;

use Zend\Db\Sql\Expression as BaseExpression;
use Zend\Db\Sql\TableIdentifier;

class LastInsertedId extends BaseExpression implements PredicateInterface
{
    /**
     * @var TableIdentifier
     */
    protected $table       = null;

    /**
     * @param null|string|array|TableIdentifier $table
     */
    public function __construct($table = null)
    {
        if ($table !== null) {
            $this->setTable($table);
        }
    }

    /**
     * @param string|array|TableIdentifier $table
     * @return self
     */
    public function setTable($table)
    {
        $this->table = TableIdentifier::factory($table);
        return $this;
    }

    /**
     * @return TableIdentifier
     */
    public function getTable()
    {
        return $this->table;
    }
}
