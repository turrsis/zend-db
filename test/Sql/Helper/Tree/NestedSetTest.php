<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Sql\Helper\Tree;

use Zend\Db\Sql\Helper\Tree\NestedSet;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use ZendTest\Db\TestAsset\TrustingMysqlPlatform;

class NestedSetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Sql
     */
    protected $sql;

    /**
     * @var Adapter
     */
    protected $adapter;

    /**
     * @var NestedSet
     */
    protected $nestedSet;

    public function setUp()
    {
        $this->adapter = $this->getMock(Adapter::class, ['query'], [
            $this->getMock('Zend\Db\Adapter\Driver\DriverInterface'),
            new TrustingMysqlPlatform()
        ]);

        $this->sql = new Sql($this->adapter);

        $this->nestedSet = new NestedSet($this->adapter, 'tFoo');
    }

    public function testExcludeLeafs()
    {
        $sqlObject = $this->nestedSet->excludeLeafs($this->sql->select('XXX'), [1, 2]);
        $this->assertEquals(
            "SELECT `XXX`.* FROM `XXX` INNER JOIN (SELECT `tFoo`.* FROM `tFoo` AS `tFoo` WHERE `tFoo`.`id` = '1') AS `ex0` ON `tFoo`.`trleft` < `ex0`.`trleft` OR `tFoo`.`trright` > `ex0`.`trright` INNER JOIN (SELECT `tFoo`.* FROM `tFoo` AS `tFoo` WHERE `tFoo`.`id` = '2') AS `ex1` ON `tFoo`.`trleft` < `ex1`.`trleft` OR `tFoo`.`trright` > `ex1`.`trright`",
            $this->sql->buildSqlString($sqlObject)
        );
    }

    public function testSelectNode()
    {
        $sqlObject = $this->nestedSet->selectNode(10);
        $this->assertEquals(
            "SELECT `tFoo`.* FROM `tFoo` AS `tFoo` WHERE `tFoo`.`id` = '10'",
            $this->sql->buildSqlString($sqlObject)
        );
    }

    public function testSelectNodeByPath()
    {
        $this->nestedSet->setColumn('path', 'thpath');
        $sqlObject = $this->nestedSet->selectNodeByPath('p1/p2');
        $this->assertEquals(
            "SELECT `tFoo`.* FROM `tFoo` WHERE `thpath` = 'p1/p2'",
            $this->sql->buildSqlString($sqlObject)
        );

        $sqlObject = $this->nestedSet->selectNodeByPath('p1/p2', false);
        $this->assertEquals(
            "SELECT `tFoo`.* FROM `tFoo` WHERE `thpath` IN ('/p1', '/p1/p2') ORDER BY `thpath` DESC LIMIT 1",
            $this->sql->buildSqlString($sqlObject)
        );
    }

    public function testSelectChilds()
    {
        $sqlObject = $this->nestedSet->selectChilds(null, [
            'depth' => 7,
        ]);
        $this->assertEquals(
            "SELECT `tFoo`.* FROM `tFoo` AS `tFoo` WHERE `tFoo`.`trlevel` <= '7' ORDER BY `tFoo`.`trleft` ASC",
            $this->sql->buildSqlString($sqlObject)
        );

        $sqlObject = $this->nestedSet->selectChilds(10);
        $this->assertEquals(
            "SELECT `tFoo`.* FROM `tFoo` AS `tFoo` INNER JOIN (SELECT `tFoo`.* FROM `tFoo` AS `tFoo` WHERE `tFoo`.`id` = '10') AS `root` ON `tFoo`.`trleft` >= `root`.`trleft` AND `tFoo`.`trright` <= `root`.`trright` ORDER BY `tFoo`.`trleft` ASC",
            $this->sql->buildSqlString($sqlObject)
        );

        $sqlObject = $this->nestedSet->selectChilds(10, [
            'depth' => 7,
        ]);
        $this->assertEquals(
            "SELECT `tFoo`.* FROM `tFoo` AS `tFoo` INNER JOIN (SELECT `tFoo`.* FROM `tFoo` AS `tFoo` WHERE `tFoo`.`id` = '10') AS `root` ON `tFoo`.`trleft` >= `root`.`trleft` AND `tFoo`.`trright` <= `root`.`trright` WHERE `tFoo`.`trlevel` - `root`.`trlevel` <= '7' ORDER BY `tFoo`.`trleft` ASC",
            $this->sql->buildSqlString($sqlObject)
        );

        $sqlObject = $this->nestedSet->selectChilds(10, [
            'depth' => 7,
            'exclude_root' => true,
        ]);
        $this->assertEquals(
            "SELECT `tFoo`.* FROM `tFoo` AS `tFoo` INNER JOIN (SELECT `tFoo`.* FROM `tFoo` AS `tFoo` WHERE `tFoo`.`id` = '10') AS `root` ON `tFoo`.`trleft` > `root`.`trleft` AND `tFoo`.`trright` < `root`.`trright` WHERE `tFoo`.`trlevel` - `root`.`trlevel` <= '7' ORDER BY `tFoo`.`trleft` ASC",
            $this->sql->buildSqlString($sqlObject)
        );

        $sqlObject = $this->nestedSet->selectChilds(10, [
            'depth' => 7,
            'select' => $this->sql->select('tFoo')->columns(['c1', 'c2']),
        ]);
        $this->assertEquals(
            "SELECT `tFoo`.`c1` AS `c1`, `tFoo`.`c2` AS `c2` FROM `tFoo` INNER JOIN (SELECT `tFoo`.* FROM `tFoo` AS `tFoo` WHERE `tFoo`.`id` = '10') AS `root` ON `tFoo`.`trleft` >= `root`.`trleft` AND `tFoo`.`trright` <= `root`.`trright` WHERE `tFoo`.`trlevel` - `root`.`trlevel` <= '7' ORDER BY `tFoo`.`trleft` ASC",
            $this->sql->buildSqlString($sqlObject)
        );
    }

    public function testSelectParent()
    {
        $sqlObject = $this->nestedSet->selectParent(10);

        $this->assertEquals(
            "SELECT `tFoo`.`tFoo`.* AS `tFoo.*` FROM `tFoo` AS `tFoo` INNER JOIN `tFoo` AS `child` ON `tFoo`.`trleft` <= `child`.`trleft` AND `tFoo`.`trright` >= `child`.`trright` AND `child`.`trlevel` - '1' = `tFoo`.`trlevel` WHERE `child`.`tFoo`.`id` = '10'",
            $this->sql->buildSqlString($sqlObject)
        );
    }

    public function testSelectParentBranch()
    {
        $sqlObject = $this->nestedSet->selectParentBranch(10);
        $this->assertEquals(
            "SELECT `tFoo`.*, `i`.* FROM `tFoo` AS `tFoo` INNER JOIN `tFoo` AS `i` ON `i`.`id` = '10' AND `tFoo`.`trleft` < `i`.`trleft` AND `tFoo`.`trright` > `i`.`trright` ORDER BY `tFoo`.`trleft` ASC",
            $this->sql->buildSqlString($sqlObject)
        );

        $sqlObject = $this->nestedSet->selectParentBranch(10, true);
        $this->assertEquals(
            "SELECT `tFoo`.*, `i`.* FROM `tFoo` AS `tFoo` INNER JOIN `tFoo` AS `i` ON `i`.`id` = '10' AND `tFoo`.`trleft` <= `i`.`trleft` AND `tFoo`.`trright` >= `i`.`trright` ORDER BY `tFoo`.`trleft` ASC",
            $this->sql->buildSqlString($sqlObject)
        );
    }

    public function testSelectPrevSibling()
    {
        $sqlObject = $this->nestedSet->selectPrevSibling(10);
        $this->assertEquals(
            "SELECT `tFoo`.* FROM `tFoo` AS `tFoo` INNER JOIN `tFoo` AS `item` ON `tFoo`.`trright` = `itemtrleft` - '1' AND `tFoo`.`trlevel` = `item`.`trlevel` WHERE `item`.`tFoo`.`id` = '10'",
            $this->sql->buildSqlString($sqlObject)
        );
    }
    
    public function testSelectNextSibling()
    {
        $sqlObject = $this->nestedSet->selectNextSibling(10);
        $this->assertEquals(
            "SELECT `tFoo`.* FROM `tFoo` AS `tFoo` INNER JOIN `tFoo` AS `item` ON `tFoo`.`trleft` = `itemtrright` + '1' AND `tFoo`.`trlevel` = `item`.`trlevel` WHERE `item`.`tFoo`.`id` = '10'",
            $this->sql->buildSqlString($sqlObject)
        );
    }

    public function testMoveNode()
    {
        $this->setUpAdapterQueryResult($this->returnCallback(function ($sql) {
            return (new ResultSet())->initialize([[
                'id'       => 10,
                'trleft'   => 101,
                'trright'  => 102,
                'trlevel'  => 103,
                'thparent' => 11,
            ]]);
        }));
        $this->nestedSet->setColumn('parent', 'thparent');

        $sqlObject = $this->nestedSet->moveNode(3, 4, 5);
        $this->assertEquals(
            "UPDATE `tFoo` SET `thparent` = CASE WHEN `id` = '10' THEN '11' ELSE `thparent` END, `trleft` = CASE WHEN `trright` <= '102' THEN `trleft` + '0' ELSE CASE WHEN `trleft` > '102' THEN `trleft` - '2' ELSE `trleft` END END, `trlevel` = CASE WHEN `trright` <= '102' THEN `trlevel` + '0' ELSE `trlevel` END, `trright` = CASE WHEN `trright` <= '102' THEN `trright` + '0' ELSE CASE WHEN `trright` <= '102' THEN `trright` - '2' ELSE `trright` END END WHERE `trright` > '101' AND `trleft` <= '102'",
            $this->sql->buildSqlString($sqlObject)
        );
    }

    public function testInsertNode()
    {
        $this->setUpAdapterQueryResult($this->returnCallback(function ($sql) {
            return (new ResultSet())->initialize([
                [
                    'id'      => 10,
                    'trleft'  => 101,
                    'trright' => 102,
                    'trlevel' => 103,
                ]
            ]);
        }));

        $sqlObject = $this->nestedSet->insertNode(null);
        $this->assertEquals([
            null,
            "INSERT INTO `tFoo` (`trleft`, `trright`, `trlevel`) VALUES ('11', '12', '0')",
        ],[
            null,
            $this->sql->buildSqlString($sqlObject['insert'])
        ]);

        $sqlObject = $this->nestedSet->insertNode(10);
        $this->assertEquals([
            "UPDATE `tFoo` SET `trright` = `trright` + '2', `trleft` = CASE WHEN `trleft` > '102' THEN `trleft` + '2' ELSE `trleft` END WHERE `trright` >= '102'",
            "INSERT INTO `tFoo` (`trleft`, `trright`, `trlevel`) VALUES ('102', '103', '104')",
        ],[
            $this->sql->buildSqlString($sqlObject['update']),
            $this->sql->buildSqlString($sqlObject['insert'])
        ]);

        $sqlObject = $this->nestedSet->insertNode(10, 20);
        $this->assertEquals([
            "UPDATE `tFoo` SET `trright` = `trright` + '2', `trleft` = CASE WHEN `trleft` >= '101' THEN `trleft` + '2' ELSE `trleft` END WHERE `trright` >= '101'",
            "INSERT INTO `tFoo` (`trleft`, `trright`, `trlevel`) VALUES ('101', '102', '103')",
        ],[
            $this->sql->buildSqlString($sqlObject['update']),
            $this->sql->buildSqlString($sqlObject['insert'])
        ]);

        $sqlObject = $this->nestedSet->insertNode(10, null, $this->sql->insert('tFoo')->columns(['c1', 'c2'])->values(['v1', 'v2']));
        $this->assertEquals([
            "UPDATE `tFoo` SET `trright` = `trright` + '2', `trleft` = CASE WHEN `trleft` > '102' THEN `trleft` + '2' ELSE `trleft` END WHERE `trright` >= '102'",
            "INSERT INTO `tFoo` (`c1`, `c2`, `trleft`, `trright`, `trlevel`) VALUES ('v1', 'v2', '102', '103', '104')",
        ],[
            $this->sql->buildSqlString($sqlObject['update']),
            $this->sql->buildSqlString($sqlObject['insert'])
        ]);
    }

    public function testDeleteNode()
    {
        $this->setUpAdapterQueryResult($this->returnCallback(function ($sql) {
            return (new ResultSet())->initialize([
                [
                    'id'      => 10,
                    'trleft'  => 101,
                    'trright' => 102,
                    'trlevel' => 103,
                ]
            ]);
        }));

        $sqlObject = $this->nestedSet->deleteNode(10);
        $this->assertEquals([
            "DELETE FROM `tFoo` WHERE `trleft` >= '101' AND `trright` <= '102'",
            "UPDATE `tFoo` SET `trleft` = CASE WHEN `trleft` > '101' THEN `trleft` - '2' ELSE `trleft` END, `trright` = `trright` - '2' WHERE `trright` > '102'",
        ],[
            $this->sql->buildSqlString($sqlObject['delete']),
            $this->sql->buildSqlString($sqlObject['update'])
        ]);
    }

    protected function setUpAdapterQueryResult($result)
    {
        $this->adapter->expects($this->any())->method('query')->will($result);
    }
}
