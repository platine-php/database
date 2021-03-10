<?php

declare(strict_types=1);

namespace Platine\Test\Database;

use Platine\Database\Query\Expression;
use Platine\Database\Query\QueryStatement;
use Platine\Database\Query\Select;
use Platine\PlatineTestCase;
use Platine\Test\Fixture\Connection;

/**
 * Select class tests
 *
 * @group core
 * @group database
 */
class SelectTest extends PlatineTestCase
{

    public function testConstructorFromIsString(): void
    {
        $cnx = new Connection('MySQL');
        $from = 'foo';
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $e = new Select($cnx, $from, $qs);

        $cr = $this->getPrivateProtectedAttribute(Select::class, 'connection');

        $this->assertInstanceOf(Connection::class, $cr->getValue($e));
        $this->assertInstanceOf(QueryStatement::class, $e->getQueryStatement());
        $this->assertEquals($qs, $e->getQueryStatement());
    }

    public function testSelectColumnsIsString(): void
    {
        $cnx = new Connection('MySQL');
        $from = 'foo';
        $columns = 'bar';

        $mockColumns = [
            [
                'name' => $columns,
                'alias' => null
            ]
        ];

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $qs->expects($this->any())
                ->method('getTables')
                ->will($this->returnValue([$from]));

        $qs->expects($this->any())
                ->method('getOffset')
                ->will($this->returnValue(-1));

        $qs->expects($this->any())
                ->method('getColumns')
                ->will($this->returnValue($mockColumns));

        $e = new Select($cnx, $from, $qs);

        $e->select($columns);

        $expected = 'SELECT `bar` FROM `foo`';
        $this->assertEquals($expected, $cnx->getRawSql());
    }

    public function testSelectColumnsIsExpression(): void
    {
        $cnx = new Connection('MySQL');
        $from = 'foo';
        $columns = (new Expression())->column('baz');

        $mockColumns = [
            [
                'name' => $columns,
                'alias' => null
            ]
        ];

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $qs->expects($this->any())
                ->method('getTables')
                ->will($this->returnValue([$from]));

        $qs->expects($this->any())
                ->method('getOffset')
                ->will($this->returnValue(-1));

        $qs->expects($this->any())
                ->method('getColumns')
                ->will($this->returnValue($mockColumns));

        $e = new Select($cnx, $from, $qs);

        $e->select($columns);

        $expected = 'SELECT `baz` FROM `foo`';
        $this->assertEquals($expected, $cnx->getRawSql());
    }

    public function testColumn(): void
    {
        $cnx = new Connection('MySQL');
        $from = 'foo';

        $columns = 'bar';

        $qsMockMethods = $this->getClassMethodsToMock(QueryStatement::class, [
            'getTables',
            'getOffset',
            'getColumns',
            'addColumn',
            'closureToExpression',
            'addTables'
        ]);

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->onlyMethods($qsMockMethods)
                    ->getMock();

        $e = new Select($cnx, $from, $qs);

        $e->column($columns);

        $expected = 'SELECT `bar` FROM `foo`';
        $this->assertEquals($expected, $cnx->getRawSql());
    }

    public function testAggregateFunctionsColumnsIsString(): void
    {
        $columns = 'col_aggregate';

        $this->aggregateFunctionsTests('count', $columns, 'COUNT');
        $this->aggregateFunctionsTests('avg', $columns, 'AVG');
        $this->aggregateFunctionsTests('min', $columns, 'MIN');
        $this->aggregateFunctionsTests('max', $columns, 'MAX');
        $this->aggregateFunctionsTests('sum', $columns, 'SUM');
    }

    private function aggregateFunctionsTests($method, $columns, $name): void
    {
        $cnx = new Connection('MySQL');
        $from = 'foo';

        $qsMockMethods = $this->getClassMethodsToMock(QueryStatement::class, [
            'getTables',
            'getOffset',
            'getColumns',
            'addColumn',
            'closureToExpression',
            'addTables'
        ]);

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->onlyMethods($qsMockMethods)
                    ->getMock();

        $e = new Select($cnx, $from, $qs);

        $e->{$method}($columns);

        $expected = sprintf('SELECT %s(`col_aggregate`) FROM `foo`', $name);
        $this->assertEquals($expected, $cnx->getRawSql());
    }
}
