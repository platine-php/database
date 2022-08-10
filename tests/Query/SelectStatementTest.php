<?php

declare(strict_types=1);

namespace Platine\Test\Database;

use Platine\Database\Query\ColumnExpression;
use Platine\Database\Query\Expression;
use Platine\Database\Query\HavingExpression;
use Platine\Database\Query\HavingStatement;
use Platine\Database\Query\QueryStatement;
use Platine\Database\Query\SelectStatement;
use Platine\Dev\PlatineTestCase;

/**
 * SelectStatement class tests
 *
 * @group core
 * @group database
 */
class SelectStatementTest extends PlatineTestCase
{
    public function testConstructorTableIsStringQueryStatementParamIsNull(): void
    {
        $tables = 'foo';
        $e = new SelectStatement($tables);

        $this->assertInstanceOf(QueryStatement::class, $e->getQueryStatement());
        $this->assertEquals([$tables], $e->getQueryStatement()->getTables());
    }

    public function testConstructorTableIsArrarQueryStatementParamIsNull(): void
    {
        $tables = ['foo'];
        $e = new SelectStatement($tables);

        $this->assertInstanceOf(QueryStatement::class, $e->getQueryStatement());
        $this->assertEquals($tables, $e->getQueryStatement()->getTables());
    }

    public function testConstructorFromIsStringQueryStatementParamIsNotNull(): void
    {
        $from = 'foo';
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $qs->expects($this->once())
           ->method('addTables')
           ->with([$from]);

        $e = new SelectStatement($from, $qs);

        $this->assertInstanceOf(QueryStatement::class, $e->getQueryStatement());
        $this->assertEquals($qs, $e->getQueryStatement());
    }

    public function testConstructorFromIsArrayQueryStatementParamIsNotNull(): void
    {
        $from = ['foo'];
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $qs->expects($this->once())
           ->method('addTables')
           ->with($from);

        $e = new SelectStatement($from, $qs);

        $this->assertInstanceOf(QueryStatement::class, $e->getQueryStatement());
        $this->assertEquals($qs, $e->getQueryStatement());
    }

    public function testInto(): void
    {
        $from = 'foo';
        $table = 'bar';
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $qs->expects($this->once())
           ->method('setInto')
           ->with($table);

        $e = new SelectStatement($from, $qs);

        $e->into($table);
    }

    public function testDistinct(): void
    {
        $from = 'foo';
        $value = true;
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $qs->expects($this->once())
           ->method('setDistinct')
           ->with($value);

        $e = new SelectStatement($from, $qs);

        $e->distinct($value);
    }

    public function testGroupBy(): void
    {
        $from = 'foo';

        $columns = 'bar';

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $qs->expects($this->once())
           ->method('addGroupBy')
           ->with([$columns]);

        $e = new SelectStatement($from, $qs);

        $e->groupBy($columns);
    }

    public function testHaving(): void
    {
        $this->havingTests('having', 'AND');
        $this->havingTests('orHaving', 'OR');
    }

    public function testOrderBy(): void
    {
        $from = 'foo';

        $columns = 'bar';
        $order = 'ASC';

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $qs->expects($this->once())
           ->method('addOrder')
           ->with([$columns], $order);

        $e = new SelectStatement($from, $qs);

        $e->orderBy($columns, $order);
    }

    public function testSelectColumnsIsString(): void
    {
        $from = 'foo';

        $columns = 'bar';

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $qs->expects($this->once())
           ->method('addColumn')
           ->with($columns, null);

        $e = new SelectStatement($from, $qs);

        $e->select($columns);
    }

    public function testSelectColumnsIsExpression(): void
    {
        $from = 'foo';

        $columns = (new Expression())->column('baz');

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $qs->expects($this->once())
           ->method('addColumn')
           ->with($columns, null);

        $e = new SelectStatement($from, $qs);

        $e->select($columns);
    }

    public function testSelectColumnsIsClosure(): void
    {
        $from = 'foo';

        $columns = function (ColumnExpression $exp) {
            $exp->column('baz');
        };

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $qs->expects($this->once())
           ->method('addColumn')
           ->with('baz', null);

        $e = new SelectStatement($from, $qs);

        $e->select($columns);
    }

    public function testColumn(): void
    {
        $from = 'foo';

        $column = 'foo';

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $qs->expects($this->once())
           ->method('addColumn')
            ->with($column);

        $e = new SelectStatement($from, $qs);

        $e->column($column);
    }

    public function testAggregateFunctions(): void
    {
        $this->aggregateFunctionsTests('count');
        $this->aggregateFunctionsTests('avg');
        $this->aggregateFunctionsTests('sum');
        $this->aggregateFunctionsTests('min');
        $this->aggregateFunctionsTests('max');
    }

    public function testClone(): void
    {
        $from = 'foo';

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $e = new SelectStatement($from, $qs);

        $c = clone $e;

        $hsr = $this->getPrivateProtectedAttribute(SelectStatement::class, 'havingStatement');

        $this->assertInstanceOf(HavingStatement::class, $hsr->getValue($c));
        $this->assertEquals($qs, $hsr->getValue($c)->getQueryStatement());
    }

    public function testlimitAndOffset(): void
    {
        $this->limitAndOffset('limit', 'setLimit');
        $this->limitAndOffset('offset', 'setOffset');
    }

    public function aggregateFunctionsTests($method): void
    {
        $from = 'foo';

        $column = 'foo';

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $qs->expects($this->once())
           ->method('addColumn');

        $e = new SelectStatement($from, $qs);

        $e->{$method}($column);
    }


    private function limitAndOffset($method, $qsMethod): void
    {
        $from = 'foo';

        $value = 13;

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $qs->expects($this->once())
           ->method($qsMethod)
           ->with($value);

        $e = new SelectStatement($from, $qs);

        $e->{$method}($value);
    }


    private function havingTests($method, $separator): void
    {
        $from = 'foo';

        $column = function (HavingExpression $exp) {
            $exp->avg()->is(1);
        };

        $closure = null;

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $qs->expects($this->once())
           ->method('addHavingGroup')
           ->with($column, $separator);


        $e = new SelectStatement($from, $qs);

        $e->{$method}($column, $closure);
    }
}
