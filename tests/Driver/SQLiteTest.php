<?php

declare(strict_types=1);

namespace Platine\Test\Database;

use Platine\Database\Driver\SQLite;
use Platine\Database\Schema\AlterColumn;
use Platine\Database\Schema\AlterTable;
use Platine\Database\Schema\CreateColumn;
use Platine\Database\Schema\CreateTable;
use Platine\Dev\PlatineTestCase;
use Platine\Test\Fixture\Connection;

/**
 * SQLite class tests
 *
 * @group core
 * @group database
 */
class SQLiteTest extends PlatineTestCase
{
    public function testGetDatabaseName(): void
    {
        $cnx = $this->getMockBuilder(Connection::class)
                      ->disableOriginalConstructor()
                      ->getMock();

        $cnx->expects($this->any())
             ->method('getDsn')
             ->will($this->returnValue('sqlite:tests.db'));

        $e = new SQLite($cnx);

        $infos = $e->getDatabaseName();

        $this->assertIsArray($infos);
        $this->assertArrayHasKey('result', $infos);

        $this->assertEquals('tests.db', $infos['result']);
    }

    public function testGetTables(): void
    {

        $e = $this->getSQLiteInstance();

        $database = 'foo';

        $infos = $e->getTables($database);

        $this->assertIsArray($infos);
        $this->assertArrayHasKey('sql', $infos);
        $this->assertArrayHasKey('params', $infos);

        $expectedSql = 'SELECT `name` FROM `sqlite_master` WHERE type = ?  ORDER BY `name` ASC';

        $this->assertEquals($expectedSql, $infos['sql']);
        $this->assertCount(1, $infos['params']);
        $this->assertContains('table', $infos['params']);
    }

    public function testGetViews(): void
    {

        $e = $this->getSQLiteInstance();

        $database = 'foo';

        $infos = $e->getViews($database);

        $this->assertIsArray($infos);
        $this->assertArrayHasKey('sql', $infos);
        $this->assertArrayHasKey('params', $infos);

        $expectedSql = 'SELECT `name` FROM `sqlite_master` WHERE type = ?  ORDER BY `name` ASC';

        $this->assertEquals($expectedSql, $infos['sql']);
        $this->assertCount(1, $infos['params']);
        $this->assertContains('view', $infos['params']);
    }

    public function testGetColumns(): void
    {

        $e = $this->getSQLiteInstance();

        $database = 'foo';
        $table = 'bar';

        $infos = $e->getColumns($database, $table);

        $this->assertIsArray($infos);
        $this->assertArrayHasKey('sql', $infos);
        $this->assertArrayHasKey('params', $infos);

        $expectedSql = sprintf('PRAGMA table_info(`%s`)', $table);

        $this->assertEquals($expectedSql, $infos['sql']);
        $this->assertEmpty($infos['params']);
    }

    public function testGetViewColumns(): void
    {

        $e = $this->getSQLiteInstance();

        $database = 'foo';
        $table = 'bar';

        $infos = $e->getViewColumns($database, $table);

        $this->assertIsArray($infos);
        $this->assertArrayHasKey('sql', $infos);
        $this->assertArrayHasKey('params', $infos);

        $expectedSql = sprintf('PRAGMA table_info(`%s`)', $table);

        $this->assertEquals($expectedSql, $infos['sql']);
        $this->assertEmpty($infos['params']);
    }

    public function testRenameTable(): void
    {

        $e = $this->getSQLiteInstance();

        $current = 'foo';
        $new = 'bar';

        $infos = $e->renameTable($current, $new);

        $this->assertIsArray($infos);
        $this->assertArrayHasKey('sql', $infos);
        $this->assertArrayHasKey('params', $infos);

        $expectedSql = sprintf('ALTER TABLE `%s` RENAME TO `%s`', $current, $new);

        $this->assertEquals($expectedSql, $infos['sql']);
        $this->assertEmpty($infos['params']);
    }

    public function testTruncateTable(): void
    {

        $e = $this->getSQLiteInstance();

        $table = 'foo';

        $infos = $e->truncate($table);

        $this->assertIsArray($infos);
        $this->assertArrayHasKey('sql', $infos);
        $this->assertArrayHasKey('params', $infos);

        $expectedSql = sprintf('DELETE FROM `%s`', $table);

        $this->assertEquals($expectedSql, $infos['sql']);
        $this->assertEmpty($infos['params']);
    }

    public function testCreateSimple(): void
    {

        $e = $this->getSQLiteInstance();

        $ccIntMockMethodsMaps = [
            'getName' => 'col_int',
            'getType' => 'integer',
        ];

        $int = $this->getCreateColumnInstance($ccIntMockMethodsMaps);

        $columns = [
            'col_int' => $int,
        ];

        $ctMockMethodsMaps = [
            'getTableName' => 'bar',
            'getColumns' => $columns,
            'getIndexes' => [],
            'getForeignKeys' => [],
            'getUniqueKeys' => [],
            'getPrimaryKey' => [],
        ];

        $ct = $this->getCreateTableInstance($ctMockMethodsMaps);

        $infos = $e->create($ct);

        $this->assertCount(1, $infos);
        $this->assertIsArray($infos[0]);
        $this->assertArrayHasKey('sql', $infos[0]);
        $this->assertArrayHasKey('params', $infos[0]);

        $expectedSql = 'CREATE TABLE `bar`(
`col_int` INTEGER)
';

        $this->assertEquals($expectedSql, $infos[0]['sql']);
        $this->assertEmpty($infos[0]['params']);
    }

    public function testCreateAllDataTypes(): void
    {

        $e = $this->getSQLiteInstance();

        //Integer
        $ccIntMockMethodsMaps = [
            'getName' => 'col_int',
            'getType' => 'integer',
        ];

        //Time
        $ccTimeMockMethodsMaps = [
            'getName' => 'col_time',
            'getType' => 'time',
        ];

        //Timestamp
        $ccTimestampSimpleMockMethodsMaps = [
            'getName' => 'col_timestamp',
            'getType' => 'timestamp',
        ];


        //Integer
        $int = $this->getCreateColumnInstance($ccIntMockMethodsMaps);
        $int->set('autoincrement', true);

        //Time
        $time = $this->getCreateColumnInstance($ccTimeMockMethodsMaps);

        //Timestamp
        $timestamp = $this->getCreateColumnInstance($ccTimestampSimpleMockMethodsMaps);

        $columns = [
            'col_int' => $int,

            'col_time' => $time,

            'col_timestamp' => $timestamp,
        ];

        $ctMockMethodsMaps = [
            'getTableName' => 'bar',
            'getColumns' => $columns,
            'getIndexes' => [],
            'getForeignKeys' => [],
            'getUniqueKeys' => [],
            'getPrimaryKey' => [],
            'getEngine' => 'INNODB',
        ];

        $ct = $this->getCreateTableInstance($ctMockMethodsMaps);

        $infos = $e->create($ct);

        $this->assertCount(1, $infos);
        $this->assertIsArray($infos[0]);
        $this->assertArrayHasKey('sql', $infos[0]);
        $this->assertArrayHasKey('params', $infos[0]);

        $expectedSql = 'CREATE TABLE `bar`(
`col_int` INTEGER PRIMARY KEY AUTOINCREMENT,
`col_time` DATETIME,
`col_timestamp` DATETIME)
';

        $this->assertCommandOutput($expectedSql, $infos[0]['sql']);
        $this->assertEmpty($infos[0]['params']);
    }

    public function testAlterAddUniqueAndIndex(): void
    {

        $e = $this->getSQLiteInstance();

        $atMockMethodsMaps = [
            'getCommands' => [
                [
                    'type' => 'addUnique',
                    'data' => [
                        'name' => 'foo_uk_col',
                        'columns' => ['foo_col']
                    ]
                ],
                [
                    'type' => 'addIndex',
                    'data' => [
                        'name' => 'foo_ik_col',
                        'columns' => ['foo_col']
                    ]
                ],
            ],
            'getTableName' => 'foo'
        ];

        $at = $this->getAlterTableInstance($atMockMethodsMaps);

        $infos = $e->alter($at);

        $this->assertCount(2, $infos);
        $this->assertIsArray($infos[0]);
        $this->assertArrayHasKey('sql', $infos[0]);
        $this->assertArrayHasKey('params', $infos[0]);
        $this->assertArrayHasKey('sql', $infos[1]);
        $this->assertArrayHasKey('params', $infos[1]);


        $expectedAddUkSql = 'CREATE UNIQUE INDEX `foo_uk_col` ON `foo` (`foo_col`)';
        $expectedAddIkSql = 'CREATE INDEX `foo_ik_col` ON `foo` (`foo_col`)';

        $this->assertEquals($expectedAddUkSql, $infos[0]['sql']);
        $this->assertEquals($expectedAddIkSql, $infos[1]['sql']);

        $this->assertEmpty($infos[0]['params']);
        $this->assertEmpty($infos[1]['params']);
    }


    private function getSQLiteInstance(): SQLite
    {
        $cnx = new Connection('SQLite');

        return new SQLite($cnx);
    }

    private function getCreateTableInstance(array $mockInfos = []): CreateTable
    {
        /** @var CreateTable $ct */
        $ct = $this->getMockBuilder(CreateTable::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        foreach ($mockInfos as $method => $returnValue) {
            $ct->expects($this->any())
                ->method($method)
                ->will($this->returnValue($returnValue));
        }

        return $ct;
    }

    private function getAlterTableInstance(array $mockInfos = []): AlterTable
    {
        /** @var AlterTable $at */
        $at = $this->getMockBuilder(AlterTable::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        foreach ($mockInfos as $method => $returnValue) {
            $at->expects($this->any())
                ->method($method)
                ->will($this->returnValue($returnValue));
        }

        return $at;
    }

    private function getCreateColumnInstance(array $mockInfos = []): CreateColumn
    {
        $methods = $this->getClassMethodsToMock(CreateColumn::class, ['get', 'set']);

        /** @var CreateColumn $cc */
        $cc = $this->getMockBuilder(CreateColumn::class)
                    ->onlyMethods($methods)
                    ->disableOriginalConstructor()
                    ->getMock();

        foreach ($mockInfos as $method => $returnValue) {
            $cc->expects($this->any())
                ->method($method)
                ->will($this->returnValue($returnValue));
        }

        return $cc;
    }

    private function getAlterColumnInstance(array $mockInfos = []): AlterColumn
    {
        $methods = $this->getClassMethodsToMock(AlterColumn::class, ['get', 'set']);

        /** @var AlterColumn $ac */
        $ac = $this->getMockBuilder(AlterColumn::class)
                    ->onlyMethods($methods)
                    ->disableOriginalConstructor()
                    ->getMock();

        foreach ($mockInfos as $method => $returnValue) {
            $ac->expects($this->any())
                ->method($method)
                ->will($this->returnValue($returnValue));
        }

        return $ac;
    }
}
