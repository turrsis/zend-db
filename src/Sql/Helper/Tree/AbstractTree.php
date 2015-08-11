<?php
namespace Zend\Db\Sql\Helper\Tree;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Predicate as SqlPredicates;

abstract class AbstractTree
{

    protected $sql        = null;
    protected $adapter          = null;

    protected $table            = null;
    protected $tableAlias       = null;

    protected $colId            = 'id';
    protected $colIdAlias       = 'id';

    protected $colName          = null;
    protected $colNameAlias     = null;

    protected $pathDelimiter    = '/';

    public function __construct($adapter, $table, $columns = [])
    {
        $this->setAdapter($adapter);
        $this->setTable($table);
        if ($columns) {
            $this->setColumns($columns);
        }
    }

    /**
     * @return \Zend\Db\Sql\Sql */
    protected function getSql()
    {
        if (!$this->sql) {
            $this->sql = new Sql($this->getAdapter());
        }
        return $this->sql;
    }

    public function setSql(Sql $sql)
    {
        $this->sql = $sql;
        return $this;
    }

    public function setAdapter($adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * @return \Tgs\Db\Adapter\Adapter
     * @throws \Exception */
    protected function getAdapter()
    {
        if ($this->adapter != null) {
            if (is_string($this->adapter)) {
                $this->adapter = $this->getServiceLocator()->getServiceLocator()->get($this->adapter);
            }
            return $this->adapter;
        }
        throw new \Exception('adapter is not set');
    }

    public function setTable($table)
    {
        if (is_array($table)) {
            reset($table);
            $this->table = current($table);
            $this->tableAlias = key($table);
        } elseif (is_string($table)) {
            $this->table = $table;
            $this->tableAlias = $table;
        }
        return $this;
    }

    public function setColumns($columns)
    {
        foreach ($columns as $colName => $column) {
            $this->setColumn($colName, $column);
        }
        return $this;
    }

    public function setColumn($colName, $column)
    {
        $colName  = 'col' . ucfirst($colName);
        $colAlias = $colName . 'Alias';
        if (is_string($column)) {
            $this->$colName = $column;
            $this->$colAlias = $column;
        } elseif (is_array($column)) {
            $this->$colName = current($column);
            $this->$colAlias = key($column);
        }
        return $this;
    }

    protected function resolveNode($node)
    {
        if ($node === null || (is_numeric($node) && $node < 0)) {
            return;
        }
        if (is_array($node) || $node instanceof \ArrayAccess) {
            return $node;
        }
        $node = $node instanceof Select
                ? $node
                : $this->selectNode($node);

        return $this->getAdapter()->queryRow(
            $this->getSql()->buildSqlString($node, $this->getAdapter())
        );
    }

    protected function getWhereConditionById($id, $prefix = '')
    {
        return function ($where) use ($id, $prefix) {
            if (is_numeric($id)) {
                $fieldName = $this->tableAlias . '.' . $this->colId;
            } elseif ($this->colName && is_string($id)) {
                $fieldName = $this->tableAlias . '.' . $this->colName;
            }
            if ($prefix != null) {
                $fieldName = $prefix . '.' . $fieldName;
            }
            $where->addPredicate(new SqlPredicates\Operator($fieldName, SqlPredicates\Operator::OP_EQ, $id));
        };
    }

    abstract public function selectNode($id);
    abstract public function selectNodeByPath($path, $strict = true);
    abstract public function selectChilds($id, $options = []);
    abstract public function selectParent($id);
    abstract public function selectParentBranch($id, $includeNode = false);
    abstract public function selectPrevSibling($id);
    abstract public function selectNextSibling($id);
    abstract public function moveNode($nodeId, $parentId, $beforeId);
    abstract public function insertNode($pId, $bId = null, $sqlInsert = null);
    abstract public function deleteNode($id);
}
