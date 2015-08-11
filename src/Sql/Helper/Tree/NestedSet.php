<?php
namespace Zend\Db\Sql\Helper\Tree;

use Zend\Db\Sql;
use Zend\Db\Sql\ExpressionInterface;

/**
 * http://www.getinfo.ru/article610.html
 */
class NestedSet extends AbstractTree
{
    protected $colParent        = null;
    protected $colParentAlias   = null;

    protected $colLeft          = 'trleft';
    protected $colLeftAlias     = 'trleft';
    protected $colRight         = 'trright';
    protected $colRightAlias    = 'trright';
    //Optional
    protected $colLevel         = 'trlevel';
    protected $colLevelAlias    = 'trlevel';
    //Optional 2
    protected $colPath          = null;
    protected $colPathAlias     = null;

    protected function selectMaxRight()
    {
        $select = $this->getSql()
                ->select([$this->tableAlias=> $this->table])
                ->columns([
                    $this->colRight => new Sql\Predicate\Expression("MAX(?)", [$this->colRight, ExpressionInterface::TYPE_IDENTIFIER])
                ])
                ->setPrefixColumnsWithTable(false);
        return $select;
    }


    public function selectNode($id) // selectNodes
    {
        $select = $this->getSql()
                    ->select([$this->tableAlias=> $this->table])
                    ->where($this->getWhereConditionById($id));
        return $select;
    }

    public function excludeLeafs($select, $exclude = [])
    {
        $exclude = (array)$exclude;
        foreach ($exclude as $exIndex => &$exId) {
            $exIndex = 'ex' . $exIndex;
            $select->join([$exIndex =>$this->selectNode($exId, [$this->colLeft, $this->colRight])], new Sql\Predicate\PredicateSet([
                new Sql\Predicate\Operator(
                    ["$this->tableAlias.$this->colLeft", ExpressionInterface::TYPE_IDENTIFIER],
                    '<',
                    ["$exIndex.$this->colLeft",  ExpressionInterface::TYPE_IDENTIFIER]
                ),
                new Sql\Predicate\Operator(
                    ["$this->tableAlias.$this->colRight", ExpressionInterface::TYPE_IDENTIFIER],
                    '>',
                    ["$exIndex.$this->colRight", ExpressionInterface::TYPE_IDENTIFIER]
                ),
            ], 'OR'), []);
        }
        return $select;
    }

    public function selectNodeByPath($path, $strict = true)
    {
        if ($this->colPath == null) {
            throw new \Exception('Path column is not present');
        }

        if ($strict) {
            return $this->getSql()
                            ->select($this->table)
                            ->where([$this->colPath => $path]);
        }

        $path = explode($this->pathDelimiter, trim($path, $this->pathDelimiter));
        $pTmp = '';
        foreach ($path as &$pathSegment) {
            $pathSegment = $pTmp .= $this->pathDelimiter . $pathSegment;
        }
        return $this->getSql()
                        ->select($this->table)
                        ->where(new Sql\Predicate\In($this->colPath, $path))
                        ->order([$this->colPath=>'DESC'])
                        ->limit(1);
    }

    public function selectChilds($id, $options = [])
    {
        $excludeRoot  = (isset($options['exclude_root']) && $options['exclude_root'] ? '' : '=');
        $depth        = (isset($options['depth']) ? ($options['depth'] != -1 ? $options['depth'] : null) : null);

        if (isset($options['select'])) {
            $select = $options['select'];
        } else {
            $select = $this->getSql()->select([$this->tableAlias=> $this->table]);
        }
        $select
            ->order($this->tableAlias . '.'.$this->colLeft);
        //======================================================================
        if ($id !== null) {
            $rootFROM = $this->selectNode($id, [$this->colLeft, $this->colRight, $this->colLevel]);
            $joinOn = new Sql\Predicate\PredicateSet([
                new Sql\Predicate\Operator(
                    ["$this->tableAlias.$this->colLeft", ExpressionInterface::TYPE_IDENTIFIER],
                    '>'.$excludeRoot,
                    ["root.$this->colLeft", ExpressionInterface::TYPE_IDENTIFIER]
                ),
                new Sql\Predicate\Operator(
                    ["$this->tableAlias.$this->colRight", ExpressionInterface::TYPE_IDENTIFIER],
                    '<'.$excludeRoot,
                    ["root.$this->colRight", ExpressionInterface::TYPE_IDENTIFIER]
                ),
            ]);
            $select->join(['root' =>$rootFROM], $joinOn, []);
        }

        if ($depth !== null) {
            if ($id === null) {
                $baseLevel = $this->tableAlias.'.'.$this->colLevel;
            } else {
                $baseLevel = new Sql\Predicate\Operator(
                    $this->tableAlias.'.'.$this->colLevel,
                    '-',
                    ['root.'.$this->colLevel, ExpressionInterface::TYPE_IDENTIFIER]
                );
            }
            $select->where(
                new Sql\Predicate\Operator($baseLevel, '<=', $depth)
            );
        }
        return $select;
    }

    public function selectParent($id)
    {
        if ($this->colParent) {
            throw new Exception('not implemented');
        }
        return $this->getSql()
                        ->select([$this->tableAlias=>$this->table])
                        ->columns(["$this->tableAlias.*"])
                        ->join(['child' => $this->table], new Sql\Predicate\PredicateSet([
                            new Sql\Predicate\Operator(
                                "$this->tableAlias.$this->colLeft",
                                "<=",
                                ["child.$this->colLeft", ExpressionInterface::TYPE_IDENTIFIER]
                            ),
                            new Sql\Predicate\Operator(
                                "$this->tableAlias.$this->colRight",
                                ">=",
                                ["child.$this->colRight", ExpressionInterface::TYPE_IDENTIFIER]
                            ),
                            new Sql\Predicate\Operator(
                                new Sql\Predicate\Operator("child.$this->colLevel", '-', 1),
                                "=",
                                ["$this->tableAlias.$this->colLevel", ExpressionInterface::TYPE_IDENTIFIER]
                            ),
                         ]), [])
                        ->where($this->getWhereConditionById($id, 'child'));
    }

    public function selectParentBranch($id, $includeNode = false)
    {
        $includeNode = ($includeNode ? '=' : '');

        $select = $this->getSql()->select([$this->tableAlias=>$this->table]);
        $select->join(['i' => $this->table], new Sql\Predicate\PredicateSet([
            new Sql\Predicate\Operator(
                "i.$this->colId",
                '=',
                $id
            ),
            new Sql\Predicate\Operator(
                "$this->tableAlias.$this->colLeft",
                "<$includeNode",
                ["i.$this->colLeft", ExpressionInterface::TYPE_IDENTIFIER]
            ),
            new Sql\Predicate\Operator(
                "$this->tableAlias.$this->colRight",
                ">$includeNode",
                ["i.$this->colRight", ExpressionInterface::TYPE_IDENTIFIER]
            ),
        ]));
        $select->order("$this->tableAlias.$this->colLeft");
        return $select;
    }

    public function selectPrevSibling($id)
    {
        $select = $this->getSql()
                ->select([$this->tableAlias=>$this->table])
                ->join(['item' =>$this->table], new Sql\Predicate\PredicateSet([
                    new Sql\Predicate\Operator(
                        "$this->tableAlias.$this->colRight",
                        "=",
                        new Sql\Predicate\Operator('item'.$this->colLeft, '-', 1)
                    ),
                    new Sql\Predicate\Operator(
                        ["$this->tableAlias.$this->colLevel", ExpressionInterface::TYPE_IDENTIFIER],
                        "=",
                        ["item.$this->colLevel", ExpressionInterface::TYPE_IDENTIFIER]
                    ),
                ]), [])
                ->where($this->getWhereConditionById($id, 'item'));
        return $select;
    }

    public function selectNextSibling($id)
    {
        $select = $this->getSql()
                ->select([$this->tableAlias=>$this->table])
                ->join(['item' =>$this->table], new Sql\Predicate\PredicateSet([
                    new Sql\Predicate\Operator(
                        "$this->tableAlias.$this->colLeft",
                        "=",
                        new Sql\Predicate\Operator('item'.$this->colRight, '+', 1)
                    ),
                    new Sql\Predicate\Operator(
                        ["$this->tableAlias.$this->colLevel", 'identifier'],
                        "=",
                        ["item.$this->colLevel", 'identifier']
                    ),
                ]), [])
                ->where($this->getWhereConditionById($id, 'item'));
        return $select;
    }

    public function moveNode($nodeId, $parentId, $beforeId)
    {
        $node   = $this->resolveNode($nodeId);
        if ($node[$this->colParentAlias] == $parentId) {
            return;
        }
        if ($node === null) {
            throw new \Exception('Ошибка в параметрах');
        }
        //======================================================================
        $context = (object)[
            'id'        => $node[$this->colId],
            'left'      => $node[$this->colLeftAlias],
            'right'     => $node[$this->colRightAlias],
            'level'     => $node[$this->colLevelAlias],
            'rightNear' => null,
            'skewTree'  => $node[$this->colRightAlias] - $node[$this->colLeftAlias] + 1,
            'skewEdit'  => null,
            'skewLevel' => null,
            'parent'    => null,
            'path'      => null,
            'pathPrefixLen'=> null,
        ];
        if ($this->colPath) {
            $pathPrefix = implode($this->pathDelimiter, array_slice(explode($this->pathDelimiter, $node[$this->colPath]), 0, $context->level));
            $context->pathPrefixLen = strlen($pathPrefix) + 1;
        }
        if ($beforeId !== null) { // Вставка перед узлом bId
            // Получаем предыдущий одноуровневый узел
            $after = $this->resolveNode(
                $this->selectPrevSibling($beforeId)->columns([$this->colId])
            );
            if ($after) {
                // TODO - !!! в функции инсерта сделано проще - проверить почему.
                // по идее получать саблинг - это лишнее
                if ($this->colParent) {
                    $context->parent = $after[$this->colParentAlias];
                }
                if ($this->colPath) {
                    $context->path = explode($this->pathDelimiter, $after[$this->colPath]);
                    array_pop($context->path);
                    $context->path = implode($this->pathDelimiter, $context->path);
                }
                $context->skewLevel = $after[$this->colLevelAlias] - $node[$this->colLevelAlias];
                $context->rightNear = $after[$this->colRightAlias];
            } else {
                $beforeParent = $this->resolveNode($this->selectParent($beforeId));
                if ($this->colParent) {
                    $context->parent = $beforeParent[$this->colIdAlias];
                }
                if ($this->colPath) {
                    $context->path = $beforeParent[$this->colPath];
                }
                $context->skewLevel = $beforeParent[$this->colLevelAlias] + 1 - $node[$this->colLevelAlias];
                $context->rightNear = $beforeParent[$this->colLeftAlias];
            }
        } else { // Вставка в родителя как последнего ребенка
            $parent = $this->resolveNode($parentId);
            if ($parent === null) { // Вставка в корень
                if ($this->colParent) {
                    $context->parent = null;
                }
                if ($this->colPath) {
                    $context->path = $this->pathDelimiter;
                }
                $context->skewLevel = -1*$context->level;
                $context->rightNear = $this->getAdapter()->queryScalar($this->selectMaxRight());
            } else {
                if ($this->colParent) {
                    $context->parent = $parent[$this->colIdAlias];
                }
                if ($this->colPath) {
                    $context->path = $parent[$this->colPath];
                }
                $context->skewLevel = $parent[$this->colLevelAlias] - $context->level + 1;
                $context->rightNear = $parent[$this->colRightAlias] - 1;
            }
        }

        //======================================================================
        $sqlUpdate = $this->getSql()->update($this->table);

        if ($context->rightNear >= $context->right) { // Вниз
            $context->skewEdit = $context->rightNear - $context->left + 1 - $context->skewTree;
            $sqlUpdate->set([
                $this->colLeft  => new Sql\Predicate\IfPredicate(
                    new Sql\Predicate\Operator($this->colRight, '<=', $context->right),
                    new Sql\Predicate\Operator($this->colLeft,  '+', $context->skewEdit),
                    new Sql\Predicate\IfPredicate(
                        new Sql\Predicate\Operator($this->colLeft, '>', $context->right),
                        new Sql\Predicate\Operator($this->colLeft, '-', $context->skewTree),
                        [$this->colLeft, Sql\Predicate\Operator::TYPE_IDENTIFIER]
                    )
                ),
                $this->colLevel => new Sql\Predicate\IfPredicate(
                    new Sql\Predicate\Operator($this->colRight, '<=', $context->right),
                    new Sql\Predicate\Operator($this->colLevel, '+',  $context->skewLevel),
                    [$this->colLevel, Sql\Predicate\Operator::TYPE_IDENTIFIER]
                ),
                $this->colRight => new Sql\Predicate\IfPredicate(
                    new Sql\Predicate\Operator($this->colRight, '<=', $context->right),
                    new Sql\Predicate\Operator($this->colRight, '+',  $context->skewEdit),
                    new Sql\Predicate\IfPredicate(
                        new Sql\Predicate\Operator($this->colRight, '<=', $context->rightNear),
                        new Sql\Predicate\Operator($this->colRight, '-',  $context->skewTree),
                        [$this->colRight, Sql\Predicate\Operator::TYPE_IDENTIFIER]
                    )
                ),
            ]);
            $sqlUpdate->where([
                new Sql\Predicate\Operator($this->colRight, '>',  $context->left),
                new Sql\Predicate\Operator($this->colLeft,  '<=', $context->rightNear),
            ]);
        } else { // Вверх
            $context->skewEdit = $context->rightNear - $context->left + 1;
            $sqlUpdate->set([
                $this->colRight => new Sql\Predicate\IfPredicate(
                    new Sql\Predicate\Operator($this->colLeft, '>=', $context->left),
                    new Sql\Predicate\Operator($this->colRight, '+',  $context->skewEdit),
                    new Sql\Predicate\IfPredicate(
                        new Sql\Predicate\Operator($this->colRight, '<', $context->left),
                        new Sql\Predicate\Operator($this->colRight, '+',  $context->skewTree),
                        [$this->colRight, Sql\Predicate\Operator::TYPE_IDENTIFIER]
                    )
                ),
                $this->colLevel => new Sql\Predicate\IfPredicate(
                    new Sql\Predicate\Operator($this->colLeft, '>=', $context->left),
                    new Sql\Predicate\Operator($this->colLevel, '+',  $context->skewLevel),
                    [$this->colLevel, Sql\Predicate\Operator::TYPE_IDENTIFIER]
                ),
                $this->colLeft  => new Sql\Predicate\IfPredicate(
                    new Sql\Predicate\Operator($this->colLeft, '>=', $context->left),
                    new Sql\Predicate\Operator($this->colLeft, '+',  $context->skewEdit),
                    new Sql\Predicate\IfPredicate(
                        new Sql\Predicate\Operator($this->colLeft, '>', $context->rightNear),
                        new Sql\Predicate\Operator($this->colLeft, '+', $context->skewTree),
                        [$this->colLeft, Sql\Predicate\Operator::TYPE_IDENTIFIER]
                    )
                ),
            ]);
            $sqlUpdate->where([
                new Sql\Predicate\Operator($this->colRight, '>', $context->rightNear),
                new Sql\Predicate\Operator($this->colLeft,  '<', $context->right),
            ]);
        }

        if ($this->colParent) {
            $sqlUpdate->set([
                $this->colParent => new Sql\Predicate\IfPredicate(
                    new Sql\Predicate\Operator($this->colId, '=', $context->id),
                    [$context->parent, Sql\Predicate\Operator::TYPE_VALUE],
                    [$this->colParent, Sql\Predicate\Operator::TYPE_IDENTIFIER]
                ),
            ], 100);
        }
        if ($this->colPath && $this->colName) {
            if ($context->path == $this->pathDelimiter) {
                $context->path = '';
            }
            $sqlUpdate->set([
                $this->colPath => new Sql\Predicate\IfPredicate(
                    new Sql\Predicate\Operator(
                        new Sql\Predicate\Operator($this->colLeft, '>=', $context->left),
                        'AND',
                        new Sql\Predicate\Operator($this->colRight, '<=', $context->right)
                    ),
                    new Sql\Predicate\ConcatPredicate([
                        $context->path => Sql\Predicate\Operator::TYPE_VALUE,
                        new Sql\Predicate\Substring(
                            [$this->colPath, Sql\Predicate\Operator::TYPE_IDENTIFIER],
                            $context->pathPrefixLen
                        ),
                    ]),
                    [$this->colPath, Sql\Predicate\Operator::TYPE_IDENTIFIER]
                )
            ], 200);
        }
        return $sqlUpdate;
    }

    public function insertNode($pId, $bId = null, $sqlInsert = null)
    {
        //TODO - добавить обновление пути
        $sqlUpdate = $this->getSql()->update($this->table);
        $sqlInsert = $sqlInsert ?:$this->getSql()->insert($this->table);

        if ($bId !== null) {
            $before = $this->resolveNode($bId);

            $sqlUpdate->set([
                $this->colRight => new Sql\Predicate\Operator($this->colRight, '+', 2),
                $this->colLeft  => new Sql\Predicate\IfPredicate(
                    new Sql\Predicate\Operator($this->colLeft, '>=', $before[$this->colLeft]),
                    new Sql\Predicate\Operator($this->colLeft, '+', 2),
                    [$this->colLeft, Sql\Predicate\PredicateInterface::TYPE_IDENTIFIER]
                ),
            ])->where([
                new Sql\Predicate\Operator($this->colRight, '>=', $before[$this->colLeft])
            ]);

            $sqlInsert->values([
                $this->colLeft  => $before[$this->colLeft],
                $this->colRight => $before[$this->colRight],
                $this->colLevel => $before[$this->colLevel],
            ], $sqlInsert::VALUES_MERGE);
        } else {
            if (($parent = $this->resolveNode($pId)) !== null) {
                $sqlUpdate->set([
                    $this->colRight => new Sql\Predicate\Operator($this->colRight, '+', 2),
                    $this->colLeft  => new Sql\Predicate\IfPredicate(
                        new Sql\Predicate\Operator($this->colLeft, '>', $parent[$this->colRight]),
                        new Sql\Predicate\Operator($this->colLeft, '+', 2),
                        [$this->colLeft, Sql\Predicate\PredicateInterface::TYPE_IDENTIFIER]
                    ),
                ])->where([
                    new Sql\Predicate\Operator($this->colRight, '>=', $parent[$this->colRight])
                ]);

                $sqlInsert->values([
                    $this->colLeft  => $parent[$this->colRight],
                    $this->colRight => $parent[$this->colRight] + 1,
                    $this->colLevel => $parent[$this->colLevel] + 1,
                ], $sqlInsert::VALUES_MERGE);
            } else {
                $pRight = $this->getAdapter()->queryScalar($this->selectMaxRight());
                $sqlInsert->values([
                    $this->colLeft  => $pRight + 1,
                    $this->colRight => $pRight + 2,
                    $this->colLevel => 0,
                ], $sqlInsert::VALUES_MERGE);
                $sqlUpdate = null;
            }
        }
        if (false && $this->colPath && $parent) {
            //TODO Не ясно что делать - нет имени нового объекта
            $sqlInsert->values([
                $this->colPath  => rtrim($parent[$this->colPath], '/') . '_UNKNOWN_NAME',
            ], $sqlInsert::VALUES_MERGE);
        }
        return [
            'update' => $sqlUpdate,
            'insert' => $sqlInsert,
        ];
    }

    public function deleteNode($id)
    {
        $node = $this->resolveNode($id);

        $delete = $this->getSql()->delete($this->table);
        $delete->where([
            new Sql\Predicate\Operator($this->colLeft,  '>=', $node[$this->colLeft]),
            new Sql\Predicate\Operator($this->colRight, '<=', $node[$this->colRight]),
        ]);

        $update = $this->getSql()->update($this->table);
        $update->set([
            $this->colLeft  => new Sql\Predicate\IfPredicate(
                new Sql\Predicate\Operator($this->colLeft, '>', $node[$this->colLeft]),
                new Sql\Predicate\Operator($this->colLeft, '-', ($node[$this->colRight] - $node[$this->colLeft] + 1)),
                [$this->colLeft, Sql\Predicate\PredicateInterface::TYPE_IDENTIFIER]
            ),
            $this->colRight => new Sql\Predicate\Operator($this->colRight, '-', ($node[$this->colRight] - $node[$this->colLeft] + 1)),
        ]);
        $update->where(
            new Sql\Predicate\Operator($this->colRight, '>', $node[$this->colRight])
        );

        return [
            'delete' => $delete,
            'update' => $update,
        ];
    }

    public function checkStruct()
    {
        $sql = [];
        // 1. Левый ключ ВСЕГДА меньше правого;
        // Если все правильно то результата работы запроса не будет, иначе, получаем список идентификаторов неправильных строк;
        $sql['11'] = $this->getSql()->select($this->table)
                        ->columns([$this->fId])
                        ->where(new Sql\Predicate\Operator($this->colLeft, '>=', $this->colRight, 'identifier', 'identifier'));
        // 2. Наименьший левый ключ ВСЕГДА равен 1;
        // 2.1. Наибольший правый ключ ВСЕГДА равен двойному числу узлов;
        $sql['22'] = $this->getSql()->select($this->table)
                        ->columns([
                            'count' => new Sql\Predicate\Expression("COUNT(<i>$this->colId</i>)"),
                            'min'   => new Sql\Predicate\Expression("MIN(<i>$this->colLeft</i>)"),
                            'max'   => new Sql\Predicate\Expression("MAX(<i>$this->colRight</i>)"),
                        ]);
        // 3. Разница между правым и левым ключом ВСЕГДА нечетное число;
        // Если все правильно то результата работы запроса не будет, иначе, получаем список идентификаторов неправильных строк;
        $sql['33'] = $this->getSql()->select($this->table)
                        ->columns([
                            $this->colId,
                            'ostatok' => new Sql\Predicate\Expression("MOD((</i>$this->colRight</i> - <i>$this->colLeft</i>) / 2)"),
                        ])
                        ->where(['ostatok', 0]);
        // 4. Если уровень узла нечетное число то тогда левый ключ ВСЕГДА нечетное число, то же самое и для четных чисел;
        // Если все правильно то результата работы запроса не будет, иначе, получаем список идентификаторов неправильных строк;
        $sql['44'] = $this->getSql()->select($this->table)
                        ->columns([
                            $this->colId,
                            'ostatok' => new Sql\Predicate\Expression("MOD((<i>$this->colLeft</i> – <i>$this->colLevel</i> + 2) / 2)"),
                        ])
                        ->where(['ostatok', 1]);
        // 5. Ключи ВСЕГДА уникальны, вне зависимости от того правый он или левый;
        // Если все правильно то результата работы запроса не будет, иначе, получаем список идентификаторов неправильных строк;
        $sql['55'] =
"SELECT
    t1.$this->colId,
    COUNT(t1.$this->colId) AS rep,
    MAX(t3.$this->colRight) AS max_right
FROM
    $this->table AS t1, $this->table AS t2, $this->table AS t3
WHERE   t1.$this->colLeft  <> t2.$this->colLeft
    AND t1.$this->colLeft  <> t2.$this->colRight
    AND t1.$this->colRight <> t2.$this->colLeft
    AND t1.$this->colRight <> t2.$this->colRight";
        $sql['55'] .= $this->_dividerSql(' AND t1.');
        $sql['55'] .= $this->_dividerSql(' AND t2.');
        $sql['55'] .= $this->_dividerSql(' AND t3.');
        $sql['55'] .=
"GROUP BY
    t1.$this->colId
HAVING
    max_right <> SQRT(4 * rep + 1) + 1 ";
    }


        /*public function sqlPaths($nodeName, $separator = ' -> ') {
        $sql =
"SELECT
  parent.name,
  (
    SELECT
        GROUP_CONCAT(parent2.name SEPARATOR ' $separator ')
    FROM
        $this->table AS node2,
        $this->table AS parent2
    WHERE
        node2.$this->fLeft BETWEEN parent2.$this->fLeft
        AND parent2.$this->fRight
        AND node2.name = parent.name
    ORDER BY parent2.$this->fLeft
   ) as path
FROM
    $this->table AS node,
    $this->table AS parent
WHERE
    node.$this->fLeft BETWEEN parent.$this->fLeft
    AND parent.$this->fRight
    AND node.name = '$nodeName'
ORDER BY parent.$this->fLeft;";
        return $sql;
    }*/
}
