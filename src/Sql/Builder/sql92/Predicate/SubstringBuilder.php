<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Builder\sql92\Predicate;

use Zend\Db\Sql\Builder\AbstractSqlBuilder;
use Zend\Db\Sql\Builder\Context;

class SubstringBuilder extends AbstractSqlBuilder
{
    protected $specification = [
        'byArgNumber' => [
            2 => ['byCount' => [
                1 => 'from %s',
                2 => 'from %s, for %s',
            ]],
        ],
        'format' => 'substring(%s, %s)',
    ];

    /**
     * @param \Zend\Db\Sql\Predicate\Substring $expression
     * @param Context $context
     * @return array
     */
    public function build($expression, Context $context)
    {
        $this->validateSqlObject($expression, 'Zend\Db\Sql\Predicate\Substring', __METHOD__);

        return [[
            'spec' => $this->specification,
            'params' => [
                            $expression->getString(),
                            $expression->getLen() === null
                                ? [$expression->getStart()]
                                : [$expression->getStart(), $expression->getLen()]
                        ],
        ]];
    }
}
