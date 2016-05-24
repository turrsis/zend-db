<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Ddl;

use Zend\Db\Sql\AbstractSqlObject;
use Zend\Db\Sql\Exception\InvalidArgumentException;

/**
 * @property string $database
 * @property bool $ifExists
 */
class DropDatabase extends AbstractSqlObject
{
    /**
     * @var string
     */
    protected $database;

    /**
     * @var bool
     */
    protected $ifExists = false;

    protected $__getProperties = [
        'database',
        'ifExists',
    ];

    /**
     * @param null|string $database
     */
    public function __construct($database = null, $ifExists = false)
    {
        parent::__construct();
        if ($database !== null) {
            $this->setDatabase($database);
        }
        if ($ifExists != $this->ifExists) {
            $this->ifExists = $ifExists;
        }
    }

    /**
     * @param string $database
     * @return self
     * @throws InvalidArgumentException
     */
    public function setDatabase($database)
    {
        if (!is_string($database)) {
            throw new InvalidArgumentException(sprintf(
                '$database must be a string, "%s" given',
                is_object($database) ? get_class($database) : gettype($database)
            ));
        }
        $this->database = $database;
        return $this;
    }

    /**
     * @param bool $ifExists
     * @return self
     */
    public function ifExists($ifExists = false)
    {
        $this->ifExists = (bool)$ifExists;
        return $this;
    }
}
