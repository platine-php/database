<?php

declare(strict_types=1);

namespace Platine\Test\Database;

use DateTime;
use Platine\Database\Driver\SQLServer;
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
use Platine\Dev\PlatineTestCase;
use Platine\Test\Fixture\Connection;

/**
 * SQLServer class tests
 *
 * @group core
 * @group database
 */
class SQLServerTest extends PlatineTestCase
{
    public function testSelectDefault(): void
    {
        $e = $this->getSQLServerInstance();

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
        $expected = 'SELECT * FROM [bar]';
        $result = $e->select($qs);
        $this->assertEquals($result, $expected);
    }

    public function testSelectOffsetIsSet(): void
    {
        $e = $this->getSQLServerInstance();

        $mockMethodsMaps = [
            'hasDistinct' => false,
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
        $expected = 'SELECT * FROM '
                . '(SELECT *, ROW_NUMBER() OVER (ORDER BY (SELECT 0)) AS P_ROWNUM '
                . 'FROM [bar]) AS A1 WHERE P_ROWNUM BETWEEN 2 AND 11';
        $result = $e->select($qs);
        $this->assertEquals($result, $expected);
    }

    public function testSelectDistinct(): void
    {
        $e = $this->getSQLServerInstance();

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
        $expected = 'SELECT DISTINCT TOP 10 * FROM [bar]';
        $result = $e->select($qs);
        $this->assertEquals($result, $expected);
    }

    public function testSelectFull(): void
    {
        $e = $this->getSQLServerInstance();

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
        $expected = 'SELECT * FROM (SELECT DISTINCT [foo], ROW_NUMBER() OVER (ORDER BY [o_col] ASC) '
                . 'AS P_ROWNUM FROM [bar] INNER JOIN [tjoin] ON [foo].[id] = [baz].[id] '
                . 'AND [bar].[name] = [baz].[stat] AND [biz] = [baz] '
                . 'AND ([my_col1] = [my_col2]) AND ([col_closure] * ?) '
                . '(SELECT [foo_sq] FROM [sub_tab]) '
                . 'WHERE [bazz] = ? GROUP BY [g_col] HAVING [h_col] = 1 OR '
                . '[bar_aggr] NOT IN (SELECT [id] FROM [my_tab] WHERE [created_at] < ?) '
                . 'AND [foo_aggr] IN (?, ?) AND (AVG([hs]) = 10.8) OR [hs_betw] BETWEEN ? AND ?) '
                . 'AS A1 WHERE P_ROWNUM BETWEEN 2 AND 11';
        $result = $e->select($qs);
        $this->assertEquals($result, $expected);
    }

    public function testGetDatabaseName(): void
    {

        $e = $this->getSQLServerInstance();

        $infos = $e->getDatabaseName();

        $this->assertIsArray($infos);
        $this->assertArrayHasKey('sql', $infos);
        $this->assertArrayHasKey('params', $infos);

        $this->assertEquals('SELECT SCHEMA_NAME()', $infos['sql']);
        $this->assertEmpty($infos['params']);
    }

    public function testGetColumns(): void
    {

        $e = $this->getSQLServerInstance();

        $database = 'foo';
        $table = 'bar';

        $infos = $e->getColumns($database, $table);

        $this->assertIsArray($infos);
        $this->assertArrayHasKey('sql', $infos);
        $this->assertArrayHasKey('params', $infos);

        $expectedSql = 'SELECT [column_name] AS [name], [data_type] AS [type] '
                . 'FROM [information_schema].[columns] '
                . 'WHERE [table_schema] = ? AND [table_name] = ? '
                . 'ORDER BY [ordinal_position] ASC';

        $this->assertEquals($expectedSql, $infos['sql']);
        $this->assertCount(2, $infos['params']);
        $this->assertEquals($database, $infos['params'][0]);
        $this->assertEquals($table, $infos['params'][1]);
    }


    public function testCreateAllDataTypes(): void
    {

        $e = $this->getSQLServerInstance();

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

         //String
        $ccStringMockMethodsMaps = [
            'getName' => 'col_str',
            'getType' => 'string',
        ];

        //Fixed
        $ccFixedMockMethodsMaps = [
            'getName' => 'col_fixed',
            'getType' => 'fixed',
        ];

        //Timestamp
        $ccTimestampMockMethodsMaps = [
            'getName' => 'col_ts',
            'getType' => 'timestamp',
        ];


        //Binary
        $ccBinaryMockMethodsMaps = [
            'getName' => 'col_binary',
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

        //Boolean
        $bool = $this->getCreateColumnInstance($ccBoolMockMethodsMaps);

        //timestamp
        $ts = $this->getCreateColumnInstance($ccTimestampMockMethodsMaps);


        //String
        $str = $this->getCreateColumnInstance($ccStringMockMethodsMaps);

        //Fixed
        $fixed = $this->getCreateColumnInstance($ccFixedMockMethodsMaps);

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

        $columns = [
            'col_int' => $int,
            'col_tiny_int' => $tiny,
            'col_small_int' => $small,
            'col_medium_int' => $medium,
            'col_big_int' => $big,

            'col_text' => $text,

            'col_binary' => $binary,

            'col_dec_sim' => $decSimple,
            'col_dec_len' => $decLength,
            'col_dec_pre' => $decPrecision,

            'col_bool' => $bool,

            'col_str' => $str,

            'col_fixed' => $fixed,

            'col_ts' => $ts,

        ];

        $ctMockMethodsMaps = [
            'getEngine' => 'bar',
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

        $expectedSql = 'CREATE TABLE [bar](
[col_int] INTEGER COMMENT \'simple int column\',
[col_tiny_int] TINYINT,
[col_small_int] SMALLINT,
[col_medium_int] INTEGER,
[col_big_int] BIGINT,
[col_text] NVARCHAR(max),
[col_binary] VARBINARY(max),
[col_dec_sim] DECIMAL,
[col_dec_len] DECIMAL(10),
[col_dec_pre] DECIMAL(10, 2),
[col_bool] BIT,
[col_str] NVARCHAR(255),
[col_fixed] NCHAR(255),
[col_ts] DATETIME)
';

        $this->assertCommandOutput($expectedSql, $infos[0]['sql']);
        $this->assertEmpty($infos[0]['params']);
    }

    public function testUpdate(): void
    {
        $e = $this->getSQLServerInstance();

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
            $expected = 'UPDATE [b] SET [foo] = ?, [baz] = ? '
               . 'FROM [bar] AS [b]  INNER JOIN [tjoin] '
               . 'WHERE [bazz] = ? AND ([name] LIKE ? OR [status] = ?) '
               . 'AND EXISTS (SELECT [id] FROM [my_tab] '
               . 'WHERE [created_at] < ?) AND [in_select] '
               . 'IN (SELECT [id] FROM [my_tab] WHERE [created_at] < ?) '
               . 'AND [wh_select] = (SELECT [id] FROM [my_tab] WHERE [created_at] < ?) OR [nop_wh]';

            $result = $e->update($qs);
            $this->assertEquals($result, $expected);
    }

    public function testRenameTable(): void
    {

        $e = $this->getSQLServerInstance();

        $current = 'foo';
        $new = 'bar';

        $infos = $e->renameTable($current, $new);

        $this->assertIsArray($infos);
        $this->assertArrayHasKey('sql', $infos);
        $this->assertArrayHasKey('params', $infos);

        $expectedSql = sprintf('sp_rename [%s], [%s]', $current, $new);

        $this->assertEquals($expectedSql, $infos['sql']);
        $this->assertEmpty($infos['params']);
    }

    public function testRenameColumn(): void
    {

        $e = $this->getSQLServerInstance();

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

        $expectedSql = 'sp_rename [foo].[old_col], [new_col], COLUMN';
        $this->assertEquals($expectedSql, $infos[0]['sql']);
        $this->assertEmpty($infos[0]['params']);
    }

    private function getSQLServerInstance(): SQLServer
    {
        $cnx = new Connection('SQLServer');

        return new SQLServer($cnx);
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
