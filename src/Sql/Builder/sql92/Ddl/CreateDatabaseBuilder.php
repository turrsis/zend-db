<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder\sql92\Ddl;

use Zend\Db\Sql\Builder\AbstractSqlBuilder;
use Zend\Db\Sql\Builder\Context;
use Zend\Db\Sql\ExpressionParameter;
use Zend\Db\Sql\ExpressionInterface;

class CreateDatabaseBuilder extends AbstractSqlBuilder
{
    protected $specification = 'CREATE DATABASE %s';

    /**
     * @param \Zend\Db\Sql\Ddl\CreateDatabase $sqlObject
     * @param Context $context
     * @return array
     */
    public function build($sqlObject, Context $context)
    {
        $this->validateSqlObject($sqlObject, 'Zend\Db\Sql\Ddl\CreateDatabase', __METHOD__);
        return [[
            'spec'   => $this->specification,
            'params' => new ExpressionParameter($sqlObject->database, ExpressionInterface::TYPE_IDENTIFIER),
        ]];
    }
}
