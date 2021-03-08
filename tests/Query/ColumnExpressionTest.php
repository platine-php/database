<?php

declare(strict_types=1);

namespace Platine\Test\Database;

use Platine\Database\Query\Expression;
use Platine\Database\Query\ColumnExpression;
use Platine\Database\Query\QueryStatement;
use Platine\PlatineTestCase;

/**
 * ColumnExpression class tests
 *
 * @group core
 * @group database
 */
class ColumnExpressionTest extends PlatineTestCase
{

    public function testConstructor(): void
    {
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $e = new ColumnExpression($qs);
        $rr = $this->getPrivateProtectedAttribute(ColumnExpression::class, 'queryStatement');

        $this->assertInstanceOf(QueryStatement::class, $rr->getValue($e));
    }

    public function testColumnParamIsStringWithoutAlias(): void
    {
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $name = 'foo';
        $alias = null;

        $qs->expects($this->once())
           ->method('addColumn')
           ->with($name, $alias);

        $e = new ColumnExpression($qs);
        $e->column($name, $alias);
    }

    public function testColumnParamIsStringWithAlias(): void
    {
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $name = 'foo';
        $alias = 'f';

        $qs->expects($this->once())
           ->method('addColumn')
           ->with($name, $alias);

        $e = new ColumnExpression($qs);
        $e->column($name, $alias);
    }

    public function testColumns(): void
    {
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $qs->expects($this->exactly(3))
           ->method('addColumn');

        $columns = ['foo', 'baz' => 'b', 'name'];

        $e = new ColumnExpression($qs);
        $e->columns($columns);
    }

    public function testColumnsWithExpression(): void
    {
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $qs->expects($this->exactly(3))
           ->method('addColumn');

        $expr = new Expression();
        $expr->avg('bar');

        $columns = ['foo', 'baz' => 'b', 'exp' => $expr];

        $e = new ColumnExpression($qs);
        $e->columns($columns);
    }

    public function testCountParamIsStringWithoutAliasAndDistinct(): void
    {
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $name = 'foo';
        $alias = null;

        $qs->expects($this->once())
           ->method('addColumn');

        $e = new ColumnExpression($qs);
        $e->count($name, $alias, false);
    }

    public function testCountParamIsStringWithAliasAndDistinct(): void
    {
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                   ->getMock();

        $name = 'foo';
        $alias = 'f';

        $qs->expects($this->once())
          ->method('addColumn');

        $e = new ColumnExpression($qs);
        $e->count($name, $alias, true);
    }

    public function testAvgParamIsStringWithoutAliasAndDistinct(): void
    {
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $name = 'foo';
        $alias = null;

        $qs->expects($this->once())
           ->method('addColumn');

        $e = new ColumnExpression($qs);
        $e->avg($name, $alias, false);
    }

    public function testAvgParamIsStringWithAliasAndDistinct(): void
    {
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $name = 'foo';
        $alias = 'f';

        $qs->expects($this->once())
           ->method('addColumn');

        $e = new ColumnExpression($qs);
        $e->avg($name, $alias, true);
    }

    public function testSumParamIsStringWithoutAliasAndDistinct(): void
    {
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $name = 'foo';
        $alias = null;

        $qs->expects($this->once())
           ->method('addColumn');

        $e = new ColumnExpression($qs);
        $e->sum($name, $alias, false);
    }

    public function testSumParamIsStringWithAliasAndDistinct(): void
    {
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $name = 'foo';
        $alias = 'f';

        $qs->expects($this->once())
           ->method('addColumn');

        $e = new ColumnExpression($qs);
        $e->sum($name, $alias, true);
    }

    public function testMinParamIsStringWithoutAliasAndDistinct(): void
    {
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $name = 'foo';
        $alias = null;

        $qs->expects($this->once())
           ->method('addColumn');

        $e = new ColumnExpression($qs);
        $e->min($name, $alias, false);
    }

    public function testMinParamIsStringWithAliasAndDistinct(): void
    {
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $name = 'foo';
        $alias = 'f';

        $qs->expects($this->once())
           ->method('addColumn');

        $e = new ColumnExpression($qs);
        $e->min($name, $alias, true);
    }

    public function testMaxParamIsStringWithoutAliasAndDistinct(): void
    {
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $name = 'foo';
        $alias = null;

        $qs->expects($this->once())
           ->method('addColumn');

        $e = new ColumnExpression($qs);
        $e->max($name, $alias, false);
    }

    public function testMaxParamIsStringWithAliasAndDistinct(): void
    {
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $name = 'foo';
        $alias = 'f';

        $qs->expects($this->once())
           ->method('addColumn');

        $e = new ColumnExpression($qs);
        $e->max($name, $alias, true);
    }

    public function testClone(): void
    {
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $e = new ColumnExpression($qs);
        $rr = $this->getPrivateProtectedAttribute(ColumnExpression::class, 'queryStatement');

        $this->assertInstanceOf(QueryStatement::class, $rr->getValue($e));

        $cl = clone $e;
        $this->assertInstanceOf(QueryStatement::class, $rr->getValue($cl));
        $this->assertEquals($qs, $rr->getValue($cl));
    }
}
