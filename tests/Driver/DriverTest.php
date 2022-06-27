<?php

declare(strict_types=1);

namespace Platine\Test\Database;

use DateTime;
use Platine\Database\Driver\Driver;
use Platine\Database\Query\Expression;
use Platine\Database\Query\HavingExpression;
use Platine\Database\Query\HavingStatement;
use Platine\Database\Query\Join;
use Platine\Database\Query\QueryStatement;
use Platine\Database\Query\SubQuery;
use Platine\Database\Query\WhereStatement;
use Platine\Database\Schema\AlterColumn;
use Platine\Database\Schema\AlterTable;
use Platine\Database\Schema\CreateColumn;
use Platine\Database\Schema\CreateTable;
use Platine\Database\Schema\ForeignKey;
use Platine\Dev\PlatineTestCase;
use Platine\Test\Fixture\Connection;

/**
 * Driver class tests
 *
 * @group core
 * @group database
 */
class DriverTest extends PlatineTestCase
{

    public function testConstructor(): void
    {
        $e = $this->getDriverInstance();
        $cr = $this->getPrivateProtectedAttribute(Driver::class, 'connection');
        $this->assertInstanceOf(Connection::class, $cr->getValue($e));
    }

    public function testSelectDefault(): void
    {
        $e = $this->getDriverInstance();

        $mockMethodsMaps = [
            'hasDistinct' => false,
            'getOffset' => -1,
            'getLimit' => 0,
            'getIntoTable' => null,
            'getTables' => ['bar'],
            'getJoins' => [],
            'getWheres' => [],
            'getGroupBy' => [],
            'getHaving' => [],
            'getOrder' => [],
            'getColumns' => [],
        ];

        $qs = $this->getQueryStatementInstance($mockMethodsMaps);
        $expected = 'SELECT * FROM "bar"';
        $result = $e->select($qs);
        $this->assertEquals($result, $expected);
    }

    public function testSelectDistinct(): void
    {
        $e = $this->getDriverInstance();

        $mockMethodsMaps = [
            'hasDistinct' => true,
            'getOffset' => -1,
            'getLimit' => 0,
            'getIntoTable' => null,
            'getTables' => ['bar'],
            'getJoins' => [],
            'getWheres' => [],
            'getGroupBy' => [],
            'getHaving' => [],
            'getOrder' => [],
            'getColumns' => [
                [
                    'name' => '*',
                    'alias' => null,
                ]
            ],
        ];

        $qs = $this->getQueryStatementInstance($mockMethodsMaps);
        $expected = 'SELECT DISTINCT * FROM "bar"';
        $result = $e->select($qs);
        $this->assertEquals($result, $expected);
    }

    public function testSelectDistinctLimitOffset(): void
    {
        $e = $this->getDriverInstance();

        $mockMethodsMaps = [
            'hasDistinct' => true,
            'getOffset' => 1,
            'getLimit' => 10,
            'getIntoTable' => null,
            'getTables' => ['bar'],
            'getJoins' => [],
            'getWheres' => [],
            'getGroupBy' => [],
            'getHaving' => [],
            'getOrder' => [],
            'getColumns' => [],
        ];

        $qs = $this->getQueryStatementInstance($mockMethodsMaps);
        $expected = 'SELECT DISTINCT * FROM "bar" LIMIT ? OFFSET ?';
        $result = $e->select($qs);
        $this->assertEquals($result, $expected);
    }

    public function testSelectColumnsDistinctLimitOffset(): void
    {
        $e = $this->getDriverInstance();

        $mockMethodsMaps = [
            'hasDistinct' => true,
            'getOffset' => 1,
            'getLimit' => 10,
            'getIntoTable' => null,
            'getTables' => ['bar'],
            'getJoins' => [],
            'getWheres' => [],
            'getGroupBy' => [],
            'getHaving' => [],
            'getOrder' => [],
            'getColumns' => [
                [
                    'name' => 'foo',
                    'alias' => 'f',
                ]
            ],
        ];

        $qs = $this->getQueryStatementInstance($mockMethodsMaps);
        $expected = 'SELECT DISTINCT "foo" AS "f" FROM "bar" LIMIT ? OFFSET ?';
        $result = $e->select($qs);
        $this->assertEquals($result, $expected);
    }

    public function testSelectColumnsDistinctLimitOffsetIntoTable(): void
    {
        $e = $this->getDriverInstance();

        $mockMethodsMaps = [
            'hasDistinct' => true,
            'getOffset' => 1,
            'getLimit' => 10,
            'getIntoTable' => 'baz',
            'getTables' => ['bar'],
            'getJoins' => [],
            'getWheres' => [],
            'getGroupBy' => [],
            'getHaving' => [],
            'getOrder' => [],
            'getColumns' => [
                [
                    'name' => 'foo',
                    'alias' => null,
                ]
            ],
        ];

        $qs = $this->getQueryStatementInstance($mockMethodsMaps);
        $expected = 'SELECT DISTINCT "foo" INTO "baz" FROM "bar" LIMIT ? OFFSET ?';
        $result = $e->select($qs);
        $this->assertEquals($result, $expected);
    }

    public function testSelectFull(): void
    {
        $e = $this->getDriverInstance();

        $join = new Join();
        $cb = function (Join $j) {
            $j->on('foo.id', 'baz.id');
            $j->on('bar.name', 'baz.stat');
            $j->on((new Expression())->column('biz'), 'baz');

            $j->on(function (Join $j1) {
                $j1->on('my_col1', 'my_col2');
            });

            $j->on(function (Expression $exp) {
                $exp->group(function (Expression $ex) {
                    $ex->column('col_closure')->op('*')->value(23);
                });

                $exp->from('sub_tab')->select('foo_sq');
            }, true);
        };

        $havingNested = new HavingStatement();

        $cbh = function (HavingStatement $hs) {
            $hs->having('hs', function (HavingExpression $exp) {
                $exp->avg()->is(10.8);
            });
        };

        $cbh($havingNested);

        $cb($join);

        $sq = new SubQuery();

        $cbs = function (SubQuery $sq) {
            $sq->from('my_tab')
                ->where('created_at')->lt('2020-01-01')
                ->select('id');
        };

        $cbs($sq);

        $mockMethodsMaps = [
            'hasDistinct' => true,
            'getOffset' => 1,
            'getLimit' => 10,
            'getIntoTable' => 'baz',
            'getTables' => ['bar'],
            'getJoins' => [
                [
                    'type' => 'INNER',
                    'table' => ['tjoin'],
                    'join' => $join
                ]
            ],
            'getWheres' => [
                [
                    'type' => 'whereColumn',
                    'column' => 'bazz',
                    'value' => DateTime::createFromFormat('Y-m-d', '2021-03-10'),
                    'operator' => '=',
                    'separator' => 'AND'
                ]
            ],
            'getGroupBy' => ['g_col'],
            'getHaving' => [
                [
                    'type' => 'havingCondition',
                    'aggregate' => 'h_col',
                    'value' => 1,
                    'operator' => '=',
                    'separator' => 'AND'
                ],
                [
                    'type' => 'havingInSelect',
                    'aggregate' => 'bar_aggr',
                    'subquery' => $sq,
                    'separator' => 'OR',
                    'not' => true
                ],
                [
                    'type' => 'havingIn',
                    'aggregate' => 'foo_aggr',
                    'value' => [100, 12],
                    'separator' => 'AND',
                    'not' => false
                ],
                [
                    'type' => 'havingNested',
                    'conditions' => $havingNested->getQueryStatement()->getHaving(),
                    'separator' => 'AND'
                ],
                [
                    'type' => 'havingBetween',
                    'aggregate' => 'hs_betw',
                    'value1' => 1,
                    'value2' => 100,
                    'separator' => 'OR',
                    'not' => false
                ]
            ],
            'getOrder' => [
                [
                    'columns' => ['o_col'],
                    'order' => 'ASC'
                ]
            ],
            'getColumns' => [
                [
                    'name' => 'foo',
                    'alias' => null,
                ]
            ],
        ];

        $qs = $this->getQueryStatementInstance($mockMethodsMaps);
        $expected = 'SELECT DISTINCT "foo" INTO "baz" FROM '
                . '"bar" INNER JOIN "tjoin" ON "foo"."id" = "baz"."id"'
                . ' AND "bar"."name" = "baz"."stat" AND "biz" = "baz"'
                . ' AND ("my_col1" = "my_col2") AND ("col_closure" * ?)'
                . ' (SELECT "foo_sq" FROM "sub_tab")'
                . ' WHERE "bazz" = ? GROUP BY "g_col"'
                . ' HAVING "h_col" = 1'
                . ' OR "bar_aggr" NOT IN (SELECT "id" FROM "my_tab" WHERE "created_at" < ?)'
                . ' AND "foo_aggr" IN (?, ?)'
                . ' AND (AVG("hs") = 10.8)'
                . ' OR "hs_betw" BETWEEN ? AND ?'
                . ' ORDER BY "o_col" ASC LIMIT ? OFFSET ?';
        $result = $e->select($qs);
        $this->assertEquals($result, $expected);
    }


    public function testInsertColumnsIsEmpty(): void
    {
        $e = $this->getDriverInstance();

        $mockMethodsMaps = [
            'getTables' => ['bar'],
            'getValues' => ['foo', 1],
            'getColumns' => [],
        ];

        $qs = $this->getQueryStatementInstance($mockMethodsMaps);
        $expected = 'INSERT INTO "bar" VALUES (?, ?)';
        $result = $e->insert($qs);
        $this->assertEquals($result, $expected);
    }

    public function testInsert(): void
    {
        $e = $this->getDriverInstance();

        $mockMethodsMaps = [
            'getTables' => ['bar'],
            'getValues' => ['foo', 1],
            'getColumns' => [
                [
                    'name' => 'foo',
                    'alias' => null,
                ]
            ],
        ];

        $qs = $this->getQueryStatementInstance($mockMethodsMaps);
        $expected = 'INSERT INTO "bar"("foo") VALUES (?, ?)';
        $result = $e->insert($qs);
        $this->assertEquals($result, $expected);
    }

    public function testUpdate(): void
    {
        $e = $this->getDriverInstance();

        $join = new Join();
        $cb = function (Join $j) {
        };

        $cb($join);

        $whereNested = new WhereStatement();

        $cbw = function (WhereStatement $ws) {
            $ws->where('name')->like('%foo%')
               ->orWhere('status')->is(1);
        };

        $cbw($whereNested);

        $sq = new SubQuery();

        $cbs = function (SubQuery $sq) {
            $sq->from('my_tab')
                ->where('created_at')->lt('2020-01-01')
                ->select('id');
        };

        $cbs($sq);

        $mockMethodsMaps = [
            'getTables' => ['bar' => 'b'],
            'getJoins' => [
                [
                    'type' => 'INNER',
                    'table' => ['tjoin'],
                    'join' => $join
                ]
            ],
            'getWheres' => [
                [
                    'type' => 'whereColumn',
                    'column' => 'bazz',
                    'value' => false,
                    'operator' => '=',
                    'separator' => 'AND'
                ],
                [
                    'type' => 'whereNested',
                    'clause' => $whereNested->getQueryStatement()->getWheres(),
                    'separator' => 'AND'
                ],
                [
                    'type' => 'whereExists',
                    'subquery' => $sq,
                    'separator' => 'AND',
                    'not' => false,
                ],
                [
                    'type' => 'whereInSelect',
                    'subquery' => $sq,
                    'column' => 'in_select',
                    'separator' => 'AND',
                    'not' => false,
                ],
                [
                    'type' => 'whereSubQuery',
                    'subquery' => $sq,
                    'column' => 'wh_select',
                    'separator' => 'AND',
                    'operator' => '=',
                    'not' => false,
                ],
                [
                    'type' => 'whereNop',
                    'column' => 'nop_wh',
                    'separator' => 'OR',
                ]
            ],
            'getColumns' => [
                [
                    'column' => 'foo',
                    'value' => null,
                ],
                [
                    'column' => 'baz',
                    'value' => 1,
                ]
            ],
        ];

        $qs = $this->getQueryStatementInstance($mockMethodsMaps);
        $expected = 'UPDATE "bar" AS "b" INNER JOIN "tjoin" '
                . 'SET "foo" = ?, "baz" = ? WHERE "bazz" = ? '
                . 'AND ("name" LIKE ? OR "status" = ?)'
                . ' AND EXISTS (SELECT "id" FROM "my_tab" WHERE "created_at" < ?)'
                . ' AND "in_select" IN (SELECT "id" FROM "my_tab" WHERE "created_at" < ?)'
                . ' AND "wh_select" = (SELECT "id" FROM "my_tab" WHERE "created_at" < ?)'
                . ' OR "nop_wh"';

        $result = $e->update($qs);
        $this->assertEquals($result, $expected);
    }

    public function testUpdateWithoutColumns(): void
    {
        $e = $this->getDriverInstance();

        $join = new Join();
        $cb = function (Join $j) {
        };

        $cb($join);

        $mockMethodsMaps = [
            'getTables' => ['bar' => 'b'],
            'getJoins' => [
                [
                    'type' => 'INNER',
                    'table' => ['tjoin'],
                    'join' => $join
                ]
            ],
            'getWheres' => [
                [
                    'type' => 'whereColumn',
                    'column' => 'bazz',
                    'value' => 1,
                    'operator' => '=',
                    'separator' => 'AND'
                ],
                [
                    'type' => 'whereColumn',
                    'column' => 'bazi',
                    'value' => 1,
                    'operator' => '=',
                    'separator' => 'AND'
                ],
                [
                    'type' => 'whereIn',
                    'column' => 'id',
                    'value' => [12],
                    'separator' => 'AND',
                    'not' => false
                ],
                [
                    'type' => 'whereNull',
                    'column' => 'name',
                    'separator' => 'AND',
                    'not' => false
                ]
            ],
            'getColumns' => [],
        ];

        $qs = $this->getQueryStatementInstance($mockMethodsMaps);
        $expected = 'UPDATE "bar" AS "b" INNER JOIN "tjoin" WHERE "bazz" = ? AND "bazi" = ?'
                . ' AND "id" IN (?) AND "name" IS NULL';

        $result = $e->update($qs);
        $this->assertEquals($result, $expected);
    }

    public function testDeleteTablesIsEmpty(): void
    {
        $e = $this->getDriverInstance();

        $join = new Join();
        $cb = function (Join $j) {
            $j->on('foo.id', 'baz.id');
        };

        $cb($join);

        $mockMethodsMaps = [
            'getFrom' => ['bar'],
            'getTables' => [],
            'getJoins' => [
                [
                    'type' => 'INNER',
                    'table' => ['tjoin'],
                    'join' => $join
                ]
            ],
            'getWheres' => [
                [
                    'type' => 'whereColumn',
                    'column' => 'bazz',
                    'value' => 1,
                    'operator' => '=',
                    'separator' => 'AND'
                ],
                [
                    'type' => 'whereBetween',
                    'column' => 'name',
                    'separator' => 'AND',
                    'value1' => 1,
                    'value2' => 10,
                    'not' => false
                ],
                [
                    'type' => 'whereLike',
                    'column' => 'status',
                    'separator' => 'OR',
                    'pattern' => '%e%',
                    'not' => false
                ]
            ]
        ];

        $qs = $this->getQueryStatementInstance($mockMethodsMaps);
        $expected = 'DELETE FROM "bar" INNER JOIN '
                . '"tjoin" ON "foo"."id" = "baz"."id" WHERE "bazz" = ?'
                . ' AND "name" BETWEEN ? AND ?'
                . ' OR "status" LIKE ?';


        $result = $e->delete($qs);
        $this->assertEquals($result, $expected);
    }

    public function testDelete(): void
    {
        $e = $this->getDriverInstance();

        $join = new Join();
        $cb = function (Join $j) {
            $j->on('foo.id', 'baz.id');
        };

        $cb($join);

        $mockMethodsMaps = [
            'getFrom' => ['bar'],
            'getTables' => ['foo'],
            'getJoins' => [
                [
                    'type' => 'INNER',
                    'table' => ['tjoin'],
                    'join' => $join
                ]
            ],
            'getWheres' => [
                [
                    'type' => 'whereColumn',
                    'column' => 'bazz',
                    'value' => 1,
                    'operator' => '=',
                    'separator' => 'AND'
                ]
            ]
        ];

        $qs = $this->getQueryStatementInstance($mockMethodsMaps);
        $expected = 'DELETE "foo" FROM "bar" '
                . 'INNER JOIN "tjoin" ON "foo"."id" = "baz"."id" WHERE "bazz" = ?';

        $result = $e->delete($qs);
        $this->assertEquals($result, $expected);
    }

    public function testDateFormat(): void
    {
        $e = $this->getDriverInstance();

        $this->assertEquals('Y-m-d H:i:s', $e->getDateFormat());

        $e->setDateFormat('Y-m-d');

        $this->assertEquals('Y-m-d', $e->getDateFormat());
    }

    public function testOptions(): void
    {
        $options = [
            'dateFormat' => 'Y-m-d'
        ];


        $e = $this->getDriverInstance();

        $this->assertEquals('Y-m-d H:i:s', $e->getDateFormat());

        $e->setOptions($options);

        $this->assertEquals('Y-m-d', $e->getDateFormat());
    }

    public function testGetDatabaseName(): void
    {

        $e = $this->getDriverInstance();

        $infos = $e->getDatabaseName();

        $this->assertIsArray($infos);
        $this->assertArrayHasKey('sql', $infos);
        $this->assertArrayHasKey('params', $infos);

        $this->assertEquals('SELECT database()', $infos['sql']);
        $this->assertEmpty($infos['params']);
    }

    public function testRenameTable(): void
    {

        $e = $this->getDriverInstance();

        $current = 'foo';
        $new = 'bar';

        $infos = $e->renameTable($current, $new);

        $this->assertIsArray($infos);
        $this->assertArrayHasKey('sql', $infos);
        $this->assertArrayHasKey('params', $infos);

        $expectedSql = sprintf('RENAME TABLE "%s" TO "%s"', $current, $new);

        $this->assertEquals($expectedSql, $infos['sql']);
        $this->assertEmpty($infos['params']);
    }

    public function testGetTables(): void
    {

        $e = $this->getDriverInstance();

        $database = 'foo';

        $infos = $e->getTables($database);

        $this->assertIsArray($infos);
        $this->assertArrayHasKey('sql', $infos);
        $this->assertArrayHasKey('params', $infos);

        $expectedSql = sprintf(
            'SELECT "%s" FROM "%s"."%s" WHERE '
                . 'table_type = ? AND table_schema = ? ORDER BY "%s" ASC',
            'table_name',
            'information_schema',
            'tables',
            'table_name',
        );

        $this->assertEquals($expectedSql, $infos['sql']);
        $this->assertCount(2, $infos['params']);
        $this->assertContains('BASE TABLE', $infos['params']);
        $this->assertContains($database, $infos['params']);
    }

    public function testGetColumns(): void
    {

        $e = $this->getDriverInstance();

        $database = 'foo';
        $table = 'bar';

        $infos = $e->getColumns($database, $table);

        $this->assertIsArray($infos);
        $this->assertArrayHasKey('sql', $infos);
        $this->assertArrayHasKey('params', $infos);

        $expectedSql = sprintf(
            'SELECT "%s" AS "%s", "%s" AS "%s" '
                . 'FROM "%s"."%s" WHERE "%s" = ? AND "%s" = ? ORDER BY "%s" ASC',
            'column_name',
            'name',
            'column_type',
            'type',
            'information_schema',
            'columns',
            'table_schema',
            'table_name',
            'ordinal_position',
        );

        $this->assertEquals($expectedSql, $infos['sql']);
        $this->assertCount(2, $infos['params']);
        $this->assertContains($table, $infos['params']);
        $this->assertContains($database, $infos['params']);
    }

    public function testCreateSimple(): void
    {

        $e = $this->getDriverInstance();

        $ccIntMockMethodsMaps = [
            'getName' => 'bar',
            'getType' => 'integer',
        ];


        $columns = [
            'bar' => $this->getCreateColumnInstance($ccIntMockMethodsMaps),
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

        $expectedSql = 'CREATE TABLE "bar"(
"bar" INT)
';

        $this->assertEquals($expectedSql, $infos[0]['sql']);
        $this->assertEmpty($infos[0]['params']);
    }

    public function testCreateFull(): void
    {

        $e = $this->getDriverInstance();

        $ccIntMockMethodsMaps = [
           'getName' => 'bar',
           'getType' => 'integer',
        ];

        $ccFloatMockMethodsMaps = [
           'getName' => 'f_bar',
           'getType' => 'float',
        ];

        $ccDoubleMockMethodsMaps = [
           'getName' => 'double_bar',
           'getType' => 'double',
        ];

        $ccDecimalMockMethodsMaps = [
           'getName' => 'deci_bar',
           'getType' => 'decimal',
        ];

        $ccBoolMockMethodsMaps = [
           'getName' => 'bool_bar',
           'getType' => 'boolean',
        ];

        $ccBinaryMockMethodsMaps = [
           'getName' => 'bin_bar',
           'getType' => 'binary',
        ];

        $ccTextMockMethodsMaps = [
           'getName' => 'txt_bar',
           'getType' => 'text',
        ];

        $ccStrMockMethodsMaps = [
           'getName' => 'str_bar',
           'getType' => 'string',
        ];

        $ccFixedMockMethodsMaps = [
           'getName' => 'fix_bar',
           'getType' => 'fixed',
        ];

        $ccTimeMockMethodsMaps = [
           'getName' => 'time_bar',
           'getType' => 'time',
        ];

        $ccTimestampMockMethodsMaps = [
           'getName' => 'timestamp_bar',
           'getType' => 'timestamp',
        ];

        $ccDateMockMethodsMaps = [
           'getName' => 'date_bar',
           'getType' => 'date',
        ];

        $ccDatetimeMockMethodsMaps = [
           'getName' => 'datet_bar',
           'getType' => 'datetime',
        ];
        
        $ccEnumMockMethodsMaps = [
           'getName' => 'enum_bar',
           'getType' => 'enum',
        ];

        $columns = [
           'bar' => $this->getCreateColumnInstance($ccIntMockMethodsMaps),
           'f_bar' => $this->getCreateColumnInstance($ccFloatMockMethodsMaps),
           'double_bar' => $this->getCreateColumnInstance($ccDoubleMockMethodsMaps),
           'deci_bar' => $this->getCreateColumnInstance($ccDecimalMockMethodsMaps),
           'bool_bar' => $this->getCreateColumnInstance($ccBoolMockMethodsMaps),
           'bin_bar' => $this->getCreateColumnInstance($ccBinaryMockMethodsMaps),
           'txt_bar' => $this->getCreateColumnInstance($ccTextMockMethodsMaps),
           'str_bar' => $this->getCreateColumnInstance($ccStrMockMethodsMaps),
           'fix_bar' => $this->getCreateColumnInstance($ccFixedMockMethodsMaps),
           'time_bar' => $this->getCreateColumnInstance($ccTimeMockMethodsMaps),
           'timestamp_bar' => $this->getCreateColumnInstance($ccTimestampMockMethodsMaps),
           'date_bar' => $this->getCreateColumnInstance($ccDateMockMethodsMaps),
           'datet_bar' => $this->getCreateColumnInstance($ccDatetimeMockMethodsMaps),
           'enum_bar' => $this->getCreateColumnInstance($ccEnumMockMethodsMaps),
        ];


        $fkMockMethodsMaps = [
           'getColumns' => ['bar'],
           'getReferenceTable' => 'bazz',
           'getReferenceColumns' => ['foo_bar'],
           'getActions' => ['ON DELETE' => 'CASCADE'],
        ];

        $fk = $this->getForeignKeyInstance($fkMockMethodsMaps);


        $ctMockMethodsMaps = [
           'getTableName' => 'bar',
           'getEngine' => 'INNODB',
           'getColumns' => $columns,
           'getIndexes' => [
              'foo_ik_bar' => ['bar']
           ],
           'getForeignKeys' => [
               'foo_fk_bar' => $fk
           ],
           'getUniqueKeys' => [
               'foo_uk_bar' => ['bar']
           ],
           'getPrimaryKey' => [
               'name' => 'foo_pk_bar',
               'columns' => ['bar']
           ],
        ];

        $ct = $this->getCreateTableInstance($ctMockMethodsMaps);

        $infos = $e->create($ct);

        $this->assertCount(2, $infos);
        $this->assertIsArray($infos[0]);
        $this->assertArrayHasKey('sql', $infos[0]);
        $this->assertArrayHasKey('params', $infos[0]);

        $this->assertIsArray($infos[1]);
        $this->assertArrayHasKey('sql', $infos[1]);
        $this->assertArrayHasKey('params', $infos[1]);

        $expectedSql = 'CREATE TABLE "bar"(
"bar" INT,
"f_bar" FLOAT,
"double_bar" DOUBLE,
"deci_bar" DECIMAL,
"bool_bar" BOOLEAN,
"bin_bar" BLOB,
"txt_bar" TEXT,
"str_bar" VARCHAR(255),
"fix_bar" CHAR(255),
"time_bar" TIME,
"timestamp_bar" TIMESTAMP,
"date_bar" DATE,
"datet_bar" DATETIME,
"enum_bar" ENUM,
CONSTRAINT "foo_pk_bar" PRIMARY KEY ("bar"),
CONSTRAINT "foo_uk_bar" UNIQUE ("bar"),
CONSTRAINT "foo_fk_bar" FOREIGN KEY ("bar") REFERENCES "bazz" ("foo_bar") ON DELETE CASCADE)
 ENGINE = INNODB';

        $expectedSqlIndexes = 'CREATE INDEX "foo_ik_bar" ON "bar"("bar")';

        $this->assertEquals($expectedSql, $infos[0]['sql']);
        $this->assertEquals($expectedSqlIndexes, $infos[1]['sql']);
        $this->assertEmpty($infos[0]['params']);
        $this->assertEmpty($infos[1]['params']);
    }

    public function testAlterFull(): void
    {

        $e = $this->getDriverInstance();

        $fkMockMethodsMaps = [
            'getColumns' => ['bar'],
            'getReferenceTable' => 'bazz',
            'getReferenceColumns' => ['foo_bar'],
            'getActions' => ['ON DELETE' => 'CASCADE'],
        ];

        $fk = $this->getForeignKeyInstance($fkMockMethodsMaps);

        $atMockMethodsMaps = [
            'getCommands' => [
                [
                    'type' => 'setDefaultValue',
                    'data' => [
                        'column' => 'bar',
                        'value' => 100,
                    ]
                ],
                [
                    'type' => 'setDefaultValue',
                    'data' => [
                        'column' => 'bar_str',
                        'value' => 'string',
                    ]
                ],
                [
                    'type' => 'setDefaultValue',
                    'data' => [
                        'column' => 'bar_bool',
                        'value' => true,
                    ]
                ],
                [
                    'type' => 'dropDefaultValue',
                    'data' => 'baz'
                ],
                [
                    'type' => 'dropPrimaryKey',
                    'data' => 'baz_pk_col'
                ],
                [
                    'type' => 'dropForeignKey',
                    'data' => 'baz_fk_col'
                ],
                [
                    'type' => 'dropUniqueKey',
                    'data' => 'baz_uk_col'
                ],
                [
                    'type' => 'dropIndex',
                    'data' => 'baz_ik_col'
                ],
                [
                    'type' => 'dropColumn',
                    'data' => 'baz_col'
                ],
                [
                    'type' => 'addPrimary',
                    'data' => [
                        'name' => 'foo_pk_col',
                        'columns' => ['foo_col']
                    ]
                ],
                [
                    'type' => 'addUnique',
                    'data' => [
                        'name' => 'foo_uk_col',
                        'columns' => ['foo_col']
                    ]
                ],
                [
                    'type' => 'addForeign',
                    'data' => [
                        'name' => 'foo_fk_col',
                        'foreign' => $fk
                    ]
                ],
                [
                    'type' => 'addIndex',
                    'data' => [
                        'name' => 'foo_ik_col',
                        'columns' => ['foo_col']
                    ]
                ],
                [
                    'type' => 'setDefaultValue',
                    'data' => [
                        'column' => 'bar',
                        'value' => null,
                    ]
                ],
            ],
            'getTableName' => 'foo'
        ];

        $at = $this->getAlterTableInstance($atMockMethodsMaps);

        $infos = $e->alter($at);

        $this->assertCount(14, $infos);
        $this->assertIsArray($infos[0]);
        $this->assertArrayHasKey('sql', $infos[0]);
        $this->assertArrayHasKey('params', $infos[0]);
        $this->assertArrayHasKey('sql', $infos[1]);
        $this->assertArrayHasKey('params', $infos[1]);
        $this->assertArrayHasKey('sql', $infos[2]);
        $this->assertArrayHasKey('params', $infos[2]);
        $this->assertArrayHasKey('sql', $infos[3]);
        $this->assertArrayHasKey('params', $infos[3]);
        $this->assertArrayHasKey('sql', $infos[4]);
        $this->assertArrayHasKey('params', $infos[4]);
        $this->assertArrayHasKey('sql', $infos[5]);
        $this->assertArrayHasKey('params', $infos[5]);
        $this->assertArrayHasKey('sql', $infos[6]);
        $this->assertArrayHasKey('params', $infos[6]);
        $this->assertArrayHasKey('sql', $infos[7]);
        $this->assertArrayHasKey('params', $infos[7]);
        $this->assertArrayHasKey('sql', $infos[8]);
        $this->assertArrayHasKey('params', $infos[8]);
        $this->assertArrayHasKey('sql', $infos[9]);
        $this->assertArrayHasKey('params', $infos[9]);
        $this->assertArrayHasKey('sql', $infos[10]);
        $this->assertArrayHasKey('params', $infos[10]);
        $this->assertArrayHasKey('sql', $infos[11]);
        $this->assertArrayHasKey('params', $infos[11]);
        $this->assertArrayHasKey('sql', $infos[12]);
        $this->assertArrayHasKey('params', $infos[12]);
        $this->assertArrayHasKey('sql', $infos[13]);
        $this->assertArrayHasKey('params', $infos[13]);

        $expectedSetDefaultIntSql = 'ALTER TABLE "foo" ALTER COLUMN "bar" SET DEFAULT (100)';
        $expectedDropDefaultSql = 'ALTER TABLE "foo" ALTER COLUMN "baz" DROP DEFAULT';
        $expectedSetDefaultStrSql = 'ALTER TABLE "foo" ALTER COLUMN "bar_str" SET DEFAULT (\'string\')';
        $expectedSetDefaultBoolSql = 'ALTER TABLE "foo" ALTER COLUMN "bar_bool" SET DEFAULT (1)';
        $expectedSetDefaultNullSql = 'ALTER TABLE "foo" ALTER COLUMN "bar" SET DEFAULT (NULL)';
        $expectedDropPkSql = 'ALTER TABLE "foo" DROP CONSTRAINT "baz_pk_col"';
        $expectedDropIkSql = 'DROP INDEX "foo"."baz_ik_col"';
        $expectedDropFkSql = 'ALTER TABLE "foo" DROP CONSTRAINT "baz_fk_col"';
        $expectedDropUkSql = 'ALTER TABLE "foo" DROP CONSTRAINT "baz_uk_col"';
        $expectedDropColSql = 'ALTER TABLE "foo" DROP COLUMN "baz_col"';
        $expectedAddPkSql = 'ALTER TABLE "foo" ADD CONSTRAINT "foo_pk_col" PRIMARY KEY ("foo_col")';
        $expectedAddUkSql = 'ALTER TABLE "foo" ADD CONSTRAINT "foo_uk_col" UNIQUE ("foo_col")';
        $expectedAddFkSql = 'ALTER TABLE "foo" ADD CONSTRAINT "foo_fk_col" '
                . 'FOREIGN KEY ("bar") REFERENCES "bazz" ("foo_bar")';
        $expectedAddIkSql = 'CREATE INDEX "foo_ik_col" ON "foo" ("foo_col")';

        $this->assertEquals($expectedSetDefaultIntSql, $infos[0]['sql']);
        $this->assertEquals($expectedSetDefaultStrSql, $infos[1]['sql']);
        $this->assertEquals($expectedSetDefaultBoolSql, $infos[2]['sql']);
        $this->assertEquals($expectedDropDefaultSql, $infos[3]['sql']);
        $this->assertEquals($expectedDropPkSql, $infos[4]['sql']);
        $this->assertEquals($expectedDropFkSql, $infos[5]['sql']);
        $this->assertEquals($expectedDropUkSql, $infos[6]['sql']);
        $this->assertEquals($expectedDropIkSql, $infos[7]['sql']);
        $this->assertEquals($expectedDropColSql, $infos[8]['sql']);
        $this->assertEquals($expectedAddPkSql, $infos[9]['sql']);
        $this->assertEquals($expectedAddUkSql, $infos[10]['sql']);
        $this->assertEquals($expectedAddFkSql, $infos[11]['sql']);
        $this->assertEquals($expectedAddIkSql, $infos[12]['sql']);
        $this->assertEquals($expectedSetDefaultNullSql, $infos[13]['sql']);

        $this->assertEmpty($infos[0]['params']);
        $this->assertEmpty($infos[1]['params']);
        $this->assertEmpty($infos[2]['params']);
        $this->assertEmpty($infos[3]['params']);
        $this->assertEmpty($infos[4]['params']);
        $this->assertEmpty($infos[5]['params']);
        $this->assertEmpty($infos[6]['params']);
        $this->assertEmpty($infos[7]['params']);
        $this->assertEmpty($infos[8]['params']);
        $this->assertEmpty($infos[9]['params']);
        $this->assertEmpty($infos[10]['params']);
        $this->assertEmpty($infos[11]['params']);
        $this->assertEmpty($infos[12]['params']);
        $this->assertEmpty($infos[13]['params']);
    }

    public function testDrop(): void
    {
        $e = $this->getDriverInstance();

        $table = 'foo';

        $infos = $e->drop($table);

        $this->assertCount(2, $infos);
        $this->assertIsArray($infos);
        $this->assertArrayHasKey('sql', $infos);
        $this->assertArrayHasKey('params', $infos);

        $expectedSql = 'DROP TABLE "foo"';


        $this->assertEquals($expectedSql, $infos['sql']);
        $this->assertEmpty($infos['params']);
    }

    public function testTruncate(): void
    {
        $e = $this->getDriverInstance();

        $table = 'foo';

        $infos = $e->truncate($table);

        $this->assertCount(2, $infos);
        $this->assertIsArray($infos);
        $this->assertArrayHasKey('sql', $infos);
        $this->assertArrayHasKey('params', $infos);

        $expectedSql = 'TRUNCATE TABLE "foo"';


        $this->assertEquals($expectedSql, $infos['sql']);
        $this->assertEmpty($infos['params']);
    }

    public function testRenameAddModifyColumn(): void
    {

        $from = 'old_col';
        $to = 'new_col';

        $acIntMockMethodsMaps = [
            'getName' => 'bar',
            'getType' => 'integer',
        ];

        $col = $this->getAlterColumnInstance($acIntMockMethodsMaps);

        $col->set('autoincrement', true);

        $atMockMethodsMaps = [
            'getCommands' => [
                [
                    'type' => 'renameColumn',
                    'data' => [
                        'from' => $from,
                        'column' => $this->getAlterColumnInstance($acIntMockMethodsMaps),
                    ]
                ],
                [
                    'type' => 'modifyColumn',
                    'data' => $this->getAlterColumnInstance($acIntMockMethodsMaps),
                ],
                [
                    'type' => 'addColumn',
                    'data' => $col,
                ]
            ],
            'getTableName' => 'foo'
        ];

        $e = $this->getDriverInstance();

        $at = $this->getAlterTableInstance($atMockMethodsMaps);

        $infos = $e->alter($at);


        $this->assertCount(2, $infos);
        $this->assertIsArray($infos[0]);
        $this->assertIsArray($infos[1]);

        $this->assertArrayHasKey('sql', $infos[0]);
        $this->assertArrayHasKey('sql', $infos[1]);
        $this->assertArrayHasKey('params', $infos[0]);
        $this->assertArrayHasKey('params', $infos[1]);

        $expectedSql = 'ALTER TABLE "foo" MODIFY COLUMN "bar" INT';
        $expectedAddColSql = 'ALTER TABLE "foo" ADD COLUMN "bar" INT AUTO_INCREMENT';
        $this->assertEquals($expectedSql, $infos[0]['sql']);
        $this->assertEquals($expectedAddColSql, $infos[1]['sql']);
        $this->assertEmpty($infos[0]['params']);
        $this->assertEmpty($infos[1]['params']);
    }

    public function testAlter(): void
    {

        $e = $this->getDriverInstance();

        $fkMockMethodsMaps = [
            'getColumns' => ['bar'],
            'getReferenceTable' => 'bazz',
            'getReferenceColumns' => ['foo_bar'],
            'getActions' => ['ON DELETE' => 'CASCADE'],
        ];

        $fk = $this->getForeignKeyInstance($fkMockMethodsMaps);

        $atMockMethodsMaps = [
            'getCommands' => [
                [
                    'type' => 'setDefaultValue',
                    'data' => [
                        'column' => 'bar',
                        'value' => 100,
                    ]
                ],
                [
                    'type' => 'dropDefaultValue',
                    'data' => 'baz'
                ],
                [
                    'type' => 'dropPrimaryKey',
                    'data' => 'baz_pk_col'
                ],
                [
                    'type' => 'dropForeignKey',
                    'data' => 'baz_fk_col'
                ],
                [
                    'type' => 'dropUniqueKey',
                    'data' => 'baz_uk_col'
                ],
                [
                    'type' => 'dropIndex',
                    'data' => 'baz_ik_col'
                ],
                [
                    'type' => 'dropColumn',
                    'data' => 'baz_col'
                ],
                [
                    'type' => 'addPrimary',
                    'data' => [
                        'name' => 'foo_pk_col',
                        'columns' => ['foo_col']
                    ]
                ],
                [
                    'type' => 'addUnique',
                    'data' => [
                        'name' => 'foo_uk_col',
                        'columns' => ['foo_col']
                    ]
                ],
                [
                    'type' => 'addForeign',
                    'data' => [
                        'name' => 'foo_fk_col',
                        'foreign' => $fk
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

        $this->assertCount(11, $infos);
        $this->assertIsArray($infos[0]);
        $this->assertArrayHasKey('sql', $infos[0]);
        $this->assertArrayHasKey('params', $infos[0]);
        $this->assertArrayHasKey('sql', $infos[1]);
        $this->assertArrayHasKey('params', $infos[1]);
        $this->assertArrayHasKey('sql', $infos[2]);
        $this->assertArrayHasKey('params', $infos[2]);
        $this->assertArrayHasKey('sql', $infos[3]);
        $this->assertArrayHasKey('params', $infos[3]);
        $this->assertArrayHasKey('sql', $infos[4]);
        $this->assertArrayHasKey('params', $infos[4]);
        $this->assertArrayHasKey('sql', $infos[5]);
        $this->assertArrayHasKey('params', $infos[5]);
        $this->assertArrayHasKey('sql', $infos[6]);
        $this->assertArrayHasKey('params', $infos[6]);
        $this->assertArrayHasKey('sql', $infos[7]);
        $this->assertArrayHasKey('params', $infos[7]);
        $this->assertArrayHasKey('sql', $infos[8]);
        $this->assertArrayHasKey('params', $infos[8]);
        $this->assertArrayHasKey('sql', $infos[9]);
        $this->assertArrayHasKey('params', $infos[9]);
        $this->assertArrayHasKey('sql', $infos[10]);
        $this->assertArrayHasKey('params', $infos[10]);

        $expectedSql = 'ALTER TABLE "foo" ALTER COLUMN "bar" SET DEFAULT (100)';
        $expectedDropDefaultSql = 'ALTER TABLE "foo" ALTER COLUMN "baz" DROP DEFAULT';
        $expectedDropPkSql = 'ALTER TABLE "foo" DROP CONSTRAINT "baz_pk_col"';
        $expectedDropIkSql = 'DROP INDEX "foo"."baz_ik_col"';
        $expectedDropFkSql = 'ALTER TABLE "foo" DROP CONSTRAINT "baz_fk_col"';
        $expectedDropUkSql = 'ALTER TABLE "foo" DROP CONSTRAINT "baz_uk_col"';
        $expectedDropColSql = 'ALTER TABLE "foo" DROP COLUMN "baz_col"';
        $expectedAddPkSql = 'ALTER TABLE "foo" ADD CONSTRAINT "foo_pk_col" PRIMARY KEY ("foo_col")';
        $expectedAddUkSql = 'ALTER TABLE "foo" ADD CONSTRAINT "foo_uk_col" UNIQUE ("foo_col")';
        $expectedAddFkSql = 'ALTER TABLE "foo" ADD CONSTRAINT "foo_fk_col" '
                . 'FOREIGN KEY ("bar") REFERENCES "bazz" ("foo_bar")';
        $expectedAddIkSql = 'CREATE INDEX "foo_ik_col" ON "foo" ("foo_col")';

        $this->assertEquals($expectedSql, $infos[0]['sql']);
        $this->assertEquals($expectedDropDefaultSql, $infos[1]['sql']);
        $this->assertEquals($expectedDropPkSql, $infos[2]['sql']);
        $this->assertEquals($expectedDropFkSql, $infos[3]['sql']);
        $this->assertEquals($expectedDropUkSql, $infos[4]['sql']);
        $this->assertEquals($expectedDropIkSql, $infos[5]['sql']);
        $this->assertEquals($expectedDropColSql, $infos[6]['sql']);
        $this->assertEquals($expectedAddPkSql, $infos[7]['sql']);
        $this->assertEquals($expectedAddUkSql, $infos[8]['sql']);
        $this->assertEquals($expectedAddFkSql, $infos[9]['sql']);
        $this->assertEquals($expectedAddIkSql, $infos[10]['sql']);
        $this->assertEmpty($infos[0]['params']);
        $this->assertEmpty($infos[1]['params']);
        $this->assertEmpty($infos[2]['params']);
        $this->assertEmpty($infos[3]['params']);
        $this->assertEmpty($infos[4]['params']);
        $this->assertEmpty($infos[5]['params']);
        $this->assertEmpty($infos[6]['params']);
        $this->assertEmpty($infos[7]['params']);
        $this->assertEmpty($infos[8]['params']);
        $this->assertEmpty($infos[9]['params']);
        $this->assertEmpty($infos[10]['params']);
    }

    private function getDriverInstance(): Driver
    {
        $cnx = new Connection('MySQL');

        return new Driver($cnx);
    }

    private function getQueryStatementInstance(array $mockInfos = []): QueryStatement
    {
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        foreach ($mockInfos as $method => $returnValue) {
            $qs->expects($this->any())
                ->method($method)
                ->will($this->returnValue($returnValue));
        }

        return $qs;
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

    private function getForeignKeyInstance(array $mockInfos = []): ForeignKey
    {
        /** @var ForeignKey $fk */
        $fk = $this->getMockBuilder(ForeignKey::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        foreach ($mockInfos as $method => $returnValue) {
            $fk->expects($this->any())
                ->method($method)
                ->will($this->returnValue($returnValue));
        }

        return $fk;
    }
}
