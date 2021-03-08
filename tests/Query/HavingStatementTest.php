<?php

declare(strict_types=1);

namespace Platine\Test\Database;

use Platine\Database\Query\Expression;
use Platine\Database\Query\QueryStatement;
use Platine\Database\Query\HavingExpression;
use Platine\Database\Query\SubQuery;
use Platine\Database\Query\HavingStatement;
use Platine\PlatineTestCase;

/**
 * HavingStatement class tests
 *
 * @group core
 * @group database
 */
class HavingStatementTest extends PlatineTestCase
{

    public function testConstructorQueryStatementParamIsNull(): void
    {
        $e = new HavingStatement();
        $her = $this->getPrivateProtectedAttribute(HavingStatement::class, 'expression');

        $this->assertInstanceOf(QueryStatement::class, $e->getQueryStatement());
        $this->assertInstanceOf(HavingExpression::class, $her->getValue($e));
    }

    public function testConstructorCustomQueryStatement(): void
    {
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $e = new HavingStatement($qs);
        $her = $this->getPrivateProtectedAttribute(HavingStatement::class, 'expression');

        $this->assertInstanceOf(QueryStatement::class, $e->getQueryStatement());
        $this->assertInstanceOf(HavingExpression::class, $her->getValue($e));
        $this->assertEquals($qs, $e->getQueryStatement());
    }

    public function testClone(): void
    {
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();


        $e = new HavingStatement($qs);

        $c = clone $e;
        $qsr = $this->getPrivateProtectedAttribute(HavingStatement::class, 'queryStatement');
        $her = $this->getPrivateProtectedAttribute(HavingStatement::class, 'expression');

        $this->assertInstanceOf(QueryStatement::class, $qsr->getValue($c));
        $this->assertInstanceOf(HavingExpression::class, $her->getValue($c));
    }


    public function testHavingColumnParamIsStringAndClosureIsNull(): void
    {
        $column = 'foo';
        $closure = null;
        $separator = 'AND';

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $her = $this->getPrivateProtectedAttribute(HavingStatement::class, 'expression');

        $e = new HavingStatement($qs);
        $e->having($column, $closure);

        $havingExpression = $her->getValue($e);
        $this->assertInstanceOf(HavingExpression::class, $havingExpression);

        $sr = $this->getPrivateProtectedAttribute(HavingExpression::class, 'separator');
        $cr = $this->getPrivateProtectedAttribute(HavingExpression::class, 'column');

        $this->assertEquals($column, $cr->getValue($havingExpression));
        $this->assertEquals($separator, $sr->getValue($havingExpression));
    }

    public function testHavingColumnParamIsStringAndClosureIsNotNull(): void
    {
        $column = 'foo';
        $closure = function (HavingExpression $expr) {
            $expr->avg()->eq(1);
        };

        $separator = 'AND';

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $her = $this->getPrivateProtectedAttribute(HavingStatement::class, 'expression');

        $e = new HavingStatement($qs);
        $e->having($column, $closure);

        $havingExpression = $her->getValue($e);
        $this->assertInstanceOf(HavingExpression::class, $havingExpression);

        $sr = $this->getPrivateProtectedAttribute(HavingExpression::class, 'separator');
        $cr = $this->getPrivateProtectedAttribute(HavingExpression::class, 'column');

        $this->assertEquals($column, $cr->getValue($havingExpression));
        $this->assertEquals($separator, $sr->getValue($havingExpression));
    }

    public function testHavingColumnParamIsClosureAndClosureIsNull(): void
    {
        $column = function () {
        };
        $closure = null;
        $separator = 'AND';

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $qs->expects($this->once())
           ->method('addHavingGroup')
           ->with($column, $separator);

        $e = new HavingStatement($qs);
        $e->having($column, $closure);
    }

    public function testOrHavingColumnParamIsClosureAndClosureIsNull(): void
    {
        $column = function () {
        };
        $closure = null;
        $separator = 'OR';

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $qs->expects($this->once())
           ->method('addHavingGroup')
           ->with($column, $separator);

        $e = new HavingStatement($qs);
        $e->orHaving($column, $closure);
    }
}
