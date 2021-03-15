<?php

declare(strict_types=1);

namespace Platine\Test\Database;

use DateTime;
use Platine\Database\Driver\Oracle;
use Platine\Database\Query\Expression;
use Platine\Database\Query\HavingExpression;
use Platine\Database\Query\HavingStatement;
use Platine\Database\Query\Join;
use Platine\Database\Query\QueryStatement;
use Platine\Database\Query\SubQuery;
use Platine\Database\Query\Where;
use Platine\Database\Query\WhereStatement;
use Platine\Database\Schema\AlterColumn;
use Platine\Database\Schema\AlterTable;
use Platine\Database\Schema\CreateColumn;
use Platine\Database\Schema\CreateTable;
use Platine\PlatineTestCase;
use Platine\Test\Fixture\Connection;

/**
 * Oracle class tests
 *
 * @group core
 * @group database
 */
class OracleTest extends PlatineTestCase
{
    public function testSelectDefault(): void
    {
        $e = $this->getOracleInstance();

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
        $expected = 'SELECT * FROM "BAR"';
        $result = $e->select($qs);
        $this->assertEquals($result, $expected);
    }

    public function testSelectWhereValueIsStringAndIsColumn(): void
    {
        $column = 'value_str_is_column';
        $value = 'baz';
        $separator = 'AND';

        $e = $this->getOracleInstance();

        $qsMockMethods = $this->getClassMethodsToMock(QueryStatement::class, [
            'addWhere',
            'getWheres',
            'closureToExpression',
           ]);

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->onlyMethods($qsMockMethods)
                    ->getMock();

        /** @var WhereStatement $ws */
        $ws = $this->getMockBuilder(WhereStatement::class)
                    ->getMock();

        $w = new Where($ws, $qs);
        $w->init($column, $separator);
        $w->isNot($value, true);

        $expected = 'SELECT * FROM  WHERE "VALUE_STR_IS_COLUMN" != "BAZ" OFFSET ?';
        $result = $e->select($qs);
        $this->assertEquals($result, $expected);
    }

    public function testSelectDistinct(): void
    {
        $e = $this->getOracleInstance();

        $mockMethodsMaps = [
            'hasDistinct' => true,
            'getOffset' => -1,
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
                    'name' => '*',
                    'alias' => null,
                ]
            ],
        ];

        $qs = $this->getQueryStatementInstance($mockMethodsMaps);
        $expected = 'SELECT * FROM (SELECT DISTINCT * FROM "BAR") A1 WHERE ROWNUM <= 10';
        $result = $e->select($qs);
        $this->assertEquals($result, $expected);
    }

    public function testSelectFull(): void
    {
        $e = $this->getOracleInstance();

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
        $expected = 'SELECT * FROM '
                . '(SELECT A1.*, ROWNUM AS P_ROWNUM FROM '
                . '(SELECT DISTINCT "FOO" FROM "BAR" INNER JOIN "TJOIN" ON "FOO"."ID" = "BAZ"."ID" '
                . 'AND "BAR"."NAME" = "BAZ"."STAT" AND "BIZ" = "BAZ" AND ("MY_COL1" = "MY_COL2") '
                . 'AND ("COL_CLOSURE" * ?) '
                . '(SELECT "FOO_SQ" FROM "SUB_TAB") '
                . 'WHERE "BAZZ" = ? GROUP BY "G_COL" '
                . 'HAVING "H_COL" = 1 OR "BAR_AGGR" NOT IN '
                . '(SELECT "ID" FROM "MY_TAB" WHERE "CREATED_AT" < ?) '
                . 'AND "FOO_AGGR" IN (?, ?) AND (AVG("HS") = 10.8) '
                . 'OR "HS_BETW" BETWEEN ? AND ? ORDER BY "O_COL" ASC) '
                . 'A1 WHERE ROWNUM <= 11) '
                . 'WHERE P_ROWNUM >= 2';
        $result = $e->select($qs);
        $this->assertEquals($result, $expected);
    }

    public function testGetDatabaseName(): void
    {

        $e = $this->getOracleInstance();

        $infos = $e->getDatabaseName();

        $this->assertIsArray($infos);
        $this->assertArrayHasKey('sql', $infos);
        $this->assertArrayHasKey('params', $infos);

        $this->assertEquals('SELECT user FROM dual', $infos['sql']);
        $this->assertEmpty($infos['params']);
    }

    public function testGetTables(): void
    {

        $e = $this->getOracleInstance();

        $database = 'foo';

        $infos = $e->getTables($database);

        $this->assertIsArray($infos);
        $this->assertArrayHasKey('sql', $infos);
        $this->assertArrayHasKey('params', $infos);

        $expectedSql = 'SELECT "TABLE_NAME" FROM "ALL_TABLES" WHERE owner = ?  ORDER BY "TABLE_NAME" ASC';

        $this->assertEquals($expectedSql, $infos['sql']);
        $this->assertCount(1, $infos['params']);
        $this->assertContains($database, $infos['params']);
    }

    public function testGetColumns(): void
    {

        $e = $this->getOracleInstance();

        $database = 'foo';
        $table = 'bar';

        $infos = $e->getColumns($database, $table);

        $this->assertIsArray($infos);
        $this->assertArrayHasKey('sql', $infos);
        $this->assertArrayHasKey('params', $infos);

        $expectedSql = 'SELECT "COLUMN_NAME" AS "NAME", "DATA_TYPE" AS "TYPE" '
                . 'FROM "ALL_TAB_COLUMNS" WHERE LOWER("OWNER") = ? '
                . 'AND LOWER("TABLE_NAME") = ? '
                . 'ORDER BY "COLUMN_ID" ASC';

        $this->assertEquals($expectedSql, $infos['sql']);
        $this->assertCount(2, $infos['params']);
        $this->assertEquals($database, $infos['params'][0]);
        $this->assertEquals($table, $infos['params'][1]);
    }


    public function testCreateAllDataTypes(): void
    {

        $e = $this->getOracleInstance();

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

        //Text
        $ccTextMockMethodsMaps = [
            'getName' => 'col_text',
            'getType' => 'text',
        ];

        $ccTextMedMockMethodsMaps = [
            'getName' => 'col_medium_text',
            'getType' => 'text',
        ];

        $ccTextTinyMockMethodsMaps = [
            'getName' => 'col_tiny_text',
            'getType' => 'text',
        ];

        $ccTextSmallMockMethodsMaps = [
            'getName' => 'col_small_text',
            'getType' => 'text',
        ];

        $ccTextBigMockMethodsMaps = [
            'getName' => 'col_big_text',
            'getType' => 'text',
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

        //Boolean
        $ccBoolMockMethodsMaps = [
            'getName' => 'col_bool',
            'getType' => 'boolean',
        ];

        //Double
        $ccDoubleMockMethodsMaps = [
            'getName' => 'col_double',
            'getType' => 'double',
        ];

         //String
        $ccStringMockMethodsMaps = [
            'getName' => 'col_str',
            'getType' => 'string',
        ];

        //Time
        $ccTimeMockMethodsMaps = [
            'getName' => 'col_time',
            'getType' => 'time',
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

        //Integer
        $int = $this->getCreateColumnInstance($ccIntMockMethodsMaps);
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

        //Text
        $text = $this->getCreateColumnInstance($ccTextMockMethodsMaps);
        $text->set('size', 'normal');

        $textTiny = $this->getCreateColumnInstance($ccTextTinyMockMethodsMaps);
        $textTiny->set('size', 'tiny');

        $textSmall = $this->getCreateColumnInstance($ccTextSmallMockMethodsMaps);
        $textSmall->set('size', 'small');

        $textMedium = $this->getCreateColumnInstance($ccTextMedMockMethodsMaps);
        $textMedium->set('size', 'medium');

        $textBig = $this->getCreateColumnInstance($ccTextBigMockMethodsMaps);
        $textBig->set('size', 'big');

        //Boolean
        $bool = $this->getCreateColumnInstance($ccBoolMockMethodsMaps);

        //Double
        $double = $this->getCreateColumnInstance($ccDoubleMockMethodsMaps);

        //String
        $str = $this->getCreateColumnInstance($ccStringMockMethodsMaps);

        //Time
        $time = $this->getCreateColumnInstance($ccTimeMockMethodsMaps);

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

        $columns = [
            'col_int' => $int,
            'col_tiny_int' => $tiny,
            'col_small_int' => $small,
            'col_medium_int' => $medium,
            'col_big_int' => $big,

            'col_text' => $text,
            'col_tiny_text' => $textTiny,
            'col_small_text' => $textSmall,
            'col_medium_text' => $textMedium,
            'col_big_text' => $textBig,

            'col_binary' => $binary,
            'col_tiny_binary' => $binaryTiny,
            'col_small_binary' => $binarySmall,
            'col_medium_binary' => $binaryMedium,
            'col_big_binary' => $binaryBig,

            'col_dec_sim' => $decSimple,
            'col_dec_len' => $decLength,
            'col_dec_pre' => $decPrecision,

            'col_bool' => $bool,

            'col_double' => $double,

            'col_str' => $str,

            'col_time' => $time,

            'col_datetime' => $datetime,
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

        $expectedSql = 'CREATE TABLE "BAR"(
"COL_INT" NUMBER(10) COMMENT \'simple int column\',
"COL_TINY_INT" NUMBER(3),
"COL_SMALL_INT" NUMBER(5),
"COL_MEDIUM_INT" NUMBER(7),
"COL_BIG_INT" NUMBER(19),
"COL_TEXT" CLOB,
"COL_TINY_TEXT" VARCHAR2(2000),
"COL_SMALL_TEXT" VARCHAR2(2000),
"COL_MEDIUM_TEXT" CLOB,
"COL_BIG_TEXT" CLOB,
"COL_BINARY" BLOB,
"COL_TINY_BINARY" RAW(2000),
"COL_SMALL_BINARY" RAW(2000),
"COL_MEDIUM_BINARY" BLOB,
"COL_BIG_BINARY" BLOB,
"COL_DEC_SIM" NUMBER(10),
"COL_DEC_LEN" NUMBER(10),
"COL_DEC_PRE" NUMBER(10, 2),
"COL_BOOL" NUMBER(1),
"COL_DOUBLE" FLOAT(24),
"COL_STR" VARCHAR2(255),
"COL_TIME" DATE,
"COL_DATETIME" DATE)
';

        $this->assertEquals($expectedSql, $infos[0]['sql']);
        $this->assertEmpty($infos[0]['params']);
    }

    public function testAlterDropKeyAndDefaultSetDrop(): void
    {

        $e = $this->getOracleInstance();

        $atMockMethodsMaps = [
            'getCommands' => [
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
                    'type' => 'setDefaultValue',
                    'data' => [
                        'column' => 'bar',
                        'value' => 100,
                    ]
                ],
            ],
            'getTableName' => 'foo'
        ];

        $at = $this->getAlterTableInstance($atMockMethodsMaps);

        $infos = $e->alter($at);

        $this->assertCount(6, $infos);
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


        $expectedDropDefaultSql = 'ALTER TABLE "FOO" MODIFY "BAZ" DEFAULT NULL';
        $expectedDropPkSql = 'ALTER TABLE "FOO" DROP CONSTRAINT "BAZ_PK_COL"';
        $expectedDropIkSql = 'DROP INDEX "FOO"."BAZ_IK_COL"';
        $expectedDropFkSql = 'ALTER TABLE "FOO" DROP CONSTRAINT "BAZ_FK_COL"';
        $expectedDropUkSql = 'ALTER TABLE "FOO" DROP CONSTRAINT "BAZ_UK_COL"';
        $expectedSetDefaultIntSql = 'ALTER TABLE "FOO" MODIFY "BAR" DEFAULT 100';

        $this->assertEquals($expectedDropDefaultSql, $infos[0]['sql']);
        $this->assertEquals($expectedDropPkSql, $infos[1]['sql']);
        $this->assertEquals($expectedDropFkSql, $infos[2]['sql']);
        $this->assertEquals($expectedDropUkSql, $infos[3]['sql']);
        $this->assertEquals($expectedDropIkSql, $infos[4]['sql']);
        $this->assertEquals($expectedSetDefaultIntSql, $infos[5]['sql']);

        $this->assertEmpty($infos[0]['params']);
        $this->assertEmpty($infos[1]['params']);
        $this->assertEmpty($infos[2]['params']);
        $this->assertEmpty($infos[3]['params']);
        $this->assertEmpty($infos[4]['params']);
        $this->assertEmpty($infos[5]['params']);
    }

    public function testAddModifyColumn(): void
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

        $e = $this->getOracleInstance();

        $at = $this->getAlterTableInstance($atMockMethodsMaps);

        $infos = $e->alter($at);


        $this->assertCount(2, $infos);
        $this->assertIsArray($infos[0]);
        $this->assertIsArray($infos[1]);

        $this->assertArrayHasKey('sql', $infos[0]);
        $this->assertArrayHasKey('sql', $infos[1]);
        $this->assertArrayHasKey('params', $infos[0]);
        $this->assertArrayHasKey('params', $infos[1]);

        $expectedSql = 'ALTER TABLE "FOO" MODIFY "BAR" NUMBER(10)';
        $expectedAddColSql = 'ALTER TABLE "FOO" ADD "BAR" NUMBER(10) '
                . 'GENERATED BY DEFAULT ON NULL AS IDENTITY';
        $this->assertEquals($expectedSql, $infos[0]['sql']);
        $this->assertEquals($expectedAddColSql, $infos[1]['sql']);
        $this->assertEmpty($infos[0]['params']);
        $this->assertEmpty($infos[1]['params']);
    }


    private function getOracleInstance(): Oracle
    {
        $cnx = new Connection('Oracle');

        return new Oracle($cnx);
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
}
