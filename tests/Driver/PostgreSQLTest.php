<?php

declare(strict_types=1);

namespace Platine\Test\Database;

use Platine\Database\Driver\PostgreSQL;
use Platine\Database\Schema\AlterColumn;
use Platine\Database\Schema\AlterTable;
use Platine\Database\Schema\CreateColumn;
use Platine\Database\Schema\CreateTable;
use Platine\Dev\PlatineTestCase;
use Platine\Test\Fixture\Connection;

/**
 * PostgreSQL class tests
 *
 * @group core
 * @group database
 */
class PostgreSQLTest extends PlatineTestCase
{
    public function testGetDatabaseName(): void
    {
        $e = $this->getPostgreSQLInstance();

        $infos = $e->getDatabaseName();

        $this->assertIsArray($infos);
        $this->assertArrayHasKey('sql', $infos);
        $this->assertArrayHasKey('params', $infos);

        $this->assertEquals('SELECT current_schema()', $infos['sql']);

        $this->assertEmpty($infos['params']);
    }

    public function testGetColumns(): void
    {

        $e = $this->getPostgreSQLInstance();

        $database = 'foo';
        $table = 'bar';

        $infos = $e->getColumns($database, $table);

        $this->assertIsArray($infos);
        $this->assertArrayHasKey('sql', $infos);
        $this->assertArrayHasKey('params', $infos);

        $expectedSql = sprintf('SELECT "column_name" AS "name", "udt_name" AS "type" '
                . 'FROM "information_schema"."columns" '
                . 'WHERE "table_schema" = ? AND "table_name" = ? '
                . 'ORDER BY "ordinal_position" ASC', $table);

        $this->assertEquals($expectedSql, $infos['sql']);
        $this->assertCount(2, $infos['params']);
        $this->assertEquals($database, $infos['params'][0]);
        $this->assertEquals($table, $infos['params'][1]);
    }

    public function testRenameTable(): void
    {

        $e = $this->getPostgreSQLInstance();

        $current = 'foo';
        $new = 'bar';

        $infos = $e->renameTable($current, $new);

        $this->assertIsArray($infos);
        $this->assertArrayHasKey('sql', $infos);
        $this->assertArrayHasKey('params', $infos);

        $expectedSql = sprintf('ALTER TABLE "%s" RENAME TO "%s"', $current, $new);

        $this->assertEquals($expectedSql, $infos['sql']);
        $this->assertEmpty($infos['params']);
    }

    public function testCreateSimple(): void
    {

        $e = $this->getPostgreSQLInstance();

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
            'getIndexes' => [
               'foo_ik_bar' => ['bar']
            ],
            'getForeignKeys' => [],
            'getUniqueKeys' => [],
            'getPrimaryKey' => [],
        ];

        $ct = $this->getCreateTableInstance($ctMockMethodsMaps);

        $infos = $e->create($ct);

        $this->assertCount(2, $infos);
        $this->assertIsArray($infos[0]);
        $this->assertIsArray($infos[1]);
        $this->assertArrayHasKey('sql', $infos[0]);
        $this->assertArrayHasKey('params', $infos[0]);

        $expectedSql = 'CREATE TABLE "bar"(
"col_int" INTEGER)
';

        $this->assertCommandOutput($expectedSql, $infos[0]['sql']);
        $this->assertEmpty($infos[0]['params']);
    }

    public function testCreateAllDataTypes(): void
    {

        $e = $this->getPostgreSQLInstance();

        //Integer
        $ccIntMockMethodsMaps = [
            'getName' => 'col_int',
            'getType' => 'integer',
        ];

        $ccMedMockMethodsMaps = [
            'getName' => 'col_medium_int',
            'getType' => 'integer',
        ];

        $ccTinyMockMethodsMaps = [
            'getName' => 'col_tiny_int',
            'getType' => 'integer',
        ];

        $ccSmallMockMethodsMaps = [
            'getName' => 'col_small_int',
            'getType' => 'integer',
        ];

        $ccBigMockMethodsMaps = [
            'getName' => 'col_big_int',
            'getType' => 'integer',
        ];

        //Decimal
        $ccDecSimpleMockMethodsMaps = [
            'getName' => 'col_dec_sim',
            'getType' => 'decimal',
        ];

        $ccDecLenMockMethodsMaps = [
            'getName' => 'col_dec_len',
            'getType' => 'decimal',
        ];

        $ccDecPreMockMethodsMaps = [
            'getName' => 'col_dec_pre',
            'getType' => 'decimal',
        ];

        //Double
        $ccDoubleMockMethodsMaps = [
            'getName' => 'col_double',
            'getType' => 'double',
        ];

        //Float
        $ccFloatMockMethodsMaps = [
            'getName' => 'col_double',
            'getType' => 'float',
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

        //Datetime
        $ccDatetimeMockMethodsMaps = [
            'getName' => 'col_datetime',
            'getType' => 'datetime',
        ];

        //Binary
        $ccBinaryMockMethodsMaps = [
            'getName' => 'col_binary',
            'getType' => 'binary',
        ];

        $ccBinaryMedMockMethodsMaps = [
            'getName' => 'col_medium_binary',
            'getType' => 'binary',
        ];

        $ccBinaryTinyMockMethodsMaps = [
            'getName' => 'col_tiny_binary',
            'getType' => 'binary',
        ];

        $ccBinarySmallMockMethodsMaps = [
            'getName' => 'col_small_binary',
            'getType' => 'binary',
        ];

        $ccBinaryBigMockMethodsMaps = [
            'getName' => 'col_big_binary',
            'getType' => 'binary',
        ];


        //Datetime
        $datetime = $this->getCreateColumnInstance($ccDatetimeMockMethodsMaps);

        //Decimal
        $decSimple = $this->getCreateColumnInstance($ccDecSimpleMockMethodsMaps);

        $decLength = $this->getCreateColumnInstance($ccDecLenMockMethodsMaps);
        $decLength->set('length', 10);

        $decPrecision = $this->getCreateColumnInstance($ccDecPreMockMethodsMaps);
        $decPrecision->set('length', 10);
        $decPrecision->set('precision', 2);

        //Binary
        $binary = $this->getCreateColumnInstance($ccBinaryMockMethodsMaps);
        $binary->set('size', 'normal');

        $binaryTiny = $this->getCreateColumnInstance($ccBinaryTinyMockMethodsMaps);
        $binaryTiny->set('size', 'tiny');

        $binarySmall = $this->getCreateColumnInstance($ccBinarySmallMockMethodsMaps);
        $binarySmall->set('size', 'small');

        $binaryMedium = $this->getCreateColumnInstance($ccBinaryMedMockMethodsMaps);
        $binaryMedium->set('size', 'medium');

        $binaryBig = $this->getCreateColumnInstance($ccBinaryBigMockMethodsMaps);
        $binaryBig->set('size', 'big');


        //Integer
        $int = $this->getCreateColumnInstance($ccIntMockMethodsMaps);
        $int->set('autoincrement', true);
        $int->set('size', 'normal');
        $int->set('description', 'simple int column');

        $tiny = $this->getCreateColumnInstance($ccTinyMockMethodsMaps);
        $tiny->set('size', 'tiny');

        $small = $this->getCreateColumnInstance($ccSmallMockMethodsMaps);
        $small->set('size', 'small');

        $medium = $this->getCreateColumnInstance($ccMedMockMethodsMaps);
        $medium->set('size', 'medium');

        $big = $this->getCreateColumnInstance($ccBigMockMethodsMaps);
        $big->set('size', 'big');

        //Double
        $double = $this->getCreateColumnInstance($ccDoubleMockMethodsMaps);

        //Float
        $float = $this->getCreateColumnInstance($ccFloatMockMethodsMaps);

        //Time
        $time = $this->getCreateColumnInstance($ccTimeMockMethodsMaps);

        //Timestamp
        $timestamp = $this->getCreateColumnInstance($ccTimestampSimpleMockMethodsMaps);

        $columns = [
            'col_int' => $int,
            'col_tiny_int' => $tiny,
            'col_small_int' => $small,
            'col_medium_int' => $medium,
            'col_big_int' => $big,

            'col_binary' => $binary,
            'col_tiny_binary' => $binaryTiny,
            'col_small_binary' => $binarySmall,
            'col_medium_binary' => $binaryMedium,
            'col_big_binary' => $binaryBig,

            'col_dec_sim' => $decSimple,
            'col_dec_len' => $decLength,
            'col_dec_pre' => $decPrecision,

            'col_time' => $time,

            'col_timestamp' => $timestamp,

            'col_datetime' => $datetime,

            'col_double' => $double,

            'col_float' => $float,

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

        $expectedSql = 'CREATE TABLE "bar"(
"col_int" SERIAL COMMENT \'simple int column\',
"col_tiny_int" SMALLINT,
"col_small_int" SMALLINT,
"col_medium_int" INTEGER,
"col_big_int" BIGINT,
"col_binary" BYTEA,
"col_tiny_binary" BYTEA,
"col_small_binary" BYTEA,
"col_medium_binary" BYTEA,
"col_big_binary" BYTEA,
"col_dec_sim" DECIMAL,
"col_dec_len" DECIMAL(10),
"col_dec_pre" DECIMAL(10, 2),
"col_time" TIME(0) WITHOUT TIME ZONE,
"col_timestamp" TIMESTAMP(0) WITHOUT TIME ZONE,
"col_datetime" TIMESTAMP(0) WITHOUT TIME ZONE,
"col_double" DOUBLE PRECISION,
"col_double" REAL)
';

        $this->assertCommandOutput($expectedSql, $infos[0]['sql']);
        $this->assertEmpty($infos[0]['params']);
    }

    public function testAlterAddDropIndex(): void
    {

        $e = $this->getPostgreSQLInstance();

        $atMockMethodsMaps = [
            'getCommands' => [
                [
                    'type' => 'dropIndex',
                    'data' => 'baz_ik_col'
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


        $expectedAddIkSql = 'CREATE INDEX "foo_foo_ik_col" ON "foo" ("foo_col")';
        $expectedDropIkSql = 'DROP INDEX "foo_baz_ik_col"';

        $this->assertEquals($expectedDropIkSql, $infos[0]['sql']);
        $this->assertEquals($expectedAddIkSql, $infos[1]['sql']);

        $this->assertEmpty($infos[0]['params']);
        $this->assertEmpty($infos[1]['params']);
    }

    public function testRenameColumn(): void
    {

        $e = $this->getPostgreSQLInstance();

        $from = 'old_col';
        $to = 'new_col';

        $acIntMockMethodsMaps = [
            'getName' => $to,
            'getType' => 'integer',
        ];

        $atMockMethodsMaps = [
            'getCommands' => [
                [
                    'type' => 'renameColumn',
                    'data' => [
                        'from' => $from,
                        'column' => $this->getAlterColumnInstance($acIntMockMethodsMaps),
                    ]
                ],
            ],
            'getTableName' => 'foo'
        ];

        $at = $this->getAlterTableInstance($atMockMethodsMaps);

        $infos = $e->alter($at);


        $this->assertCount(1, $infos);
        $this->assertIsArray($infos[0]);

        $this->assertArrayHasKey('sql', $infos[0]);
        $this->assertArrayHasKey('params', $infos[0]);

        $expectedSql = 'ALTER TABLE "foo" RENAME COLUMN "old_col" TO "new_col"';
        $this->assertEquals($expectedSql, $infos[0]['sql']);
        $this->assertEmpty($infos[0]['params']);
    }



    private function getPostgreSQLInstance(): PostgreSQL
    {
        $cnx = new Connection('PostgreSQL');

        return new PostgreSQL($cnx);
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
