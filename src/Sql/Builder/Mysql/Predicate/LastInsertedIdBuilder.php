<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder\Mysql\Predicate;

use Zend\Db\Sql\Builder\sql92\Predicate\LastInsertedIdBuilder as BaseLastInsertedIdBuilder;

class LastInsertedIdBuilder extends BaseLastInsertedIdBuilder
{
    protected $specificationWithTable = "(SELECT `AUTO_INCREMENT` - 1 FROM information_schema.`TABLES` WHERE `TABLE_SCHEMA` = (select database()) AND `TABLE_NAME` = %s)";

    protected $specificationWithoutTable = "LAST_INSERT_ID()";
}
